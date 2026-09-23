{{-- Fecha, hora y lugar: lo que el invitado busca primero. Recibe $hero y $location. --}}
@if($hero['date'] || $hero['time'] || $location)
    <table class="when">
        <tr>
            @if($hero['date'])
                <td>
                    <p class="label">Fecha</p>
                    <p class="value">{{ $hero['date'] }}</p>
                </td>
            @endif
            @if($hero['time'])
                <td @class(['sep' => $hero['date']])>
                    <p class="label">Hora</p>
                    <p class="value">{{ $hero['time'] }}</p>
                </td>
            @endif
            @if($location)
                <td @class(['sep' => $hero['date'] || $hero['time']])>
                    <p class="label">Lugar</p>
                    <p class="value">{{ $location['name'] ?? $location['address'] }}</p>
                </td>
            @endif
        </tr>
    </table>
@endif
