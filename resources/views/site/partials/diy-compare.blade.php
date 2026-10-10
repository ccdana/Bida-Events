{{--
    La comparación de los planes mensuales de «Hazlo tú», punto por punto: lo que la portada no repite.
    Cada fila dice qué trae cada plan (cupo, accesos para clientes, qué plantillas hay en cada familia,
    confirmación y marca) y la última fila deja elegir. Va solo en la página «Hazlo tú» (ancla
    #comparar). Recibe $plans, $featuredPlan y $collections (App\Support\DiyPlans).
--}}
@use('App\Support\Money')

@php
    $limit = fn (array $plan): string => $plan['quota_per_month'] === null ? 'Sin tope' : (string) $plan['quota_per_month'];
    // Las familias de plantillas llevan debajo los nombres de lo que trae cada una
    $familyNote = function (string $key) use ($collections): ?string {
        $names = $collections[$key]['templates'] ?? [];

        if ($key === 'lienzo') {
            return null;
        }

        return $names === [] ? 'Se suman durante el año' : implode(', ', $names);
    };
    $rows = [
        ['label' => 'Invitaciones al mes', 'note' => null, 'value' => $limit],
        ['label' => 'Accesos para clientes al mes', 'note' => 'Uno por evento, para que tu cliente vea sus confirmaciones', 'value' => $limit],
        ['label' => $collections['lienzo']['label'], 'note' => null, 'value' => fn (array $plan) => in_array('lienzo', $plan['collections'] ?? [], true)],
        ['label' => $collections['clasica']['label'], 'note' => $familyNote('clasica'), 'value' => fn (array $plan) => in_array('clasica', $plan['collections'] ?? [], true)],
        ['label' => $collections['temporada']['label'], 'note' => $familyNote('temporada'), 'value' => fn (array $plan) => in_array('temporada', $plan['collections'] ?? [], true)],
        ['label' => $collections['nueva']['label'], 'note' => $familyNote('nueva'), 'value' => fn (array $plan) => in_array('nueva', $plan['collections'] ?? [], true)],
        ['label' => 'Confirmación por WhatsApp', 'note' => 'El invitado confirma y la respuesta te llega por WhatsApp', 'value' => fn (array $plan) => in_array('whatsapp', $plan['rsvp'] ?? [], true)],
        ['label' => 'Confirmación con pase QR y control de entrada', 'note' => 'Pase para cada invitado y escaneo en la puerta', 'value' => fn (array $plan) => in_array('pass', $plan['rsvp'] ?? [], true)],
        ['label' => 'Tu marca al pie', 'note' => 'Tu nombre comercial en lugar del nuestro', 'value' => fn (array $plan) => (bool) ($plan['white_label'] ?? false)],
    ];
@endphp

<section id="comparar" class="site-diy-section scroll-mt-20" aria-labelledby="comparar-titulo">
    <h2 id="comparar-titulo" class="site-display site-display--md" data-reveal>Compara los planes punto por punto</h2>
    <p class="site-muted-lead" data-reveal>Qué trae cada uno, para que elijas el que te alcanza.</p>

    <div class="site-compare site-compare--full" data-reveal>
        <table>
            <caption class="sr-only">Qué incluye cada plan mensual</caption>
            <thead>
                <tr>
                    <th scope="col"><span class="sr-only">Función</span></th>
                    @foreach($plans as $plan)
                        <th scope="col" @class(['is-featured' => $plan['key'] === $featuredPlan])>
                            <span class="block">{{ $plan['name'] }}</span>
                            <span class="site-compare__price">
                                @if($plan['old_price'])
                                    <del><span class="sr-only">Antes </span>{{ Money::format($plan['old_price']) }}</del>
                                @endif
                                {{ Money::format($plan['final_price']) }}<small> al mes</small>
                            </span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <th scope="row">
                            {{ $row['label'] }}
                            @if($row['note'])
                                <span class="site-compare__note">{{ $row['note'] }}</span>
                            @endif
                        </th>
                        @foreach($plans as $plan)
                            @php($value = $row['value']($plan))
                            <td @class(['is-featured' => $plan['key'] === $featuredPlan])>
                                @if($value === true)
                                    <x-phosphor-check-bold class="site-compare__yes" aria-hidden="true" /><span class="sr-only">Incluido</span>
                                @elseif($value === false)
                                    <span class="site-compare__no" aria-hidden="true">—</span><span class="sr-only">No incluido</span>
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th scope="row"><span class="sr-only">Elegir</span></th>
                    @foreach($plans as $plan)
                        <td @class(['is-featured' => $plan['key'] === $featuredPlan])>
                            {{-- Misma regla que las tarjetas: registro con el plan, pago o WhatsApp --}}
                            <a href="{{ $plan['checkout'] ?? $plan['whatsapp'] }}" @unless($plan['checkout']) target="_blank" rel="noopener" @endunless
                                @class(['site-btn justify-center', 'site-btn--ghost' => $plan['key'] !== $featuredPlan])>
                                Elegir<span class="sr-only"> el plan {{ $plan['name'] }}</span>
                            </a>
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</section>
