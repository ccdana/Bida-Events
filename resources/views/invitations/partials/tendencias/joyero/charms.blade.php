{{--
    Itinerario de «Joyero musical»: los dijes de la noche. Una cadena baja por la izquierda y de ella
    cuelga un dije por momento (un medallón de terciopelo con su ícono y el borde de oro); al lado, la
    hora, lo que pasa y su descripción. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal jo-charms-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Los dijes de mi noche',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="jo-charms">
                @foreach($eventos as $index => $evento)
                    <li class="jo-charm" data-step style="--step: {{ $index }}">
                        <span class="jo-charm__pendant" aria-hidden="true">
                            @include('invitations.partials.itinerary-icon', ['name' => $evento['icono'] ?? null, 'class' => 'jo-charm__icon'])
                        </span>
                        <div class="jo-charm__body">
                            <time class="jo-charm__time">{{ $evento['hora'] ?? '' }}</time>
                            <h3 class="jo-charm__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="jo-charm__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto sumaremos cada dije de la noche.' }}</p>
        @endif
    </div>
</section>
