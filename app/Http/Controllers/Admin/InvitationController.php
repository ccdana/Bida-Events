<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Support\InvitationDefaults;
use App\Services\InvitationModuleService;
use App\Services\InvitationCacheService;
use App\Services\InvitationPreviewSession;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationModuleService $moduleService
    ) {}

    public function create()
    {
        InvitationPreviewSession::forgetDraft();

        $modulos = InvitationDefaults::emptyModules();

        return view('admin.invitations.create', $this->formData(
            invitation: null,
            modulos: $modulos,
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvitation($request);

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

        return view('admin.invitations.edit', $this->formData(
            invitation: $invitation,
            modulos: $modulos,
        ));
    }

    public function update(Request $request, Invitation $invitation)
    {
        $validated = $this->validateInvitation($request, $invitation);
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

    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        ]);

        $client = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(16)),
            'is_admin' => false,
        ]);

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
            ],
        ], 201);
    }

    protected function formData(?Invitation $invitation, array $modulos): array
    {
        return [
            'invitation' => $invitation,
            'modulos' => $modulos,
            'templates' => InvitationDefaults::templates(),
            'itineraryIcons' => InvitationDefaults::itineraryIcons(),
            'eventTypes' => EventType::orderBy('name')->get(),
            'clients' => User::where('is_admin', false)->orderBy('name')->get(),
            'cloudinaryConfigured' => app(MediaUploadService::class)->isCloudinaryConfigured(),
            'moduleCodes' => InvitationDefaults::moduleCodes(),
            'moduleTabMap' => InvitationDefaults::moduleTabMap(),
        ];
    }

    protected function validateInvitation(Request $request, ?Invitation $invitation = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('invitations', 'slug')->ignore($invitation?->id)],
            'template' => ['required', 'string'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'event_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,active,suspended,expired'],
            'expires_at' => ['required', 'date'],
        ]);
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
