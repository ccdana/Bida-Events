<?php

namespace App\ViewModels\Admin;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Services\MediaUploadService;
use App\Support\InvitationDefaults;
use App\Services\InvitationPreviewSession;
use Illuminate\Support\Collection;

class InvitationEditorViewData
{
    public function __construct(
        protected MediaUploadService $mediaUpload
    ) {}

    public function make(?Invitation $invitation, array $modulos, bool $isCreate): array
    {
        $templates = collect(InvitationDefaults::templates());
        $itineraryIcons = InvitationDefaults::itineraryIcons();
        $eventTypes = EventType::orderBy('name')->get();
        $clients = User::where('is_admin', false)->orderBy('name')->get();

        $clientList = $clients;
        if ($invitation?->user_id && ! $clientList->contains('id', $invitation->user_id)) {
            $invitation->loadMissing('user');
            if ($invitation->user) {
                $clientList = $clientList->prepend($invitation->user)->unique('id')->values();
            }
        }

        $assignedClientPassword = $invitation?->user_id
            ? (session('client_temp_passwords', [])[$invitation->user_id] ?? null)
            : null;

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
                assignedClientPassword: $assignedClientPassword,
                itineraryIcons: $itineraryIcons,
            ),
            'isCreate' => $isCreate,
        ];
    }

    protected function editorConfig(
        ?Invitation $invitation,
        array $modulos,
        bool $isCreate,
        Collection $templates,
        Collection $eventTypes,
        Collection $clientList,
        ?string $assignedClientPassword,
        array $itineraryIcons,
    ): array {
        $defaultEventDate = $invitation?->event_date?->format('Y-m-d\TH:i') ?? now()->addMonths(3)->format('Y-m-d\TH:i');
        $defaultExpires = $invitation?->expires_at?->format('Y-m-d') ?? now()->addMonths(9)->format('Y-m-d');
        $clientPasswords = session('client_temp_passwords', []);

        return [
            'modules' => $modulos,
            'eventTypes' => $eventTypes->map(fn ($type) => [
                'id' => (string) $type->id,
                'name' => $type->name,
            ])->values(),
            'templateOptions' => $templates->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
            'clients' => $clientList->map(fn ($client) => [
                'id' => (string) $client->id,
                'name' => $client->name,
                'email' => $client->email,
            ])->values(),
            'clientPasswords' => $clientPasswords,
            'assignedClientPassword' => $assignedClientPassword,
            'meta' => [
                'title' => $invitation?->title ?? '',
                'slug' => $invitation?->slug ?? '',
                'template' => $invitation?->template ?? array_key_first($templates->all()),
                'event_type_id' => $invitation?->event_type_id ? (string) $invitation->event_type_id : (string) ($eventTypes->first()?->id ?? ''),
                'user_id' => $invitation?->user_id ? (string) $invitation->user_id : '',
                'event_date' => $defaultEventDate,
                'expires_at' => $defaultExpires,
                'status' => $invitation?->status ?? 'draft',
            ],
            'isCreate' => $isCreate,
            'slugManual' => ! $isCreate,
            'previewUrl' => route('admin.preview.frame'),
            'previewStoreUrl' => route('admin.preview.store'),
            'clientStoreUrl' => route('admin.clients.store'),
            'mediaUploadUrl' => route('admin.media.upload'),
            'mapsSearchUrl' => route('admin.maps.search'),
            'mapsResolveUrl' => route('admin.maps.resolve'),
            'itineraryIcons' => $itineraryIcons,
            'cloudinaryConfigured' => $this->mediaUpload->isCloudinaryConfigured(),
            'moduleCodes' => InvitationDefaults::moduleCodes(),
            'moduleTabMap' => InvitationDefaults::moduleTabMap(),
            'previewKey' => InvitationPreviewSession::keyFor($invitation),
            'previewRevision' => $invitation?->updated_at?->timestamp ?? 0,
        ];
    }
}
