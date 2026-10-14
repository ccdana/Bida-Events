<?php

namespace App\Http\Controllers\Client;

use App\Events\InvitationUpdated;
use App\Http\Controllers\Concerns\SavesInvitationModules;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreDiyInvitationRequest;
use App\Http\Requests\Client\UpdateDiyInvitationRequest;
use App\Models\Invitation;
use App\Services\InvitationCacheService;
use App\Services\InvitationModuleService;
use App\Services\InvitationPreviewSession;
use App\Support\DiyInvitations;
use App\Support\InvitationDefaults;
use App\Support\LeadSource;
use App\Support\Packages;
use App\ViewModels\Admin\InvitationEditorViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * «Hazlo tú»: el dueño arma su propia invitación con el mismo editor que el equipo, sin pagar antes.
 *
 * Mientras es borrador tiene todo desbloqueado. «Publicar» guarda y, la primera vez (o si ahora pide
 * un nivel mayor que el que tiene), pasa por la página de publicar: gratis apagando lo que no incluye,
 * o con el nivel que piden sus partes. Las reglas están en App\Support\DiyInvitations.
 *
 * Quién entra lo deciden las rutas: la policy (createOwn / editOwn).
 */
class DiyInvitationController extends Controller
{
    use SavesInvitationModules;

    public function __construct(
        protected InvitationModuleService $moduleService,
        protected InvitationEditorViewData $editorData,
    ) {}

    public function create(): View|RedirectResponse
    {
        // Con todos sus borradores ocupados no tiene sentido abrir el editor: se avisa en el panel
        if (! DiyInvitations::canCreateDraft(auth()->user())) {
            return redirect()->route('client.dashboard')->withErrors(['quota' => $this->draftsMessage()]);
        }

        InvitationPreviewSession::forgetDraft();

        return view('client.diy.create', $this->editorData->make(
            invitation: null,
            modulos: $this->modulesFromOldInput() ?? InvitationDefaults::emptyModules(),
            isCreate: true,
            context: InvitationEditorViewData::DIY,
            owner: auth()->user(),
        ));
    }

    public function store(StoreDiyInvitationRequest $request): RedirectResponse
    {
        $user = $request->user();

        $invitation = DB::transaction(function () use ($request, $user) {
            // Se vuelve a contar con el usuario bloqueado: dos pestañas no pueden pasarse juntas del tope
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();

            if (! DiyInvitations::canCreateDraft($user)) {
                throw ValidationException::withMessages(['quota' => $this->draftsMessage()]);
            }

            $eventDate = Carbon::parse($request->validated('event_date'), config('app.timezone'));

            $invitation = Invitation::create([
                ...$request->safe()->except('modulos_data', 'status'),
                'slug' => Str::slug($request->validated('slug')),
                // Siempre es de quien la crea; nace sin publicar y sin nivel (todo desbloqueado)
                'user_id' => $user->id,
                'reseller_id' => null,
                'is_diy' => true,
                'package' => null,
                'status' => 'inactive',
                'expires_at' => DiyInvitations::expiresAt($eventDate, null),
            ]);

            $this->syncModules($invitation, $request->modulesData());

            return $invitation;
        });

        $modulos = $this->moduleService->resolveModules($invitation);

        InvitationPreviewSession::seed($invitation, InvitationPreviewSession::payloadFromInvitation($invitation, $modulos));
        InvitationUpdated::dispatch($invitation);

        return redirect()
            ->route('client.diy.edit', $invitation)
            ->with('success', 'Tu invitación quedó guardada como borrador. Prueba todas las partes que quieras: al publicarla eliges si va gratis o con más.');
    }

    public function edit(Invitation $invitation): View
    {
        $invitation->loadMissing('eventType');
        $modulos = $this->modulesFromOldInput() ?? $this->moduleService->resolveModules($invitation);

        InvitationPreviewSession::seed($invitation, InvitationPreviewSession::payloadFromInvitation($invitation, $modulos));

        return view('client.diy.edit', $this->editorData->make(
            invitation: $invitation,
            modulos: $modulos,
            isCreate: false,
            context: InvitationEditorViewData::DIY,
            owner: auth()->user(),
        ));
    }

