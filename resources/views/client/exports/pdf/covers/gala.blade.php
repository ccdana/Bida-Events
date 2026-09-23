{{--
    «Noche de gala»: el telón oscuro con destellos dorados arriba y abajo, y el nombre en el
    centro de un marco doble, como cuando se abre el telón en el celular.
--}}
<style>
    .gala-curtain { background: {{ $colors['panel'] }}; padding: 7mm 8mm 6mm; text-align: center; }
    .gala-curtain .kicker { color: {{ $colors['onPanelAccent'] }}; }
    .gala-curtain img { width: 62mm; margin: 3mm auto 0; }
    .gala-body { padding: 10mm 8mm; text-align: center; }
    .gala-rule { width: 64mm; margin: 6mm auto; border-collapse: collapse; }
    .gala-rule td { height: 0.8mm; padding: 0; background: {{ $colors['primary'] }}; }
    .gala-foot { padding: 6mm 8mm 7mm; text-align: center; }
    .gala-foot img { width: 48mm; margin: 0 auto; }
</style>

<div class="sheet is-double">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell">
                <div class="gala-curtain">
                    <p class="kicker">{{ $hero['subtitle'] }}</p>
                    <img src="{{ $motifs['ink'] }}" alt="">
                </div>

                <div class="gala-body">
                    <h1 class="name">{{ $hero['name'] }}</h1>
                    <table class="gala-rule"><tr><td></td></tr></table>

                    @if($hero['message'])
                        <p class="message">{{ $hero['message'] }}</p>
                    @endif

                    @include('client.exports.pdf.partials.when')
                </div>

                <div class="gala-foot"><img src="{{ $motifs['main'] }}" alt=""></div>
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
