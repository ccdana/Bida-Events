{{--
    «Dos caminos»: cada nombre en un extremo con su camino punteado y, entre los dos, el aro donde
    se juntan con las iniciales. Debajo, la frase del encuentro y la fecha, la hora y el lugar.
--}}
@php
    $mapNames = preg_split('/\s+(?:&|\+|y|e)\s+/iu', trim($hero['name']), 2) ?: [$hero['name']];
    $mapInitials = collect($mapNames)->map(fn (string $name) => mb_strtoupper(mb_substr(trim($name), 0, 1)))->implode(' & ');
@endphp
<style>
    .mapa-names { width: 100%; border-collapse: collapse; }
    .mapa-names td { padding: 0; vertical-align: middle; }
    .mapa-names .name { margin: 0; }
    .mapa-path { border-top: 1.4pt dashed {{ $colors['primary'] }}; }
    .mapa-path.is-b { border-top-color: {{ $colors['ink'] }}; }
    .mapa-ring { width: 26mm; height: 26mm; margin: 5mm auto 0; border: 1.6pt solid {{ $colors['ink'] }}; border-radius: 13mm; text-align: center; }
    .mapa-ring span { display: block; padding-top: 8mm; font-size: 15pt; color: {{ $colors['ink'] }}; }
    .mapa-meet { margin: 8mm 0 0; font-size: 10pt; color: {{ $colors['muted'] }}; }
</style>

<div class="sheet is-thin">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <table class="mapa-names" style="margin-top: 6mm;">
                    <tr>
                        <td style="width: 60%;"><h1 class="name">{{ $mapNames[0] }}</h1></td>
                        <td><div class="mapa-path"></div></td>
                    </tr>
                </table>

                <div class="mapa-ring"><span>{{ $mapInitials }}</span></div>

                @if(isset($mapNames[1]))
                    <table class="mapa-names" style="margin-top: 5mm;">
                        <tr>
                            <td><div class="mapa-path is-b"></div></td>
                            <td style="width: 60%; text-align: right;"><h1 class="name">{{ $mapNames[1] }}</h1></td>
                        </tr>
                    </table>
                @endif

                <div class="center">
                    <p class="mapa-meet">{{ $copy['hero_meet'] ?? 'Nuestros caminos se juntan el' }}</p>
                    @if($hero['message'])
                        <p class="message" style="margin-top: 4mm;">{{ $hero['message'] }}</p>
                    @endif
                    @include('client.exports.pdf.partials.when')
                </div>
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
