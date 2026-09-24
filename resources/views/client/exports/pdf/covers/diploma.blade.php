{{--
    «Birrete al aire»: la hoja se imprime como un diploma. Birretes arriba, el nombre en el centro
    entre dos filetes dorados, el sello con las iniciales como el lacre del título y los datos al pie.
--}}
<style>
    .diploma-top { padding: 9mm 8mm 0; text-align: center; }
    .diploma-top img { width: 74mm; margin: 0 auto; }
    .diploma-body { padding: 6mm 12mm 4mm; text-align: center; }
    .diploma-rule { width: 88mm; margin: 5mm auto; border-collapse: collapse; }
    .diploma-rule td { padding: 0; height: 0.5mm; background: {{ $colors['primary'] }}; }
    .diploma-rule td.gap { background: transparent; height: 0.9mm; }
    .diploma-title { font-size: 8pt; letter-spacing: 0.32em; text-transform: uppercase; color: {{ $colors['muted'] }}; margin: 0 0 3mm; }
    .diploma-seal { width: 22mm; margin: 6mm auto 0; }
</style>

<div class="sheet is-double">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell">
                <div class="diploma-top"><img src="{{ $motifs['main'] }}" alt=""></div>

                <div class="diploma-body">
                    <p class="kicker">{{ $hero['subtitle'] }}</p>
                    <table class="diploma-rule"><tr><td></td></tr><tr><td class="gap"></td></tr><tr><td></td></tr></table>
                    <p class="diploma-title">Invitación de graduación</p>
                    <h1 class="name">{{ $hero['name'] }}</h1>
                    <table class="diploma-rule"><tr><td></td></tr><tr><td class="gap"></td></tr><tr><td></td></tr></table>

                    @if($hero['message'])
                        <p class="message">{{ $hero['message'] }}</p>
                    @endif

                    @include('client.exports.pdf.partials.when')

                    <img class="diploma-seal" src="{{ $motifs['seal'] }}" alt="">
                </div>
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
