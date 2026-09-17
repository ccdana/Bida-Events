{{--
    «Juntos desde» (App\Modules\Card\MilestoneModule): cuánto tiempo pasó desde una fecha.
    Se calcula en el servidor, así se lee aunque no haya JavaScript. Recibe $data (titulo, fecha) y $page.
--}}
@php
    $since = null;

    try {
        $since = ! empty($data['fecha']) ? \Illuminate\Support\Carbon::parse($data['fecha'])->startOfDay() : null;
    } catch (\Throwable) {
        $since = null;
    }

    $elapsed = $since && $since->isPast() ? $since->diff(now()) : null;
    $units = $elapsed ? array_filter([
        ['value' => $elapsed->y, 'label' => $elapsed->y === 1 ? 'año' : 'años'],
        ['value' => $elapsed->m, 'label' => $elapsed->m === 1 ? 'mes' : 'meses'],
        ['value' => $elapsed->d, 'label' => $elapsed->d === 1 ? 'día' : 'días'],
    ], fn (array $unit, int $index) => $unit['value'] > 0 || $index === 2, ARRAY_FILTER_USE_BOTH) : [];
@endphp

@if($since && $elapsed)
    <section class="inv-section reveal inv-milestone" id="juntos-desde">
        <div class="inv-wrap inv-wrap--wide">
            @include('invitations.partials.section-header', [
                'compact' => true,
                'eyebrow' => 'Desde el '.$since->locale('es')->translatedFormat('j \d\e F \d\e Y'),
                'title' => ($data['titulo'] ?? null) ?: 'Llevamos juntos',
            ])

            <div class="inv-milestone__grid">
                @foreach($units as $unit)
                    <div class="inv-milestone__unit">
                        <span class="inv-milestone__value">{{ $unit['value'] }}</span>
                        <span class="inv-milestone__label">{{ $unit['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <p class="inv-milestone__total">
                {{ number_format($since->diffInDays(now()), 0, ',', '.') }} días compartidos
            </p>
        </div>
    </section>
@endif
