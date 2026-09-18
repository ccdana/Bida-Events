{{--
    «Juntos desde» en la tarjeta de amor: una margarita para deshojar. Cada pétalo que se toca cae
    girando y dice «me quiere» o «no me quiere»; son nueve, así que el último siempre dice «¡me quiere!».
    Entonces el centro late y aparece el tiempo juntos (años, meses, días), las primaveras compartidas
    y los latidos aproximados. Reemplaza a modules/juntos-desde solo en esta plantilla
    (InvitationTemplates, «partials»); daisyGate en resources/js/cards/amor/daisy.js.
    Todo se calcula en el servidor, así se lee aunque no haya JavaScript. Recibe $data (titulo, fecha).
--}}
@php
    $since = null;

    try {
        $since = ! empty($data['fecha']) ? \Illuminate\Support\Carbon::parse($data['fecha'])->startOfDay() : null;
    } catch (\Throwable) {
        $since = null;
    }

    $today = now();
    $elapsed = $since && $since->isPast() ? $since->diff($today) : null;
    $units = $elapsed ? array_filter([
        ['value' => $elapsed->y, 'label' => $elapsed->y === 1 ? 'año' : 'años'],
        ['value' => $elapsed->m, 'label' => $elapsed->m === 1 ? 'mes' : 'meses'],
        ['value' => $elapsed->d, 'label' => $elapsed->d === 1 ? 'día' : 'días'],
    ], fn (array $unit, int $index) => $unit['value'] > 0 || $index === 2, ARRAY_FILTER_USE_BOTH) : [];
    $totalDays = $since ? (int) $since->diffInDays($today) : 0;

    // Primaveras juntos: cuántos 21 de septiembre pasaron desde esa fecha
    $springs = 0;
    if ($since) {
        for ($year = $since->year; $year <= $today->year; $year++) {
            $spring = \Illuminate\Support\Carbon::create($year, 9, 21)->startOfDay();
            $springs += $spring->gt($since) && $spring->lte($today) ? 1 : 0;
        }
    }

    // Latidos aproximados: 72 por minuto, en millones
    $heartbeats = max(1, (int) round($totalDays * 72 * 60 * 24 / 1_000_000));

    $petals = 9;
    $yes = $invCopy['daisy_yes'] ?? 'Me quiere';
    $no = $invCopy['daisy_no'] ?? 'No me quiere';
    $answer = $invCopy['daisy_answer'] ?? '¡Me quiere!';
@endphp

@if($since && $elapsed)
    <section class="inv-section reveal inv-milestone inv-daisy-scene" id="juntos-desde">
        <div class="inv-wrap inv-wrap--wide">
            @include('invitations.partials.section-header', [
                'compact' => true,
                'eyebrow' => 'Desde el '.$since->locale('es')->translatedFormat('j \d\e F \d\e Y'),
                'title' => $invCopy['daisy_title'] ?? '¿Me quiere?',
            ])

            <div class="inv-daisy"
                x-data="daisyGate({ petals: {{ $petals }}, yes: @js($yes), no: @js($no), answer: @js($answer) })"
                data-story-gate
                :data-gate-done="done"
                :class="{ 'is-plucking': count > 0, 'is-answered': count >= total }">
                <div class="inv-daisy__stage" data-needs-js>
                    <p class="inv-daisy__word" aria-live="polite"
                        :class="{ 'is-on': count > 0, 'is-a': count % 2 === 1, 'is-b': count > 0 && count % 2 === 0, 'is-last': count >= total }"
                        x-text="word"></p>

                    <div class="inv-daisy__head" data-story-ignore>
                        @for($petal = 0; $petal < $petals; $petal++)
                            <button type="button" class="inv-daisy__petal"
                                style="--a: {{ round(360 / $petals * $petal, 2) }}deg; --i: {{ $petal }}; --fx: {{ ($petal % 2 ? 1 : -1) * (8 + $petal * 3) }}vw"
                                :class="{ 'is-plucked': isPlucked({{ $petal }}) }"
                                :disabled="isPlucked({{ $petal }})"
                                @click="pluck({{ $petal }})"
                                aria-label="Deshojar un pétalo"></button>
                        @endfor
                        <span class="inv-daisy__center" x-ref="center" aria-hidden="true">
                            <svg class="inv-daisy__heart" viewBox="0 0 24 24"><path d="M12 21s-7.5-4.6-9.6-9.3C.9 8.3 3 4.5 6.7 4.5c2.1 0 3.6 1.2 5.3 3.1 1.7-1.9 3.2-3.1 5.3-3.1 3.7 0 5.8 3.8 4.3 7.2C19.5 16.4 12 21 12 21z"/></svg>
                        </span>
                        <span class="inv-daisy__stem" aria-hidden="true"></span>
                    </div>

                    <p class="inv-daisy__hint" x-show="!done">{{ $invCopy['daisy_hint'] ?? 'Deshoja la margarita, pétalo por pétalo' }}</p>
                    <button type="button" class="inv-daisy__skip" x-show="count === 0" @click="pluckAll()">Ver la respuesta sin deshojar</button>
                </div>

                <div class="inv-daisy__result">
                    <p class="inv-daisy__answer" data-needs-js>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.3C.9 8.3 3 4.5 6.7 4.5c2.1 0 3.6 1.2 5.3 3.1 1.7-1.9 3.2-3.1 5.3-3.1 3.7 0 5.8 3.8 4.3 7.2C19.5 16.4 12 21 12 21z"/></svg>
                        {{ $answer }}
                    </p>
                    <p class="inv-daisy__result-title">{{ ($data['titulo'] ?? null) ?: 'Llevamos juntos' }}</p>

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

                    <ul class="inv-daisy__facts">
                        @if($springs > 0)
                            <li class="inv-daisy__fact">
                                <svg viewBox="0 0 40 40" aria-hidden="true"><use href="#amor-flower" /></svg>
                                <span><strong data-count-to="{{ $springs }}">{{ $springs }}</strong> {{ $springs === 1 ? 'primavera' : 'primaveras' }} juntos</span>
                            </li>
                        @endif
                        <li class="inv-daisy__fact inv-daisy__fact--beat">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.3C.9 8.3 3 4.5 6.7 4.5c2.1 0 3.6 1.2 5.3 3.1 1.7-1.9 3.2-3.1 5.3-3.1 3.7 0 5.8 3.8 4.3 7.2C19.5 16.4 12 21 12 21z"/></svg>
                            <span>unos <strong data-count-to="{{ $heartbeats }}">{{ $heartbeats }}</strong> {{ $heartbeats === 1 ? 'millón' : 'millones' }} de latidos</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endif
