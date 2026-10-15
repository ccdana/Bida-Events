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
use Illuminate\View\View;

/**
 * El editor del revendedor: el mismo flujo y los mismos paneles que el del administrador, con tres
 * diferencias. La invitación siempre queda a cargo del propio revendedor, solo ofrece las plantillas de su
 * plan y, al publicarla por primera vez, respeta el cupo del mes (los borradores no lo gastan): sin
 * cupo, se guarda igual pero queda sin publicar, con el aviso.
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

    public function create(): View
    {
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

        $held = false;

        $invitation = DB::transaction(function () use ($request, $user, &$held) {
            // Se vuelve a contar dentro de la transacción, con el usuario bloqueado: dos pestañas que
            // publican a la vez no pueden pasarse juntas del cupo
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();

            // Crear siempre se puede; publicarla, solo con cupo (si no, queda como borrador)
            $held = $request->validated('status') === 'active' && ! ResellerSubscription::hasQuotaLeft($user);

            $invitation = Invitation::create([
                ...$request->safe()->except('modulos_data'),
                'slug' => Str::slug($request->validated('slug')),
                'status' => $held ? 'inactive' : $request->validated('status'),
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
            ->with($held ? 'warning' : 'success', $held ? $this->heldMessage() : 'Invitación creada. Cuando esté lista, publícala y comparte su enlace.');
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
        $held = false;

        DB::transaction(function () use ($request, $invitation, &$held) {
            $user = $request->user();
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();

            // Publicarla por primera vez gasta cupo: sin cupo, se guarda igual pero sin publicar
            $held = $request->validated('status') === 'active'
                && $invitation->status !== 'active'
                && ! ResellerSubscription::canPublish($user, $invitation);

            $invitation->update([
                ...$request->safe()->except('modulos_data'),
                'slug' => Str::slug($request->validated('slug')),
                'status' => $held ? 'inactive' : $request->validated('status'),
            ]);

            $this->syncModules($invitation, $request->modulesData());
        });

        $invitation->refresh();
        $modulos = $this->moduleService->resolveModules($invitation);

        InvitationPreviewSession::seed($invitation, InvitationPreviewSession::payloadFromInvitation($invitation, $modulos));
        InvitationUpdated::dispatch($invitation, $previousSlug);

        return redirect()
            ->route('client.invitations.edit', $invitation)
            ->with($held ? 'warning' : 'success', $held ? $this->heldMessage() : 'Invitación actualizada correctamente.');
    }

    /** Se guardó, pero no se publicó: ya no le queda cupo este mes. */
    private function heldMessage(): string
    {
        $limit = ResellerSubscription::quotaLimit(auth()->user());
        $nextMonth = now()->timezone(config('app.timezone'))->startOfMonth()->addMonthNoOverflow()->locale('es')->translatedFormat('j \d\e F');

        return "Guardamos tus cambios, pero no la publicamos: ya publicaste las {$limit} invitaciones de tu plan este mes. "
            ."Podrás publicarla desde el {$nextMonth}, o ahora mismo con un plan mayor.";
    }
}
