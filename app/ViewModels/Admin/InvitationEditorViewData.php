<?php

namespace App\ViewModels\Admin;

use App\EventProfiles\EventProfile;
use App\EventProfiles\EventProfiles;
use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationPreviewSession;
use App\Services\MediaUploadService;
use App\Support\ColorPalettes;
use App\Support\DiyInvitations;
use App\Support\EditableTexts;
use App\Support\ImageFrames;
use App\Support\InvitationDefaults;
use App\Support\InvitationTemplates;
use App\Support\Packages;
use App\Support\ResellerSubscription;
use App\Support\SampleContent;
use App\Support\ShowcaseDemos;
use App\Support\TemplateAvailability;
use Illuminate\Support\Collection;

/**
 * Todo lo que necesita el editor de invitaciones en una sola estructura. Lo usan dos editores con
 * los mismos paneles: el del administrador y el del revendedor. El del revendedor no ve la lista de
 * clientes (son datos de otras personas), manda sus pedidos a rutas propias bajo /client y solo
 * ofrece las plantillas de su plan.
 */
class InvitationEditorViewData
{
    public const ADMIN = 'admin';

    public const RESELLER = 'reseller';

    /** «Hazlo tú»: el dueño arma la suya; todo desbloqueado y el nivel se ve parte por parte. */
    public const DIY = 'diy';

    public function __construct(
        protected MediaUploadService $mediaUpload
    ) {}

    public function make(?Invitation $invitation, array $modulos, bool $isCreate, string $context = self::ADMIN, ?User $owner = null): array
    {
        // El revendedor y «Hazlo tú» no eligen cliente: la invitación es de quien la arma
        $isReseller = $context !== self::ADMIN;
        $templates = match (true) {
            $context === self::RESELLER && $owner !== null => collect(ResellerSubscription::allowedTemplates($owner))->map(fn (array $template) => $template['label']),
            $context === self::DIY => collect(DiyInvitations::allowedTemplates())->map(fn (array $template) => $template['label']),
            default => collect(InvitationDefaults::templates()),
        };
        $itineraryIcons = InvitationDefaults::itineraryIcons();
        $eventTypes = EventType::orderBy('name')->get();
        $clients = $isReseller ? new Collection : User::where('is_admin', false)->with('createdByReseller:id,name,business_name')->orderBy('name')->get();

        $clientList = $clients;
        if (! $isReseller && $invitation?->user_id && ! $clientList->contains('id', $invitation->user_id)) {
            $invitation->loadMissing('user');
            if ($invitation->user) {
                $clientList = $clientList->prepend($invitation->user)->unique('id')->values();
            }
        }

        return [
            'invitation' => $invitation,
            'modulos' => $modulos,
            'templates' => $templates,
            'itineraryIcons' => $itineraryIcons,
            'eventTypes' => $eventTypes,
            'clients' => $clients,
            'cloudinaryConfigured' => $this->mediaUpload->isCloudinaryConfigured(),
            'moduleCodes' => InvitationDefaults::moduleCodes(),
            'moduleTabMap' => InvitationDefaults::moduleTabMap(),
            'editorConfig' => $this->editorConfig(
                invitation: $invitation,
                modulos: $modulos,
                isCreate: $isCreate,
                templates: $templates,
                eventTypes: $eventTypes,
                clientList: $clientList,
                itineraryIcons: $itineraryIcons,
                context: $context,
                owner: $owner,
            ),
            'isCreate' => $isCreate,
            'editorMode' => $context,
        ];
    }

