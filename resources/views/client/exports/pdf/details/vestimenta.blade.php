<div class="block">
    <h3 class="block-title">Cómo vestir</h3>
    @if($dressCode['style'])
        <p class="lead">{{ $dressCode['style'] }}</p>
    @endif
    @if($dressCode['description'])
        <p>{{ $dressCode['description'] }}</p>
    @endif
    @if($dressCode['colors'])
        <table class="swatches">
            @foreach(array_chunk($dressCode['colors'], 2) as $colorRow)
                <tr>
                    @foreach($colorRow as $color)
                        <td><span class="swatch" style="background: {{ $color['hex'] }};"></span>{{ $color['name'] }}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    @endif
    @foreach($dressCode['suggestions'] as $suggestion)
        <p style="margin-top: 2mm;">
            <span class="strong">{{ $suggestion['title'] }}</span>@if($suggestion['for']) <span class="muted">({{ $suggestion['for'] }})</span>@endif
            @if($suggestion['description'])
                <br><span class="muted">{{ $suggestion['description'] }}</span>
            @endif
        </p>
    @endforeach
    @if($dressCode['avoid'])
        <p style="margin-top: 2mm;"><span class="strong">Evitar:</span> {{ implode(', ', $dressCode['avoid']) }}.</p>
    @endif
</div>
