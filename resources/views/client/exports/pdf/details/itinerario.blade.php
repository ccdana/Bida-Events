<div class="block">
    <h3 class="block-title">{{ $copy['itinerary_eyebrow'] ?? 'Así será el día' }}</h3>
    <table class="timeline">
        @foreach($itinerary as $item)
            <tr>
                <td class="time">{{ $item['time'] }}</td>
                <td>
                    <p class="strong">{{ $item['title'] }}</p>
                    @if($item['description'])
                        <p class="muted">{{ $item['description'] }}</p>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    @if($omitted['itinerario'] ?? 0)
        <p class="caption" style="text-align: left;">
            Y {{ $omitted['itinerario'] }} {{ $omitted['itinerario'] === 1 ? 'momento más' : 'momentos más' }} en la invitación digital.
        </p>
    @endif
</div>
