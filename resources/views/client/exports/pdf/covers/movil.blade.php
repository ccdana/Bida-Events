{{--
    «Móvil de cuna»: la varilla del móvil con sus figuras de fieltro colgando de hilos de distinto
    largo y, debajo, el banderín cosido con el nombre bordado.
--}}
@php
    $mobileThreads = [14, 26, 8, 22, 16];
@endphp
<style>
    .mv-bar { width: 120mm; height: 1.8mm; margin: 0 auto; border-radius: 1mm; background: {{ $colors['line'] }}; }
    .mv-hangs { width: 120mm; margin: 0 auto; border-collapse: collapse; }
    .mv-hangs td { width: 20%; padding: 0; text-align: center; vertical-align: top; }
    .mv-thread { width: 0.4mm; margin: 0 auto; background: {{ $colors['muted'] }}; }
    .mv-felt { width: 11mm; height: 11mm; margin: 0 auto; border-radius: 6mm; background: {{ $colors['primary'] }}; border: 0.8pt dashed {{ $colors['paper'] }}; }
    .mv-felt.is-soft { background: {{ $colors['line'] }}; }
    .mv-banner { width: 130mm; margin: 10mm auto 0; padding: 8mm 6mm; border-radius: 4mm; background: {{ $colors['tint'] }}; border: 1pt dashed {{ $colors['line'] }}; }
    .mv-banner .name { margin: 2mm 0 0; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <div class="mv-bar"></div>
                <table class="mv-hangs">
                    <tr>
                        @foreach($mobileThreads as $index => $length)
                            <td>
                                <div class="mv-thread" style="height: {{ $length }}mm;"></div>
                                <div @class(['mv-felt', 'is-soft' => $index % 2 === 1])></div>
                            </td>
                        @endforeach
                    </tr>
                </table>

                <div class="mv-banner">
                    <p class="kicker">{{ $hero['subtitle'] }}</p>
                    <h1 class="name">{{ $hero['name'] }}</h1>
                </div>

                @if($hero['message'])
                    <p class="message" style="margin-top: 7mm;">{{ $hero['message'] }}</p>
                @endif

                @include('client.exports.pdf.partials.when')
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
