<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use App\Models\Invitation;
use App\Models\Plan;
use App\Models\User;
use App\Support\InvitationDefaults;
use App\Services\InvitationModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationModuleService $moduleService
    ) {}

    public function create()
    {
        return view('admin.invitations.create', $this->formData(
            invitation: null,
            modulos: InvitationDefaults::modules(),
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

        return redirect()
            ->route('admin.invitations.edit', $invitation)
            ->with('success', 'Invitación creada correctamente.');
    }

    public function edit(Invitation $invitation)
    {
        $invitation->load('modulesData', 'eventType', 'user', 'plan');

        return view('admin.invitations.edit', $this->formData(
            invitation: $invitation,
            modulos: $invitation->modules,
        ));
    }

    public function update(Request $request, Invitation $invitation)
    {
        $validated = $this->validateInvitation($request, $invitation);

        if (empty($validated['user_id'])) {
            $validated['user_id'] = null;
        }

        $invitation->update([
            ...$validated,
            'slug' => Str::slug($validated['slug']),
        ]);

        $this->syncModulesFromRequest($request, $invitation);

        return redirect()
            ->route('admin.invitations.edit', $invitation)
            ->with('success', 'Invitación actualizada correctamente.');
    }

    protected function formData(?Invitation $invitation, array $modulos): array
    {
        return [
            'invitation' => $invitation,
            'modulos' => $modulos,
            'templates' => InvitationDefaults::templates(),
            'itineraryIcons' => InvitationDefaults::itineraryIcons(),
            'eventTypes' => EventType::orderBy('name')->get(),
            'plans' => Plan::orderBy('price')->get(),
            'clients' => User::where('is_admin', false)->orderBy('name')->get(),
        ];
    }

    protected function validateInvitation(Request $request, ?Invitation $invitation = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('invitations', 'slug')->ignore($invitation?->id)],
            'template' => ['required', 'string'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'event_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,active,suspended,expired'],
            'expires_at' => ['required', 'date'],
        ]);
    }

    protected function syncModulesFromRequest(Request $request, Invitation $invitation): void
    {
        $moduleCodes = [
            'config', 'bienvenida', 'ubicacion', 'itinerario', 'dress_code',
            'destacados', 'galeria', 'musica', 'video', 'playlist', 'hashtag',
            'encuestas', 'regalos', 'post_evento', 'rsvp',
        ];

        foreach ($moduleCodes as $code) {
            $raw = $request->input("modulos.{$code}");
            if ($raw === null) {
                continue;
            }

            $data = is_string($raw) ? json_decode($raw, true) : $raw;
            if (is_array($data)) {
                $this->moduleService->syncModule($invitation, $code, $data);
            }
        }
    }
}
