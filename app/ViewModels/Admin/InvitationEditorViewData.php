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
use App\Support\EditableTexts;
use App\Support\InvitationDefaults;
use App\Support\InvitationTemplates;
use App\Support\Packages;
use App\Support\ResellerSubscription;
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

    public function __construct(
        protected MediaUploadService $mediaUpload
    ) {}

    public function make(?Invitation $invitation, array $modulos, bool $isCreate, string $context = self::ADMIN, ?User $owner = null): array
    {
        $isReseller = $context === self::RESELLER;
        $templates = $isReseller && $owner
            ? collect(ResellerSubscription::allowedTemplates($owner))->map(fn (array $template) => $template['label'])
            : collect(InvitationDefaults::templates());
        $itineraryIcons = InvitationDefaults::itineraryIcons();
        $eventTypes = EventType::orderBy('name')->get();
        $clients = $isReseller ? new Collection : User::where('is_admin', false)->orderBy('name')->get();

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
    ): array {
        $urls = $this->urls($context);

        $defaultEventDate = $invitation?->event_date?->format('Y-m-d\TH:i') ?? now()->addMonths(3)->format('Y-m-d\TH:i');
        $defaultExpires = $invitation?->expires_at?->format('Y-m-d') ?? now()->addMonths(9)->format('Y-m-d');

        return [
            'modules' => $modulos,
            'eventTypes' => $eventTypes->map(fn ($type) => [
                'id' => (string) $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'kind' => $type->kind,
            ])->values(),
            // Perfil de cada tipo de evento: el editor arma pestañas, rótulos y avisos con él
            'profiles' => collect(app(EventProfiles::class)->all())->map(fn (EventProfile $profile) => $profile->toArray()),
            'templateOptions' => $templates->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
                'description' => InvitationTemplates::get($value)['description'],
                'event' => InvitationTemplates::get($value)['event'],
                // Colores y letras con los que nace una invitación nueva de esta plantilla
                'palette' => InvitationTemplates::get($value)['palette'],
                'fonts' => InvitationTemplates::get($value)['fonts'] ?? null,
            ])->values(),
            // La contraseña no viaja: solo se ve la que se acaba de crear o regenerar en el editor
            'clients' => $clientList->map(fn ($client) => [
                'id' => (string) $client->id,
                'name' => $client->name,
                'username' => $client->username,
                'password' => null,
            ])->values(),
            'meta' => [
                'title' => $invitation?->title ?? '',
                'slug' => $invitation?->slug ?? '',
                'template' => $invitation?->template ?? array_key_first($templates->all()),
                'event_type_id' => $invitation?->event_type_id ? (string) $invitation->event_type_id : (string) ($eventTypes->first()?->id ?? ''),
                'user_id' => $invitation?->user_id ? (string) $invitation->user_id : '',
                'event_date' => $defaultEventDate,
                'expires_at' => $defaultExpires,
                'status' => $invitation?->status === 'active' ? 'active' : 'inactive',
                // Paquete vendido; una nueva del equipo arranca en Estándar, la del revendedor no lleva
                'package' => $invitation ? (string) $invitation->package : ($context === 'admin' ? Packages::STANDARD : ''),
            ],
            'isCreate' => $isCreate,
            'slugManual' => ! $isCreate,
            'editorMode' => $context,
            ...$urls,
            'itineraryIcons' => $itineraryIcons,
            // Paletas listas de «Estética»: la original de cada plantilla, las de su evento y las generales
            'palettes' => ColorPalettes::all(),
            // Qué incluye cada paquete: el editor apaga y marca lo que el paquete elegido no trae
            'packageOptions' => collect(config('bida.packages', []))->map(fn (array $package) => ['value' => $package['key'], 'label' => $package['name'], 'hint' => $package['summary'] ?? ''])->values(),
            'packageOrder' => Packages::ORDER,
            'packageModules' => Packages::MODULES,
            // Textos editables de cada plantilla, agrupados por módulo, con el valor que trae la plantilla
            'editableTexts' => $templates->keys()->mapWithKeys(fn ($value) => [$value => EditableTexts::forTemplate($value)]),
            'cloudinaryConfigured' => $this->mediaUpload->isCloudinaryConfigured(),
            'moduleCodes' => InvitationDefaults::moduleCodes(),
            'moduleTabMap' => InvitationDefaults::moduleTabMap(),
            'previewKey' => InvitationPreviewSession::keyFor($invitation),
            'previewRevision' => $invitation?->updated_at?->timestamp ?? 0,
        ];
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
        if ($context === self::RESELLER) {
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
