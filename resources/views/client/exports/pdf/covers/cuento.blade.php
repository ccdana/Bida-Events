{{--
    «Cuento desplegable»: la portadilla del libro. «Érase una vez», el título «El cuento de» con su
    nombre, la viñeta de flor, el mensaje y el colofón con la fecha, la hora y el lugar.
--}}
<style>
    .cu-page { width: 132mm; margin: 4mm auto 0; padding: 10mm 9mm; border: 0.6pt solid {{ $colors['line'] }}; background: {{ $colors['tint'] }}; }
    .cu-once { margin: 0; font-style: italic; font-size: 14pt; color: {{ $colors['accent'] }}; }
    .cu-title { margin: 5mm 0 0; font-size: 8pt; letter-spacing: 3pt; text-transform: uppercase; color: {{ $colors['muted'] }}; }
    .cu-page .name { margin: 2mm 0 0; }
    .cu-page .fleuron { width: 34mm; margin: 5mm auto 0; }
</style>

<div class="sheet is-double">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <p class="kicker">{{ $hero['subtitle'] }}</p>

                <div class="cu-page">
                    <p class="cu-once">{{ $copy['book_opening'] ?? 'Érase una vez…' }}</p>
                    <p class="cu-title">{{ $copy['book_title'] ?? 'El cuento de' }}</p>
                    <h1 class="name">{{ $hero['name'] }}</h1>
                    <img class="fleuron" src="{{ $motifs['main'] }}" alt="">

                    @if($hero['message'])
                        <p class="message" style="margin-top: 6mm;">{{ $hero['message'] }}</p>
                    @endif
                </div>

                @include('client.exports.pdf.partials.when')
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
