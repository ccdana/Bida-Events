{{--
    Hoja 2: los complementos, en dos columnas. El reparto y lo que entra lo decide
    App\Support\Pdf\PrintLayout, para que el PDF siempre salga en dos hojas.
--}}
<table class="details-head">
    <tr>
        <td>
            <p class="kicker">{{ $hero['name'] }}</p>
            <h2 class="details-title">Detalles del evento</h2>
        </td>
        <td style="text-align: right;"><img class="details-motif" src="{{ $motifs['main'] }}" alt=""></td>
    </tr>
</table>

<table @class(['details-cols', 'is-dense' => $details['tight']])>
    <tr>
        @foreach($details['columns'] as $column)
            <td @class(['col-right' => $loop->last])>
                @foreach($column as $view)
                    @include('client.exports.pdf.details.'.$view)
                @endforeach
            </td>
        @endforeach
    </tr>
</table>

<p class="details-foot">
    @if($details['dropped'])
        {{ Str::ucfirst(implode(', ', $details['dropped'])) }} y todo lo demás —galería, música y confirmaciones—
        está en la invitación digital:
    @else
        La galería, la música y las confirmaciones están en la invitación digital:
    @endif
    <span class="strong">{{ $rsvp['url'] }}</span>
</p>
