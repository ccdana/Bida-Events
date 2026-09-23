{{--
    Pedidos de archivos: se arman en segundo plano y el aviso de arriba muestra cuándo están listos.
    Recibe $invitation y, opcional, $isCard (una tarjeta solo descarga su propia versión impresa).
--}}
@php
    $buttons = [
        'guests-excel' => ['Lista en Excel', 'file-xls', 'Para el salón y el catering: nombres, pases y mesas.'],
        'guests-pdf' => ['Reporte en PDF', 'file-pdf', 'Un resumen de quién confirmó, listo para imprimir.'],
        'invitation-pdf' => ['Invitación en PDF', 'envelope-simple', 'Tu invitación tal como se ve, en dos hojas para imprimir.'],
    ];
    $isCard ??= false;
    $types = $isCard ? App\Models\InvitationExport::CARD_TYPES : array_keys(App\Models\InvitationExport::TYPES);

    if ($isCard) {
        $buttons['invitation-pdf'] = ['Tarjeta en PDF', 'envelope-simple', 'Tu tarjeta tal como se ve, lista para imprimir.'];
    }
@endphp

@foreach($types as $type)
    @php([$label, $icon, $help] = $buttons[$type])
    <form method="POST" action="{{ route('client.export.store', [$invitation, $type]) }}" class="max-w-[16rem] flex-1">
        @csrf
        <button type="submit" class="admin-link-button w-full justify-center">
            <x-dynamic-component :component="'phosphor-'.$icon" aria-hidden="true" />
            {{ $label }}
        </button>
        <p class="mt-1.5 text-xs text-site-muted">{{ $help }}</p>
    </form>
@endforeach