    protected function editorConfig(
        ?Invitation $invitation,
        array $modulos,
        bool $isCreate,
        Collection $templates,
        Collection $eventTypes,
        Collection $clientList,
        array $itineraryIcons,
        string $context = self::ADMIN,
        ?User $owner = null,
    ): array {
        $urls = $this->urls($context);
        $captures = $this->templateCaptures();

        $defaultEventDate = $invitation?->event_date?->format('Y-m-d\TH:i') ?? now()->addMonths(3)->setTime(19, 0)->format('Y-m-d\TH:i');
        $defaultExpires = $invitation?->expires_at?->format('Y-m-d') ?? now()->addMonths(9)->format('Y-m-d');

        return [
            'modules' => $modulos,
            // Agrupados (todo el año, primaveral y romántico, tenebroso) y con el motivo si hoy no se ofrecen;
            // el de la invitación que se edita siempre se puede mantener
            'eventTypes' => $eventTypes->map(fn ($type) => [
                'id' => (string) $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'kind' => $type->kind,
                'category' => TemplateAvailability::category($type->season),
                'disabledReason' => (int) $invitation?->event_type_id === (int) $type->id ? null : TemplateAvailability::seasonReason($type->season),
            ])->values(),
            'eventCategories' => TemplateAvailability::categoryOrder(),
            // Perfil de cada tipo de evento: el editor arma pestañas, rótulos y avisos con él
            'profiles' => collect(app(EventProfiles::class)->all())->map(fn (EventProfile $profile) => $profile->toArray()),
            'templateOptions' => $templates->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
                'description' => InvitationTemplates::get($value)['description'],
                // Cómo empieza su muestra: el asistente de creación la enseña al elegir el diseño
                'capture' => $captures[$value] ?? null,
                'event' => InvitationTemplates::get($value)['event'],
                // Colores y letras con los que nace una invitación nueva de esta plantilla
                'palette' => InvitationTemplates::get($value)['palette'],
                'fonts' => InvitationTemplates::get($value)['fonts'] ?? null,
                // Para qué usa cada color esta plantilla (las que no lo dicen muestran el uso general)
                'colorUsage' => InvitationTemplates::get($value)['color_usage'] ?? null,
                // Apagada en Ajustes o de una temporada que no está a la venta (la actual se conserva)
                'disabledReason' => $value === $invitation?->template ? null : TemplateAvailability::templateReason($value),
            ])->values(),
            // La contraseña no viaja: solo se ve la que se acaba de crear o regenerar en el editor
            'clients' => $clientList->map(fn ($client) => [
                'id' => (string) $client->id,
                'name' => $client->name,
                'username' => $client->username,
                'password' => null,
                // De dónde viene: un revendedor también puede ser cliente de una invitación del equipo
                'origin' => match (true) {
                    (bool) $client->is_reseller => 'Revendedor',
                    $client->createdByReseller !== null => 'Cliente de '.($client->createdByReseller->business_name ?: $client->createdByReseller->name),
                    default => null,
                },
            ])->values(),
            'meta' => [
                'title' => $invitation?->title ?? '',
                'slug' => $invitation?->slug ?? '',
                // Una nueva arranca en la primera plantilla que hoy se ofrece
                'template' => $invitation?->template
                    ?? $templates->keys()->first(fn (string $key) => TemplateAvailability::templateReason($key) === null)
                    ?? array_key_first($templates->all()),
                'event_type_id' => $invitation?->event_type_id ? (string) $invitation->event_type_id : (string) ($eventTypes->first()?->id ?? ''),
                'user_id' => $invitation?->user_id ? (string) $invitation->user_id : '',
                'event_date' => $defaultEventDate,
                'expires_at' => $defaultExpires,
                'status' => $invitation?->status === 'active' ? 'active' : 'inactive',
                // Paquete vendido; una nueva del equipo arranca en Estándar, la del revendedor no lleva y
                // la de «Hazlo tú» se arma con todo desbloqueado (su nivel va aparte, en diyTiers)
                'package' => match (true) {
                    $context === self::DIY => '',
                    $invitation !== null => (string) $invitation->package,
                    $context === self::ADMIN => Packages::STANDARD,
                    default => '',
                },
            ],
            'isCreate' => $isCreate,
            'slugManual' => ! $isCreate,
            'editorMode' => $context,
            ...$urls,
            'itineraryIcons' => $itineraryIcons,
            // Paletas listas de «Estética»: la original de cada plantilla, las de su evento y las generales
            'palettes' => ColorPalettes::all(),
            // Medida y forma real de cada espacio de foto: el recortador encuadra con ellas
            'imageFrames' => ImageFrames::forEditor(),
            // Qué incluye cada paquete: el editor apaga y marca lo que el paquete elegido no trae
            'packageOptions' => collect(config('bida.packages', []))
                ->map(fn (array $package) => ['value' => $package['key'], 'label' => $package['name'], 'hint' => $package['summary'] ?? ''])
                // Una de «Hazlo tú» publicada gratis: el administrador la ve (y la puede subir de nivel)
                ->when($invitation?->is_diy && $invitation->package === Packages::FREE, fn ($options) => $options->prepend(['value' => Packages::FREE, 'label' => 'Gratis', 'hint' => 'Hazlo tú: portada, cuenta regresiva y ubicación.']))
                ->values(),
            // Escalera completa (Gratis incluido) y el nivel de cada módulo, para marcar lo que el paquete no trae
            'packageOrder' => Packages::TIERS,
            'packageModules' => $this->moduleTiers(),
            // Revendedor: la confirmación que trae su plan (whatsapp, pass) y desde qué plan viene cada una
            'planRsvp' => $context === self::RESELLER ? array_values((array) ($owner?->planConfig()['rsvp'] ?? [])) : null,
            'planRsvpTiers' => $context === self::RESELLER ? self::rsvpTiers() : null,
            // «Hazlo tú»: el nivel de cada parte, el que ya pagó (null = nunca publicada) y dónde se publica
            'diyTiers' => $context === self::DIY ? $this->diyTiers($invitation) : null,
            // Textos editables de cada plantilla, agrupados por módulo, con el valor que trae la plantilla
            // Contenido de ejemplo de cada evento: al crear la invitación y al encender una parte, aparece
            // con él para tocarlo y cambiarlo; antes de publicar se avisa lo que sigue sin cambiar
            'samples' => SampleContent::forEditor(),
            'samplePlaceholders' => SampleContent::placeholders(),
            // Forma vacía de cada módulo: una parte «vacía» recibe el ejemplo al encenderla
            'moduleDefaults' => InvitationDefaults::emptyModules(),
            // Una invitación nueva empieza por el asistente (evento, diseño y datos); si volvió con
            // errores del guardado, directo al editor para corregirlos
            'wizard' => $isCreate && ! session()->hasOldInput(),
            'editableTexts' => $templates->keys()->mapWithKeys(fn ($value) => [$value => EditableTexts::forTemplate($value)]),
            'cloudinaryConfigured' => $this->mediaUpload->isCloudinaryConfigured(),
            'moduleCodes' => InvitationDefaults::moduleCodes(),
            'moduleTabMap' => InvitationDefaults::moduleTabMap(),
            'previewKey' => InvitationPreviewSession::keyFor($invitation),
            'previewRevision' => $invitation?->updated_at?->timestamp ?? 0,
        ];
    }

    /** Niveles de «Hazlo tú» para el editor: cada parte dice desde qué nivel viene y la barra, el nivel de hoy. */
    private function diyTiers(?Invitation $invitation): array
    {
        return [
            'order' => Packages::TIERS,
            'names' => Packages::tierNames(),
            'modules' => $this->moduleTiers(),
            'paid' => Packages::isTier($invitation?->package) ? $invitation->package : null,
            'publishUrl' => $invitation ? route('client.diy.publish', $invitation) : null,
        ];
    }

    /** Desde qué nivel viene cada módulo (Gratis, Básico, Estándar o Premium). */
    private function moduleTiers(): array
    {
        return collect(InvitationDefaults::moduleCodes())
            ->reject(fn (string $code) => $code === 'config')
            ->mapWithKeys(fn (string $code) => [$code => Packages::moduleTier($code)])
            ->all();
    }

    /** Captura de la muestra de cada plantilla (la primera muestra que la usa), si la hay. */
    private function templateCaptures(): array
    {
        $showcase = rescue(
            fn () => Invitation::whereIn('slug', ShowcaseDemos::slugs())->pluck('template', 'slug')->all(),
            [],
            report: false,
        );

        $captures = [];
        foreach ($showcase as $slug => $template) {
            $captures[$template] ??= ShowcaseDemos::capture($slug);
        }

        return array_filter($captures);
    }

    /** Primer plan de revendedor que trae cada confirmación, para el aviso «Viene en el plan…». */
    private static function rsvpTiers(): array
    {
        $plans = collect(config('bida.reseller_plans', []));
        $first = fn (string $mode) => $plans->first(fn (array $plan) => in_array($mode, (array) ($plan['rsvp'] ?? []), true))['name'] ?? null;

        return ['rsvp_whatsapp' => $first('whatsapp'), 'rsvp' => $first('pass')];
    }

    /**
     * Adónde manda sus pedidos el editor. El revendedor usa los mismos controladores que el
     * administrador (vista previa, subida de archivos, mapas), pero por rutas bajo /client, y no
     * tiene alta de clientes.
     *
     * @return array<string, string|null>
     */
    protected function urls(string $context): array
    {
        if ($context !== self::ADMIN) {
            return [
                'previewUrl' => route('client.editor.preview.frame'),
                'previewStoreUrl' => route('client.editor.preview.store'),
                'clientStoreUrl' => null,
                'clientPasswordUrl' => null,
                'mediaUploadUrl' => route('client.editor.media.upload'),
                'mapsSearchUrl' => route('client.editor.maps.search'),
                'mapsResolveUrl' => route('client.editor.maps.resolve'),
            ];
        }

        return [
            'previewUrl' => route('admin.preview.frame'),
            'previewStoreUrl' => route('admin.preview.store'),
            'clientStoreUrl' => route('admin.clients.store'),
            'clientPasswordUrl' => route('admin.clients.password', ['client' => '__ID__']),
            'mediaUploadUrl' => route('admin.media.upload'),
            'mapsSearchUrl' => route('admin.maps.search'),
            'mapsResolveUrl' => route('admin.maps.resolve'),
        ];
    }
}
