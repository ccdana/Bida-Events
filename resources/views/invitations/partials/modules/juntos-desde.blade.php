{{--
    «Juntos desde» (App\Modules\Card\MilestoneModule): cuánto tiempo pasó desde una fecha.
    Se calcula en el servidor, así se lee aunque no haya JavaScript. Recibe $data (titulo, fecha) y $page.

    Con JavaScript el resultado llega tapado por una lámina que se raspa con el dedo
    (scratchReveal en resources/js/story); al descubrirlo, los números cuentan desde cero.
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
    $totalDays = $since ? (int) $since->diffInDays(now()) : 0;
@endphp

@if($since && $elapsed)
    <section class="inv-section reveal inv-milestone" id="juntos-desde">
        <div class="inv-wrap inv-wrap--wide">
            @include('invitations.partials.section-header', [
                'compact' => true,
                'eyebrow' => 'Desde el '.$since->locale('es')->translatedFormat('j \d\e F \d\e Y'),
                'title' => ($data['titulo'] ?? null) ?: 'Llevamos juntos',
            ])

            <div class="inv-scratch"
                x-data="scratchReveal({ threshold: 0.45 })"
                data-story-gate
                data-scratch-label="Raspa para descubrirlo"
                :data-gate-done="revealed"
                :class="{ 'is-scratching': scratching }">
                <div class="inv-scratch__surface">
                    <div class="inv-milestone__grid">
                        @foreach($units as $unit)
                            <div class="inv-milestone__unit">
                                <span class="inv-milestone__value" data-count-to="{{ $unit['value'] }}">{{ $unit['value'] }}</span>
                                <span class="inv-milestone__label">{{ $unit['label'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <p class="inv-milestone__total">
                        <span data-count-to="{{ $totalDays }}" data-count-format="thousands">{{ number_format($totalDays, 0, ',', '.') }}</span> días compartidos
                    </p>

                    <canvas class="inv-scratch__canvas" x-ref="canvas" data-needs-js aria-hidden="true"
                        x-show="!revealed" x-transition:leave="inv-scratch-leave" x-transition:leave-end="inv-scratch-gone"></canvas>
                </div>

                <button type="button" class="inv-scratch__skip" data-needs-js x-show="!revealed" @click="reveal()">
                    Mostrar sin raspar
                </button>
            </div>
        </div>
    </section>
@endif
