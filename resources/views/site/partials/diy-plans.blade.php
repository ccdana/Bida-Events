{{--
    Los planes mensuales de «Únete» (el modelo principal), con la misma interfaz que los paquetes de
    «Te la hacemos» (components/site/price-card): una columna por plan, de tres en tres, y la última
    celda para quien necesita algo distinto. Lo usan la portada y la página «Únete».
    Recibe $plans, $featuredPlan y $contactUrl y, opcionales, $diyTitle, $diyLead, $diyDetail (true:
    suma a cada plan lo que trae: plantillas, confirmación y marca) y $diyMoreLink (true: enlace al
    detalle, para la portada).
--}}
@use('App\Support\Money')

@php
    $quotaLabel = fn (array $plan): string => $plan['quota_per_month'] === null ? 'Invitaciones sin tope' : ($plan['quota_per_month'] === 1 ? '1 invitación al mes' : $plan['quota_per_month'].' invitaciones al mes');
    $detailed = (bool) ($diyDetail ?? false);

    // Lo que dice cada plan: el cupo de hoy (se quita el de la lista para que no quede un número viejo)
    // y, en el detalle, lo que desbloquea que la lista no nombra. Con el pago en línea, «Elegir» pasa
    // por crear la cuenta y pagar; si no, va a WhatsApp.
    $featuresFor = function (array $plan) use ($quotaLabel, $detailed): array {
        $features = collect($plan['features'] ?? [])
            ->reject(fn (string $feature) => preg_match('/^(\d+ invitaci(ón|ones) al mes|Invitaciones sin tope)/iu', $feature))
            ->values()
            ->all();
        $list = [$quotaLabel($plan), ...$features];

        if ($detailed) {
            $mentions = fn (string $word): bool => collect($list)->contains(fn (string $line) => mb_stripos($line, $word) !== false);

            $list[] = $plan['templates_count'].' plantillas para elegir';

            if (in_array('whatsapp', $plan['rsvp'] ?? [], true) && ! $mentions('WhatsApp')) {
                $list[] = 'Confirmación de asistencia por WhatsApp';
            }
            if (in_array('pass', $plan['rsvp'] ?? [], true) && ! $mentions('QR')) {
                $list[] = 'Pase QR y control de entrada';
            }
            if (($plan['white_label'] ?? false) && ! $mentions('marca')) {
                $list[] = 'Tu marca al pie de cada invitación';
            }
        }

        return $list;
    };
@endphp

<section id="planes" class="scroll-mt-20 border-t border-site-line bg-site-surface" aria-labelledby="planes-titulo">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
        <div class="max-w-3xl">
            <h2 id="planes-titulo" class="site-display site-display--md" data-reveal>{{ $diyTitle ?? 'Elige tu plan' }}</h2>
            <p class="site-muted-lead" data-reveal>
                {{ $diyLead ?? 'Precios por mes, en dólares ('.Money::code().'). Cada plan suma al anterior.' }}
            </p>
        </div>

        <div class="site-plans site-plans--grid mt-14">
            @foreach($plans as $index => $plan)
                @php($featured = $plan['key'] === $featuredPlan)
                {{-- El de más arriba (sin tope de invitaciones) lleva el estilo premium, como el paquete Premium --}}
                @php($premium = $plan['quota_per_month'] === null)
                <x-site.price-card
                    :name="$plan['name']"
                    :price="$plan['final_price']"
                    :old="$plan['old_price'] ? Money::format($plan['old_price']) : null"
                    :suffix="Money::code().' al mes'"
                    :saving="$plan['old_price'] ? 'Ahorras '.Money::format($plan['old_price'] - $plan['final_price']).' al mes' : null"
                    :summary="$plan['summary'] ?? ''"
                    :features="$featuresFor($plan)"
                    :featured="$featured"
                    :premium="$premium"
                    :tag="$featured ? 'Recomendado' : ($premium ? 'Sin tope' : null)"
                    :url="$plan['checkout'] ?? $plan['whatsapp']"
                    :external="! $plan['checkout']"
                    :cta="'Elegir '.$plan['name']"
                    :index="$index"
                    :reserve="true" />
            @endforeach

            {{-- La celda que sobra para completar la fila: quien necesita algo distinto --}}
            <div class="site-plan site-plan--note" data-reveal style="--reveal-index: {{ count($plans) }}">
                <p class="site-plan__name">¿Necesitas algo distinto?</p>
                <p class="mt-4 max-w-[36ch] leading-relaxed text-site-muted">Cuéntanos qué organizas y vemos el plan que te conviene.</p>
                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-link-arrow mt-6">
                    Escríbenos
                    <x-phosphor-arrow-right aria-hidden="true" />
                </a>
            </div>
        </div>

        {{-- La portada no repite el detalle: manda a la página «Únete», donde cada plan trae más --}}
        @if($diyMoreLink ?? false)
            <p class="mt-10" data-reveal>
                <a href="{{ route('diy') }}#planes" class="site-link-arrow">
                    Ver el detalle de cada plan
                    <x-phosphor-arrow-right aria-hidden="true" />
                </a>
            </p>
        @endif
    </div>
</section>
