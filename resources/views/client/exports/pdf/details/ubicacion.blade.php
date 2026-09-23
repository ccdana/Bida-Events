<div class="block">
    <h3 class="block-title">Cómo llegar</h3>
    @if($location['name'])
        <p class="lead">{{ $location['name'] }}</p>
    @endif
    @if($location['address'])
        <p>{{ $location['address'] }}</p>
    @endif
    @if($location['note'])
        <p class="note">{{ $location['note'] }}</p>
    @endif
    @if($location['qr'])
        <img class="qr-small" src="{{ $location['qr'] }}" alt="" style="margin: 2.5mm 0 0;">
        <p class="caption" style="text-align: left;">Escanea para abrir el mapa</p>
    @endif
</div>
