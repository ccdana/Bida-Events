@php
    $eventos = $itinerario['eventos'] ?? [];
@endphp

<section class="inv-section reveal inv-itinerary" id="itinerario" x-data="scrollItinerary()" x-init="init()">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'lottie' => 'itinerar-people',
            'eyebrow' => 'El recorrido de la noche',
            'title' => $itinerario['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <div class="inv-itinerary__track" x-ref="track">
                <div class="inv-itinerary__spine" aria-hidden="true">
                    <div class="inv-itinerary__spine-fill" data-itinerary-fill></div>
                    <div class="inv-itinerary__light" data-itinerary-light>
                        <span class="inv-itinerary__trail"></span>
                        <span class="inv-itinerary__bloom"></span>
                        <span class="inv-itinerary__core"></span>
                    </div>
                </div>

                <ol class="inv-itinerary__list">
                    @foreach($eventos as $index => $evento)
                        @php($iconName = $evento['icono'] ?? 'star')
                        <li class="inv-itinerary__item {{ $index % 2 === 0 ? 'is-left' : 'is-right' }}" data-itinerary-item>
                            <div class="inv-itinerary__node" data-itinerary-node aria-hidden="true">
                                <span class="inv-itinerary__node-glow"></span>
                                <span class="inv-itinerary__node-dot">
                                    @if(in_array($iconName, ['users', 'people'], true))
                                        @include('invitations.partials.lottie-icon', ['name' => 'itinerar-people', 'class' => 'inv-itinerary__lottie'])
                                    @else
                                        @include('invitations.partials.icon', ['name' => $iconName, 'class' => 'inv-itinerary__icon', 'animated' => false])
                                    @endif
                                </span>
                            </div>

                            <div class="inv-itinerary__body">
                                @if(!empty($evento['hora']))
                                    <time class="inv-itinerary__time">{{ $evento['hora'] }}</time>
                                @endif
                                <h3 class="inv-itinerary__title">{{ $evento['titulo'] }}</h3>
                                @if(!empty($evento['descripcion']))
                                    <p class="inv-itinerary__text">{{ $evento['descripcion'] }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        @else
            <p class="inv-empty">Muy pronto compartiremos el orden de la noche.</p>
        @endif
    </div>
</section>
