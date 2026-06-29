<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Invitation\StoreClientRequest;
use App\Http\Requests\Admin\Invitation\StoreInvitationRequest;
use App\Http\Requests\Admin\Invitation\UpdateInvitationRequest;
use App\Models\Invitation;
use App\Models\User;
use App\Support\InvitationDefaults;
use App\Services\InvitationModuleService;
use App\Services\InvitationCacheService;
use App\Services\InvitationPreviewSession;
use App\ViewModels\Admin\InvitationEditorViewData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationModuleService $moduleService
    ) {}

    public function create()
    {
        InvitationPreviewSession::forgetDraft();

        $modulos = InvitationDefaults::emptyModules();

        return view('admin.invitations.create', app(InvitationEditorViewData::class)->make(
            invitation: null,
            modulos: $modulos,
            isCreate: true,
        ));
    }

    public function store(StoreInvitationRequest $request)
    {
        $validated = $request->validated();

        if (empty($validated['user_id'])) {
            $validated['user_id'] = null;
        }

        $invitation = Invitation::create([
            ...$validated,
            'slug' => Str::slug($validated['slug']),
        ]);

        $this->syncModulesFromRequest($request, $invitation);

        $invitation->load('modulesData');
        $invitation->clearModulesCache();
        $modulos = $this->moduleService->normalizeModules($invitation->modules);

        InvitationCacheService::invalidate($invitation);
        InvitationPreviewSession::seed(
            $invitation,
            InvitationPreviewSession::payloadFromInvitation($invitation, $modulos)
        );
        InvitationCacheService::warmup($invitation);

        return redirect()
            ->route('admin.invitations.edit', $invitation)
            ->with('success', 'Invitación creada correctamente.');
    }

    public function edit(Invitation $invitation)
    {
        $invitation->loadMissing('modulesData', 'eventType', 'user');
        $invitation->clearModulesCache();
        $modulos = $this->moduleService->normalizeModules($invitation->modules);

        InvitationPreviewSession::seed(
            $invitation,
            InvitationPreviewSession::payloadFromInvitation($invitation, $modulos)
        );

        return view('admin.invitations.edit', app(InvitationEditorViewData::class)->make(
            invitation: $invitation,
            modulos: $modulos,
            isCreate: false,
        ));
    }

    public function update(UpdateInvitationRequest $request, Invitation $invitation)
    {
        $validated = $request->validated();
        $previousSlug = $invitation->slug;

        if (empty($validated['user_id'])) {
            $validated['user_id'] = null;
        }

        $invitation->update([
            ...$validated,
            'slug' => Str::slug($validated['slug']),
        ]);

        $this->syncModulesFromRequest($request, $invitation);

        $invitation->refresh();
        $invitation->load('modulesData');
        $modulos = $this->moduleService->normalizeModules($invitation->modules);

        InvitationCacheService::invalidate($invitation, $previousSlug);
        InvitationPreviewSession::seed(
            $invitation,
            InvitationPreviewSession::payloadFromInvitation($invitation, $modulos)
        );
        InvitationCacheService::warmup($invitation);

        return redirect()
            ->route('admin.invitations.edit', $invitation)
            ->with('success', 'Invitación actualizada correctamente.');
    }

    public function storeClient(StoreClientRequest $request)
    {
        $validated = $request->validated();

        $tempPassword = Str::random(16);
        
        $client = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($tempPassword),
            'is_admin' => false,
        ]);

        $passwords = session('client_temp_passwords', []);
        $passwords[$client->id] = $tempPassword;
        session(['client_temp_passwords' => $passwords]);

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'tempPassword' => $tempPassword,
            ],
        ], 201);
    }

    protected function syncModulesFromRequest(Request $request, Invitation $invitation): void
    {
        $moduleCodes = InvitationDefaults::moduleCodes();
        $modules = InvitationDefaults::emptyModules();

        foreach ($moduleCodes as $code) {
            $raw = $request->input("modulos.{$code}", '{}');
            $data = is_string($raw) ? json_decode($raw, true) : $raw;

            if (! is_array($data) || (is_string($raw) && json_last_error() !== JSON_ERROR_NONE)) {
                $data = [];
            }

            $modules[$code] = $data;
        }

        $this->moduleService->syncAllModules($invitation, $modules);
        $invitation->touch();
    }
}
