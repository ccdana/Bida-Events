{{--
    «Esencia XV»: el afiche del lanzamiento, en tipografía. La línea de la fragancia, su nombre en
    versalitas espaciadas como una marca, los filetes finos y el lanzamiento (fecha, hora y lugar).
--}}
<style>
    .ez-line { margin: 0; font-size: 8pt; letter-spacing: 4pt; text-transform: uppercase; color: {{ $colors['accent'] }}; }
    .ez-brand { margin: 8mm 0 0; text-transform: uppercase; letter-spacing: 4pt; }
    .ez-rule { width: 40mm; margin: 7mm auto; border-top: 0.6pt solid {{ $colors['primary'] }}; }
    .ez-launch { margin: 0; font-size: 8pt; letter-spacing: 3pt; text-transform: uppercase; color: {{ $colors['muted'] }}; }
</style>

<div class="sheet is-thin">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <p class="ez-line">{{ $copy['scent_line'] ?? 'Eau de Quince' }}</p>
                <h1 class="name ez-brand">{{ $hero['name'] }}</h1>
                <div class="ez-rule"></div>

                @if($hero['message'])
                    <p class="message">{{ $hero['message'] }}</p>
                @endif

                <p class="ez-launch" style="margin-top: 7mm;">{{ $copy['scent_launch'] ?? 'Lanzamiento' }}</p>
                @include('client.exports.pdf.partials.when')
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
