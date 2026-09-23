{{--
    «Sopla las velas»: banderines colgados, confeti y el nombre en grande sobre un bloque de
    color, como la portada del pastel.
--}}
<style>
    .fiesta-bunting { width: 100%; height: 22mm; }
    .fiesta-confeti { width: 100%; height: 14mm; }
    .fiesta-name { background: {{ $colors['tint'] }}; padding: 9mm 8mm; text-align: center; }
    .fiesta-body { padding: 8mm 6mm 4mm; text-align: center; }
</style>

<div class="sheet is-block" style="border: 1.5pt solid {{ $colors['primary'] }}; padding: 5mm;">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell">
                <img class="fiesta-bunting" src="{{ $motifs['main'] }}" alt="">

                <div class="fiesta-name">
                    <p class="kicker">{{ $hero['subtitle'] }}</p>
                    <h1 class="name">{{ $hero['name'] }}</h1>
                </div>

                <img class="fiesta-confeti" src="{{ $motifs['confeti'] }}" alt="">

                <div class="fiesta-body">
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
