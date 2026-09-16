{{-- Pedidos de archivos: se arman en segundo plano y el aviso de arriba muestra cuándo están listos. --}}
@foreach(App\Models\InvitationExport::TYPES as $type => [$label, , ])
    <form method="POST" action="{{ route('client.export.store', [$invitation, $type]) }}">
        @csrf
        <button type="submit" class="admin-link-button">
            <x-dynamic-component :component="'phosphor-'.[
                'guests-excel' => 'file-xls',
                'guests-pdf' => 'file-pdf',
                'invitation-pdf' => 'envelope-simple',
            ][$type]" aria-hidden="true" />
            {{ $label }}
        </button>
    </form>
@endforeach
