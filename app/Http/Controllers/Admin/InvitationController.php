<?php

namespace App\Http\Controllers\Admin;

use App\Events\InvitationUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Invitation\StoreClientRequest;
use App\Http\Requests\Admin\Invitation\StoreInvitationRequest;
use App\Http\Requests\Admin\Invitation\UpdateInvitationRequest;
use App\Models\Invitation;
use App\Models\User;
use App\Support\InvitationDefaults;
use App\Services\InvitationModuleService;
use App\Services\InvitationPreviewSession;
use App\ViewModels\Admin\InvitationEditorViewData;
use Illuminate\Support\Facades\DB;
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

        $modulos = $this->modulesFromOldInput() ?? InvitationDefaults::emptyModules();

        return view('admin.invitations.create', app(InvitationEditorViewData::class)->make(
            invitation: null,
            modulos: $modulos,
            isCreate: true,
        ));
    }

    public function store(StoreInvitationRequest $request)
    {
        $validated = $request->safe()->except('modulos_data');

        if (empty($validated['user_id'])) {
            $validated['user_id'] = null;
        }

        $invitation = DB::transaction(function () use ($request, $validated) {
            $invitation = Invitation::create([
                ...$validated,
                'slug' => Str::slug($validated['slug']),
            ]);

            $this->syncModules($invitation, $request->modulesData());

            return $invitation;
        });

        $invitation->load('modulesData');
        $invitation->clearModulesCache();
        $modulos = $this->moduleService->resolveModules($invitation);

        InvitationPreviewSession::seed(
            $invitation,
            InvitationPreviewSession::payloadFromInvitation($invitation, $modulos)
        );
        InvitationUpdated::dispatch($invitation);

        return redirect()
            ->route('admin.invitations.edit', $invitation)
            ->with('success', 'Invitación creada correctamente.');
    }

    public function edit(Invitation $invitation)
    {
        $invitation->loadMissing('modulesData', 'eventType', 'user');
        $invitation->clearModulesCache();
        $modulos = $this->modulesFromOldInput() ?? $this->moduleService->resolveModules($invitation);

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
        $validated = $request->safe()->except('modulos_data');
        $previousSlug = $invitation->slug;

        if (empty($validated['user_id'])) {
            $validated['user_id'] = null;
        }

        DB::transaction(function () use ($request, $invitation, $validated) {
            $invitation->update([
                ...$validated,
                'slug' => Str::slug($validated['slug']),
            ]);

            $this->syncModules($invitation, $request->modulesData());
        });

        $invitation->refresh();
        $invitation->load('modulesData');
        $modulos = $this->moduleService->resolveModules($invitation);

        InvitationPreviewSession::seed(
            $invitation,
            InvitationPreviewSession::payloadFromInvitation($invitation, $modulos)
        );
        InvitationUpdated::dispatch($invitation, $previousSlug);

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

    protected function syncModules(Invitation $invitation, array $modulesData): void
    {
        $modules = InvitationDefaults::emptyModules();

        foreach (InvitationDefaults::moduleCodes() as $code) {
            $modules[$code] = is_array($modulesData[$code] ?? null) ? $modulesData[$code] : [];
        }

        $this->moduleService->syncAllModules($invitation, $modules);
        $invitation->touch();
    }

    /**
     * Si el último guardado no pasó la validación, el editor se reabre con lo que el usuario había enviado.
     */
    protected function modulesFromOldInput(): ?array
    {
        $old = session()->getOldInput('modulos');

        if (! is_array($old)) {
            return null;
        }

        $modules = InvitationDefaults::emptyModules();

        foreach (InvitationDefaults::moduleCodes() as $code) {
            $decoded = is_string($old[$code] ?? null) ? json_decode($old[$code], true) : null;

            if (is_array($decoded)) {
                $modules[$code] = $decoded;
            }
        }

        return $this->moduleService->normalizeModules($modules);
    }
}
