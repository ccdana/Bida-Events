@php
    use App\Services\InvitationPreviewSession;

    $inv = $invitation;
    $defaultEventDate = $inv?->event_date?->format('Y-m-d\TH:i') ?? now()->addMonths(3)->format('Y-m-d\TH:i');
    $defaultExpires = $inv?->expires_at?->format('Y-m-d') ?? now()->addMonths(9)->format('Y-m-d');

    $editorConfig = [
        'modules' => $modulos,
        'clients' => $clients->map(fn ($client) => [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
        ])->values(),
        'meta' => [
            'title' => $inv?->title ?? '',
            'slug' => $inv?->slug ?? '',
            'template' => $inv?->template ?? array_key_first($templates),
            'event_type_id' => $inv?->event_type_id ?? ($eventTypes->first()?->id),
            'user_id' => $inv?->user_id ?? '',
            'event_date' => $defaultEventDate,
            'expires_at' => $defaultExpires,
            'status' => $inv?->status ?? 'draft',
        ],
        'isCreate' => $isCreate ?? false,
        'slugManual' => !($isCreate ?? false),
        'previewUrl' => route('admin.preview.frame'),
        'previewStoreUrl' => route('admin.preview.store'),
        'clientStoreUrl' => route('admin.clients.store'),
        'mediaUploadUrl' => route('admin.media.upload'),
        'mapsSearchUrl' => route('admin.maps.search'),
        'mapsResolveUrl' => route('admin.maps.resolve'),
        'itineraryIcons' => $itineraryIcons,
        'cloudinaryConfigured' => $cloudinaryConfigured,
            'moduleCodes' => $moduleCodes ?? [],
            'moduleTabMap' => $moduleTabMap ?? [],
            'previewKey' => InvitationPreviewSession::keyFor($inv),
            'previewRevision' => $inv?->updated_at?->timestamp ?? 0,
        ];
@endphp

@include('admin.invitations.editor.script')
@include('admin.invitations.editor.layout', ['editorConfig' => $editorConfig])
