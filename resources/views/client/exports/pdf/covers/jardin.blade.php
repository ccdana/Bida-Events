{{--
    «Promesa en el jardín»: las ramas que se abren arriba, los nombres al centro y el lacre con
    las iniciales al pie, igual que el sobre que se abre al entrar.
--}}
<style>
    .jardin-branches { width: 92mm; margin: 0 auto 5mm; }
    .jardin-branches.is-foot { margin: 6mm auto 0; }
    .jardin-seal { width: 22mm; margin: 6mm auto 0; }
    .jardin-body { padding: 4mm 6mm; text-align: center; }
</style>

<div class="sheet is-thin">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell jardin-body">
                <img class="jardin-branches" src="{{ $motifs['main'] }}" alt="">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>
                <img class="jardin-seal" src="{{ $motifs['seal'] }}" alt="">

                @if($hero['message'])
                    <p class="message" style="margin-top: 6mm;">{{ $hero['message'] }}</p>
                @endif

                @include('client.exports.pdf.partials.when')

                <img class="jardin-branches is-foot" src="{{ $motifs['main'] }}" alt="">
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