    public function update(UpdateDiyInvitationRequest $request, Invitation $invitation): RedirectResponse
    {
        $previousSlug = $invitation->slug;
        $wasPublished = $invitation->status === 'active';
        // «Publicar» del editor llega como status=active: no publica por sí solo (ver abajo)
        $wantsPublished = $request->validated('status') === 'active';

        DB::transaction(function () use ($request, $invitation, $wantsPublished) {
            $invitation->fill([
                ...$request->safe()->except('modulos_data', 'status'),
                'slug' => Str::slug($request->validated('slug')),
            ]);
            // El vencimiento sigue a la fecha del evento
            $invitation->expires_at = DiyInvitations::expiresAt($invitation->event_date, $invitation->package);

            // Ocultarla siempre se puede; publicarla, desde la página de publicar
            if (! $wantsPublished) {
                $invitation->status = 'inactive';
            }

            $invitation->save();
            $this->syncModules($invitation, $request->modulesData());
        });

        $invitation->refresh();
        $modulos = $this->moduleService->resolveModules($invitation);

        InvitationPreviewSession::seed($invitation, InvitationPreviewSession::payloadFromInvitation($invitation, $modulos));
        InvitationUpdated::dispatch($invitation, $previousSlug);

        // Pidió publicarla (o subirla de nivel): la página de publicar dice cómo (gratis, con un nivel o otra vez)
        if ($wantsPublished && (! $wasPublished || $request->input('next') === 'publish')) {
            return redirect()->route('client.diy.publish', $invitation);
        }

        $message = match (true) {
            $wasPublished && ! $wantsPublished => 'Tu invitación quedó oculta: tus invitados no la verán hasta que la publiques otra vez.',
            $wasPublished && ! DiyInvitations::isCovered($invitation, $modulos) => 'Cambios guardados. Lo que encendiste de un nivel mayor no se verá en tu invitación hasta que la subas de nivel.',
            default => 'Cambios guardados.',
        };

        return redirect()->route('client.diy.edit', $invitation)->with('success', $message);
    }

    /**
     * Antes de publicar: el nivel que piden sus partes y las dos salidas, publicarla gratis (apagando
     * lo que la gratuita no trae) o con ese nivel. También sirve para subir de nivel una ya publicada.
     */
    public function publish(Request $request, Invitation $invitation): View|RedirectResponse
    {
        $user = $request->user();
        $modulos = $this->moduleService->resolveModules($invitation);
        $covered = DiyInvitations::isCovered($invitation, $modulos);

        if ($covered && $invitation->status === 'active') {
            return redirect()->route('client.diy.edit', $invitation)->with('success', 'Tu invitación ya está publicada con todo lo que tiene encendido.');
        }

        $tier = DiyInvitations::publishTier($invitation, $modulos);
        $names = Packages::tierNames();
        // La gratuita se ofrece si todavía es borrador (o ya es gratuita) y le queda una en la cuenta
        $canGoFree = ($invitation->package === null && DiyInvitations::freeAvailable($user, $invitation))
            || $invitation->package === Packages::FREE;

        return view('client.diy.publish', [
            'invitation' => $invitation,
            'covered' => $covered,
            'tier' => $tier,
            'tierName' => $names[$tier] ?? $tier,
            'currentTierName' => Packages::isTier($invitation->package) ? ($names[$invitation->package] ?? null) : null,
            // Lo que lleva la invitación a ese nivel (lo de más de Gratis), con el nivel de cada cosa
            'paidParts' => DiyInvitations::modulesAbove($invitation, $modulos, Packages::FREE),
            // Lo mismo que el paquete del equipo de ese nivel («Todo lo del nivel Básico», no del paquete)
            'tierFeatures' => array_map(
                fn (string $feature) => str_replace('del paquete', 'del nivel', $feature),
                collect(config('bida.packages', []))->firstWhere('key', $tier)['features'] ?? [],
            ),
            'paidUntil' => DiyInvitations::expiresAt($invitation->event_date, $tier),
            'canGoFree' => $canGoFree,
            'freeUsedElsewhere' => $invitation->package === null && ! $canGoFree,
            'freeOff' => DiyInvitations::modulesAbove($invitation, $modulos, Packages::FREE),
            'freeUntil' => DiyInvitations::expiresAt($invitation->event_date, Packages::FREE),
            // El pago en línea de cada nivel llega después: mientras, se activa por WhatsApp
            'contactUrl' => LeadSource::whatsappUrl(
                $request,
                "Hola {brand}, quiero publicar mi invitación «{$invitation->title}» con el nivel {$names[$tier]}.",
                'HAZLO',
            ),
            'needsEmail' => ! $user->hasVerifiedEmail(),
            'hasEmail' => filled($user->email),
        ]);
    }

