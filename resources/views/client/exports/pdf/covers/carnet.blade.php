{{--
    «Carta de baile»: la hoja se imprime como la carta. Marco doble, el título, el nombre, el renglón
    del lugar reservado y la fecha, la hora y el salón con puntos guía.
--}}
<style>
    .carnet-title { margin: 0 0 5mm; font-size: 9pt; letter-spacing: 1pt; color: {{ $colors['accent'] }}; }
    .carnet-reserved { width: 90mm; margin: 7mm auto 0; padding-bottom: 2mm; border-bottom: 0.8pt dotted {{ $colors['line'] }}; }
    .carnet-facts { width: 110mm; margin: 7mm auto 0; border-collapse: collapse; table-layout: fixed; }
    .carnet-facts td { padding: 1.6mm 0; vertical-align: bottom; }
    .carnet-facts .dots { border-bottom: 1pt dotted {{ $colors['line'] }}; }
    .carnet-facts .fact-label { width: 22mm; font-size: 9pt; text-align: left; color: {{ $colors['muted'] }}; }
    .carnet-facts .fact-value { width: 56mm; padding-left: 2mm; text-align: right; font-weight: bold; }
</style>

<div class="sheet is-double">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <p class="carnet-title">{{ $copy['card_title'] ?? 'Carta de baile' }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>
                <p class="kicker" style="margin-top: 3mm;">{{ $hero['subtitle'] }}</p>

                <div class="carnet-reserved">
                    <p class="label">{{ $copy['card_reserved_any'] ?? 'Un lugar reservado para ti' }}</p>
                </div>

                <table class="carnet-facts">
                    @foreach(array_filter([
                        $copy['hero_day_label'] ?? 'Fecha' => $hero['date'],
                        $copy['hero_time_label'] ?? 'Hora' => $hero['time'],
                        $copy['hero_place_label'] ?? 'Salón' => $location['name'] ?? $location['address'] ?? null,
                    ]) as $label => $value)
                        <tr>
                            <td class="fact-label">{{ $label }}</td>
                            <td class="dots"></td>
                            <td class="fact-value">{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>

                @if($hero['message'])
                    <p class="message" style="margin-top: 7mm;">{{ $hero['message'] }}</p>
                @endif
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
