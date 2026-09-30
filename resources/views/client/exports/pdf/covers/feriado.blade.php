{{--
    «Día feriado»: la hoja del almanaque del cumpleaños. La franja del mes; el número grande en el color
    del feriado con el día de la semana y, a su lado, el mes entero con su día marcado; el nombre y el
    pensamiento del día. Debajo, el día, la hora y el lugar.
--}}
@php
    $calDate = $invitation->event_date?->copy()->locale('es');
    $calMonth = $calDate ? \Illuminate\Support\Str::upper($calDate->translatedFormat('F')) : null;
    $calCells = [];
    if ($calDate) {
        $calFirst = $calDate->copy()->startOfMonth();
        $calCells = array_merge(array_fill(0, $calFirst->dayOfWeekIso - 1, null), range(1, $calDate->daysInMonth));
        $calCells = array_merge($calCells, array_fill(0, (7 - count($calCells) % 7) % 7, null));
    }
@endphp
<style>
    .fd-leaf { width: 118mm; margin: 4mm auto 0; border-collapse: collapse; border: 0.8pt solid {{ $colors['line'] }}; }
    .fd-band { padding: 2.2mm 0; background: {{ $colors['accent'] }}; color: {{ $colors['paper'] }}; font-family: {!! $titleFont !!}; font-size: 12pt; letter-spacing: 4pt; text-align: center; }
    .fd-number { margin: 0; font-family: {!! $titleFont !!}; font-size: 62pt; line-height: 1; color: {{ $colors['accent'] }}; }
    .fd-weekday { margin: 1mm 0 0; font-family: {!! $titleFont !!}; font-size: 12pt; letter-spacing: 4pt; text-transform: uppercase; color: {{ $colors['ink'] }}; }
    .fd-line { margin: 3mm 10mm 0; padding-top: 2.5mm; border-top: 0.6pt dashed {{ $colors['line'] }}; font-size: 7.5pt; letter-spacing: 2pt; text-transform: uppercase; color: {{ $colors['muted'] }}; }
    .fd-holiday { padding: 0.5mm 1.5mm; border: 0.8pt solid {{ $colors['accent'] }}; color: {{ $colors['accent'] }}; font-weight: bold; }
    .fd-name { margin: 2mm 6mm 0; font-family: {!! $titleFont !!}; font-weight: normal; font-size: {{ $hero['nameSize'] }}; line-height: 1.05; text-transform: uppercase; color: {{ $colors['ink'] }}; }
    .fd-thought { margin: 3mm 8mm 4mm; padding: 2.5mm 4mm; background: {{ $colors['tint'] }}; }
    .fd-thought .label { margin-bottom: 1mm; }
    .fd-thought .message { width: auto; font-size: 10.5pt; font-style: italic; }
    .fd-day { width: 50%; padding: 4mm 2mm 0; text-align: center; vertical-align: middle; }
    .fd-month { width: 52mm; margin: 0 auto; border-collapse: collapse; }
    .fd-month th { padding: 0.6mm 0; font-size: 7pt; color: {{ $colors['muted'] }}; }
    .fd-month td { width: 7.4mm; height: 4.6mm; font-size: 7.5pt; text-align: center; color: {{ $colors['body'] }}; }
    .fd-month td.sunday, .fd-month th.sunday { color: {{ $colors['accent'] }}; }
    .fd-month td.marked { background: {{ $colors['accent'] }}; color: {{ $colors['paper'] }}; font-weight: bold; }
</style>

<div class="sheet is-thin">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <p class="kicker">{{ $hero['subtitle'] }}</p>

                <table class="fd-leaf">
                    @if($calDate)
                        <tr><td class="fd-band" colspan="2">{{ $calMonth }} · {{ $calDate->format('Y') }}</td></tr>
                        {{-- El número del día y, a su lado, el mes entero con el día marcado --}}
                        <tr>
                            <td class="fd-day">
                                <p class="fd-number">{{ $calDate->format('j') }}</p>
                                <p class="fd-weekday">{{ $calDate->translatedFormat('l') }}</p>
                            </td>
                            <td class="fd-day">
                                <table class="fd-month">
                                    <tr>
                                        @foreach(['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $index => $initial)
                                            <th @class(['sunday' => $index === 6])>{{ $initial }}</th>
                                        @endforeach
                                    </tr>
                                    @foreach(array_chunk($calCells, 7) as $week)
                                        <tr>
                                            @foreach($week as $index => $day)
                                                <td @class(['sunday' => $index === 6 && $day, 'marked' => $day === (int) $calDate->format('j')])>{{ $day }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td class="center" colspan="2">
                            <p class="fd-line">
                                <span class="fd-holiday">{{ $copy['cal_holiday'] ?? 'Feriado' }}</span>
                                {{ $copy['cal_line'] ?? 'Cumpleaños de' }}
                            </p>
                            <h1 class="fd-name">{{ $hero['name'] }}</h1>

                            @if($hero['message'])
                                <div class="fd-thought">
                                    <p class="label">{{ $copy['cal_thought'] ?? 'Pensamiento del día' }}</p>
                                    <p class="message">{{ $hero['message'] }}</p>
                                </div>
                            @else
                                <div style="height: 5mm;"></div>
                            @endif
                        </td>
                    </tr>
                </table>

                @include('client.exports.pdf.partials.when')
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