    /**
     * Publicarla: «free» la deja gratuita (apaga lo que no incluye, una por cuenta); «covered» la vuelve
     * a publicar con el nivel que ya tiene, si alcanza para lo que tiene encendido.
     */
    public function storePublish(Request $request, Invitation $invitation): RedirectResponse
    {
        $mode = $request->validate(['mode' => ['required', 'in:free,covered']])['mode'];
        $user = $request->user();

        // Publicar es lo único que exige el correo confirmado: así se sabe a quién avisar
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('client.diy.publish', $invitation)
                ->withErrors(['publish' => 'Confirma tu correo para publicar tu invitación.']);
        }

        DB::transaction(function () use ($user, $invitation, $mode) {
            // Con el usuario bloqueado: dos pestañas no pueden gastar juntas la misma invitación gratuita
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->first();
            $invitation->refresh();
            $modulos = $this->moduleService->resolveModules($invitation);

            if ($mode === 'covered') {
                if (! DiyInvitations::isCovered($invitation, $modulos)) {
                    throw ValidationException::withMessages(['publish' => 'Tu invitación tiene partes de un nivel que todavía no tiene.']);
                }
            } else {
                if ($invitation->package !== null && $invitation->package !== Packages::FREE) {
                    throw ValidationException::withMessages(['publish' => 'Tu invitación ya tiene un nivel pagado: no hace falta publicarla gratis.']);
                }

                if ($invitation->package !== Packages::FREE && ! DiyInvitations::freeAvailable($user, $invitation)) {
                    throw ValidationException::withMessages(['publish' => 'Ya usaste tu invitación gratuita. Esta se publica con un nivel pagado.']);
                }

                // Lo que la gratuita no trae se apaga; su contenido queda guardado por si sube de nivel
                $this->moduleService->syncAllModules($invitation, DiyInvitations::withoutModulesAbove($invitation, $modulos, Packages::FREE));
                $invitation->package = Packages::FREE;
            }

            $invitation->status = 'active';
            $invitation->expires_at = DiyInvitations::expiresAt($invitation->event_date, $invitation->package);
            $invitation->save();
        });

        InvitationUpdated::dispatch($invitation);

        return redirect()->route('client.dashboard')
            ->with('success', "¡«{$invitation->title}» ya está publicada! Copia el enlace y compártelo con tus invitados.");
    }

    /** Borrar un borrador que nunca se publicó (policy deleteOwn): libera su lugar para crear otro. */
    public function destroy(Invitation $invitation): RedirectResponse
    {
        $title = $invitation->title;

        DB::transaction(fn () => $invitation->delete());
        InvitationCacheService::invalidate($invitation);

        return redirect()->route('client.dashboard')->with('success', "Borramos el borrador «{$title}».");
    }

    private function draftsMessage(): string
    {
        $limit = DiyInvitations::draftsLimit();

        return "Ya tienes {$limit} invitaciones sin publicar. Publica una o borra la que no uses para crear otra.";
    }
}
