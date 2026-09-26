{{--
    «La gota»: tres ondas concéntricas con las iniciales al centro, el nombre debajo y los datos
    separados por ondas cortas. Sin símbolos de un credo, como la plantilla.
--}}
@php
    $ondasInitials = collect(preg_split('/\s+/u', trim($hero['name'])) ?: [])->take(2)->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))->implode('');
    $ondasDetails = array_values(array_filter([$hero['date'], $hero['time'], $location['name'] ?? $location['address'] ?? null]));
@endphp
<style>
    .ondas-ring { margin: 0 auto; border-radius: 50%; text-align: center; }
    .ondas-ring.is-1 { width: 58mm; height: 58mm; border: 0.6pt solid {{ $colors['line'] }}; }
    .ondas-ring.is-2 { width: 46mm; height: 46mm; margin-top: 5.4mm; border: 0.8pt solid {{ $colors['line'] }}; }
    .ondas-ring.is-3 { width: 34mm; height: 34mm; margin-top: 5.2mm; border: 1.2pt solid {{ $colors['ink'] }}; }
    .ondas-ring.is-3 span { display: block; padding-top: 10.5mm; font-size: 17pt; color: {{ $colors['accent'] }}; }
    .ondas-wave { width: 14mm; margin: 2.5mm auto; border-top: 1pt dashed {{ $colors['line'] }}; }
    .ondas-detail { margin: 0; font-size: 11pt; font-weight: bold; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <div class="ondas-ring is-1"><div class="ondas-ring is-2"><div class="ondas-ring is-3"><span>{{ $ondasInitials }}</span></div></div></div>

                <p class="kicker" style="margin-top: 7mm;">{{ $hero['subtitle'] }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>

                <div style="height: 4mm;"></div>
                @foreach($ondasDetails as $index => $detail)
                    @if($index > 0)
                        <div class="ondas-wave"></div>
                    @endif
                    <p class="ondas-detail">{{ $detail }}</p>
                @endforeach

                @if($hero['message'])
                    <p class="message" style="margin-top: 6mm;">{{ $hero['message'] }}</p>
                @endif
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
