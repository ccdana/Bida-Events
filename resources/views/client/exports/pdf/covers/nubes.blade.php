{{--
    «Entre nubes»: el cielo con la paloma arriba, el nombre sobre el fondo claro y las nubes
    otra vez al pie, como la portada que se abre entre nubes.
--}}
<style>
    .nubes-sky { background: {{ $colors['tint'] }}; padding: 6mm 0 0; }
    .nubes-sky img { width: 100%; height: 24mm; }
    .nubes-body { padding: 10mm 8mm; text-align: center; }
    .nubes-ground { background: {{ $colors['tint'] }}; padding: 5mm 0 6mm; text-align: center; }
    .nubes-ground img { width: 54mm; margin: 0 auto; }
</style>

<div class="sheet is-none" style="border: 0.8pt solid {{ $colors['line'] }};">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell">
                <div class="nubes-sky">
                    <p class="kicker center">{{ $hero['subtitle'] }}</p>
                    <img src="{{ $motifs['main'] }}" alt="">
                </div>

                <div class="nubes-body">
                    <h1 class="name">{{ $hero['name'] }}</h1>

                    @if($hero['message'])
                        <p class="message" style="margin-top: 5mm;">{{ $hero['message'] }}</p>
                    @endif

                    @include('client.exports.pdf.partials.when')
                </div>

                <div class="nubes-ground"><img src="{{ $motifs['seal'] }}" alt="" style="width: 20mm;"></div>
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
