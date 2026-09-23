{{--
    «Libro de aventuras»: la hoja del cuaderno con su cinta pegada, la rosa de los vientos y el
    nombre escrito al centro, como la página que se hojea en el celular.
--}}
<style>
    .cuaderno-page { background: {{ $colors['tint'] }}; padding: 9mm 10mm; text-align: center; }
    .cuaderno-tape { width: 30mm; margin: 0 auto 6mm; }
    .cuaderno-rule { margin: 7mm 0; border-top: 0.8pt dashed {{ $colors['line'] }}; }
</style>

<div class="sheet is-dashed">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell">
                <div class="cuaderno-page">
                    <img class="cuaderno-tape" src="{{ $motifs['cinta'] }}" alt="">
                    <p class="kicker">{{ $hero['subtitle'] }}</p>
                    <h1 class="name">{{ $hero['name'] }}</h1>
                    <img class="motif" src="{{ $motifs['main'] }}" alt="">

                    <div class="cuaderno-rule"></div>

                    @if($hero['message'])
                        <p class="message">{{ $hero['message'] }}</p>
                    @endif

                    @include('client.exports.pdf.partials.when')
                </div>
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
