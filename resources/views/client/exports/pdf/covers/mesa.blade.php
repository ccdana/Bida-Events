{{--
    «Mesa de honor»: la tarjeta del lugar con los nombres y, debajo, el menú de la celebración con su
    doble filete de oro, el mensaje y el día, la hora y el salón.
--}}
<style>
    .ms-place { width: 118mm; margin: 4mm auto 0; padding: 7mm 8mm 6mm; border: 0.6pt solid {{ $colors['line'] }}; border-top: 3mm solid {{ $colors['tint'] }}; }
    .ms-place .name { margin: 0; }
    .ms-menu { width: 128mm; margin: 7mm auto 0; padding: 2.5mm; border: 1.2pt solid {{ $colors['line'] }}; }
    .ms-menu-inner { padding: 7mm 8mm; border: 0.5pt solid {{ $colors['line'] }}; background: {{ $colors['tint'] }}; }
    .ms-menu-title { margin: 0 0 4mm; font-style: italic; font-size: 14pt; color: {{ $colors['accent'] }}; }
    .ms-branch { width: 36mm; margin: 5mm auto 0; }
</style>

<div class="sheet is-double">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <p class="kicker">{{ $hero['subtitle'] }}</p>

                <div class="ms-place">
                    <h1 class="name">{{ $hero['name'] }}</h1>
                </div>

                <div class="ms-menu">
                    <div class="ms-menu-inner">
                        <p class="ms-menu-title">{{ $copy['table_menu'] ?? 'Menú de la celebración' }}</p>
                        @if($hero['message'])
                            <p class="message">{{ $hero['message'] }}</p>
                        @endif
                        @include('client.exports.pdf.partials.when')
                    </div>
                </div>

                <img class="ms-branch" src="{{ $motifs['main'] }}" alt="">
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
