{{--
    «Partitura a dos voces»: la primera página de la partitura. La obra arriba, el nombre sobre un
    pentagrama de cinco líneas con la barra final, y las indicaciones de la función.
--}}
<style>
    .pt-head { width: 100%; border-collapse: collapse; border-bottom: 0.6pt solid {{ $colors['ink'] }}; }
    .pt-head td { padding-bottom: 2mm; font-size: 8pt; letter-spacing: 2pt; text-transform: uppercase; color: {{ $colors['muted'] }}; }
    .pt-work { margin: 10mm 0 0; font-style: italic; font-size: 17pt; }
    .pt-staff { width: 150mm; margin: 6mm auto 0; border-collapse: collapse; }
    .pt-staff td { height: 3.2mm; padding: 0; border-bottom: 0.5pt solid {{ $colors['line'] }}; }
    .pt-staff td.bar { width: 1.6mm; border-right: 1.6mm solid {{ $colors['ink'] }}; }
    .pt-name { margin: 6mm 0 0; }
</style>

<div class="sheet is-none" style="padding: 10mm 12mm;">
    <table class="pt-head">
        <tr>
            <td>{{ $copy['score_program'] ?? 'Programa' }}</td>
            <td style="text-align: right;">{{ $copy['score_opus'] ?? 'Op. 1' }}</td>
        </tr>
    </table>

    <table class="cover-fill" style="height: {{ max(120, $cover['fill'] - 20) }}mm;">
        <tr>
            <td class="fill-cell center">
                <p class="pt-work">{{ $copy['score_title'] ?? 'Concierto para dos voces' }}</p>
                <p class="kicker" style="margin-top: 4mm;">{{ $hero['subtitle'] }}</p>
                <h1 class="name pt-name">{{ $hero['name'] }}</h1>

                <table class="pt-staff">
                    @for($line = 0; $line < 5; $line++)
                        <tr><td></td><td class="bar"></td></tr>
                    @endfor
                </table>

                @if($hero['message'])
                    <p class="message" style="margin-top: 7mm; font-style: italic;">{{ $hero['message'] }}</p>
                @endif

                @include('client.exports.pdf.partials.when')
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
