<?php

namespace App\Http\Controllers\Client;

use App\Events\InvitationUpdated;
use App\Http\Controllers\Concerns\SavesInvitationModules;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreResellerInvitationRequest;
use App\Http\Requests\Client\UpdateResellerInvitationRequest;
use App\Models\Invitation;
use App\Services\InvitationModuleService;
use App\Services\InvitationPreviewSession;
use App\Support\InvitationDefaults;
use App\Support\ResellerSubscription;
use App\ViewModels\Admin\InvitationEditorViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * El editor del revendedor: el mismo flujo y los mismos paneles que el del administrador, con tres
 * diferencias. La invitación siempre queda a cargo del propio revendedor, solo ofrece las plantillas de su
 * plan y, al crear, respeta el cupo del mes.
 *
 * Quién entra lo deciden las rutas: middleware «reseller» y la policy (create / update), que exige
 * la suscripción al día.
 */
class InvitationController extends Controller
{
    use SavesInvitationModules;

    public function __construct(
        protected InvitationModuleService $moduleService,
        protected InvitationEditorViewData $editorData,
    ) {}

    public function create(): View|RedirectResponse
    {
        // Sin cupo no tiene sentido abrir el editor: se avisa en el panel
        if (! ResellerSubscription::hasQuotaLeft(auth()->user())) {
            return redirect()->route('client.dashboard')->withErrors(['quota' => $this->quotaMessage()]);
        }

        InvitationPreviewSession::forgetDraft();

        return view('client.invitations.create', $this->editorData->make(
            invitation: null,
            modulos: $this->modulesFromOldInput() ?? InvitationDefaults::emptyModules(),
            isCreate: true,
            context: InvitationEditorViewData::RESELLER,
            owner: auth()->user(),
        ));
    }

    public function store(StoreResellerInvitationRequest $request): RedirectResponse
    {
        $user = $request->user();

        $invitation = DB::transaction(function () use ($request, $user) {
            // Se vuelve a contar dentro de la transacción, con el usuario bloqueado: dos pestañas que
            // guardan a la vez no pueden pasarse juntas del cupo
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();

            if (! ResellerSubscription::hasQuotaLeft($user)) {
                throw ValidationException::withMessages(['quota' => $this->quotaMessage()]);
            }

            $invitation = Invitation::create([
                ...$request->safe()->except('modulos_data'),
                'slug' => Str::slug($request->validated('slug')),
                // El revendedor no viene del formulario: siempre es quien la crea. El cliente del evento
                // se le asigna después, si lo quiere crear (ResellerClientController)
                'reseller_id' => $user->id,
                'user_id' => null,
            ]);

            $this->syncModules($invitation, $request->modulesData());

            return $invitation;
        });

        $modulos = $this->moduleService->resolveModules($invitation);

        InvitationPreviewSession::seed($invitation, InvitationPreviewSession::payloadFromInvitation($invitation, $modulos));
        InvitationUpdated::dispatch($invitation);

        return redirect()
            ->route('client.invitations.edit', $invitation)
            ->with('success', 'Invitación creada. Cuando esté lista, publícala y comparte su enlace.');
    }

    public function edit(Invitation $invitation): View
    {
        $invitation->loadMissing('eventType');
        $modulos = $this->modulesFromOldInput() ?? $this->moduleService->resolveModules($invitation);

        InvitationPreviewSession::seed($invitation, InvitationPreviewSession::payloadFromInvitation($invitation, $modulos));

        return view('client.invitations.edit', $this->editorData->make(
            invitation: $invitation,
            modulos: $modulos,
            isCreate: false,
            context: InvitationEditorViewData::RESELLER,
            owner: auth()->user(),
        ));
    }

    public function update(UpdateResellerInvitationRequest $request, Invitation $invitation): RedirectResponse
    {
        $previousSlug = $invitation->slug;

        DB::transaction(function () use ($request, $invitation) {
            $invitation->update([
                ...$request->safe()->except('modulos_data'),
                'slug' => Str::slug($request->validated('slug')),
            ]);

            $this->syncModules($invitation, $request->modulesData());
        });

        $invitation->refresh();
        $modulos = $this->moduleService->resolveModules($invitation);

        InvitationPreviewSession::seed($invitation, InvitationPreviewSession::payloadFromInvitation($invitation, $modulos));
        InvitationUpdated::dispatch($invitation, $previousSlug);

        return redirect()
            ->route('client.invitations.edit', $invitation)
            ->with('success', 'Invitación actualizada correctamente.');
    }

    private function quotaMessage(): string
    {
        $limit = ResellerSubscription::quotaLimit(auth()->user());

        return "Ya usaste las {$limit} invitaciones que incluye tu plan este mes. "
            .'El cupo vuelve a empezar el primer día del próximo mes; si necesitas más, pregúntanos por un plan mayor.';
    }
}
