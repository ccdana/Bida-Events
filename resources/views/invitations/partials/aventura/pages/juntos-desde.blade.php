{{--
    «Mes de aniversario»: la hoja del calendario del mes en que empezó todo, con el día rodeado por
    una flor amarilla, y debajo cuánto llevan juntos y cuánto falta para el próximo mes cumplido.
    Todo se calcula en el servidor; con JavaScript los números cuentan desde cero al llegar a la hoja.
    Recibe $data (titulo, fecha).
--}}
@php
    $since = null;

    try {
        $since = ! empty($data['fecha']) ? \Illuminate\Support\Carbon::parse($data['fecha'])->startOfDay() : null;
    } catch (\Throwable) {
        $since = null;
    }

    $today = now()->startOfDay();
@endphp

@if($since && $since->lte($today))
    @php
        $elapsed = $since->diff($today);
        $monthsTogether = $elapsed->y * 12 + $elapsed->m;
        $totalDays = (int) $since->diffInDays($today);
        $monthStart = $since->copy()->startOfMonth();
        $leadingBlanks = $monthStart->dayOfWeekIso - 1;
        $weekdays = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
        $weekdayNames = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];

        // Próximo mes cumplido: el mismo día del mes (o el último, si el mes es más corto)
        $nextMonthly = null;

        for ($offset = 0; $offset <= 1; $offset++) {
            $candidateMonth = $today->copy()->startOfMonth()->addMonthsNoOverflow($offset);
            $candidate = $candidateMonth->copy()->day(min($since->day, $candidateMonth->daysInMonth));

            if ($candidate->gte($today)) {
                $nextMonthly = $candidate;
                break;
            }
        }

        $daysToNext = $nextMonthly ? (int) $today->diffInDays($nextMonthly) : null;
        $nextCount = $monthsTogether + ($daysToNext === 0 ? 0 : 1);
        $units = array_values(array_filter([
            ['value' => $elapsed->y, 'label' => $elapsed->y === 1 ? 'año' : 'años'],
            ['value' => $elapsed->m, 'label' => $elapsed->m === 1 ? 'mes' : 'meses'],
            ['value' => $elapsed->d, 'label' => $elapsed->d === 1 ? 'día' : 'días'],
        ], fn (array $unit, int $index) => $unit['value'] > 0 || $index === 2, ARRAY_FILTER_USE_BOTH));
    @endphp

    <article class="nb-page nb-page--grid nb-anniversary" data-nb-page id="juntos-desde" data-nb-count>
        <div class="nb-page__inner">
            <p class="nb-eyebrow">Nuestro mes de aniversario</p>

            <div class="nb-calendar">
                <span class="nb-tape nb-tape--a" aria-hidden="true"></span>
                <p class="nb-calendar__month">
                    {{ \Illuminate\Support\Str::ucfirst($since->locale('es')->translatedFormat('F')) }}
                    <span>{{ $since->year }}</span>
                </p>
                <table class="nb-calendar__grid">
                    <caption class="sr-only">{{ \Illuminate\Support\Str::ucfirst($since->locale('es')->translatedFormat('F \d\e Y')) }}</caption>
                    <thead>
                        <tr>
                            @foreach($weekdays as $index => $weekday)
                                <th scope="col"><abbr title="{{ $weekdayNames[$index] }}">{{ $weekday }}</abbr></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_chunk(array_merge(array_fill(0, $leadingBlanks, null), range(1, $since->daysInMonth)), 7) as $week)
                            <tr>
                                @foreach(array_pad($week, 7, null) as $day)
                                    @if($day === null)
                                        <td></td>
                                    @elseif($day === $since->day)
                                        <td class="is-marked" aria-current="date">
                                            <span class="nb-calendar__day">
                                                @include('invitations.partials.aventura.flower', ['kind' => 'girasol', 'class' => 'nb-calendar__flower'])
                                                <span>{{ $day }}</span>
                                            </span>
                                        </td>
                                    @else
                                        <td>{{ $day }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="nb-hand nb-anniversary__since">
                El {{ $since->locale('es')->translatedFormat('j \d\e F \d\e Y') }} empezó nuestra aventura
            </p>

            <p class="nb-anniversary__label">{{ ($data['titulo'] ?? null) ?: 'Llevamos juntos' }}</p>
            <div class="nb-anniversary__units">
                @foreach($units as $unit)
                    <p class="nb-anniversary__unit">
                        <span class="nb-anniversary__value" data-count-to="{{ $unit['value'] }}">{{ $unit['value'] }}</span>
                        <span>{{ $unit['label'] }}</span>
                    </p>
                @endforeach
            </div>

            <p class="nb-anniversary__note">
                <span data-count-to="{{ $totalDays }}" data-count-format="thousands">{{ number_format($totalDays, 0, ',', '.') }}</span> días de aventuras
                @if($daysToNext === 0 && $monthsTogether > 0)
                    · ¡hoy cumplimos {{ $monthsTogether }} {{ $monthsTogether === 1 ? 'mes' : 'meses' }}!
                @elseif($daysToNext !== null && $nextCount > 0)
                    · faltan {{ $daysToNext }} {{ $daysToNext === 1 ? 'día' : 'días' }} para los {{ $nextCount }} {{ $nextCount === 1 ? 'mes' : 'meses' }}
                @endif
            </p>
        </div>
        <span class="nb-folio"><!--nb-folio--></span>
    </article>
@endif
