@php
    $eventos = $itinerario['eventos'] ?? [];
    $totalEventos = count($eventos);
@endphp

<section
    class="invitation-section reveal invitation-itinerary"
    id="itinerario"
    x-data="scrollItinerary({{ $totalEventos }})"
    x-init="init()"
    x-ref="section"
>
    <div class="section-inner-wide">
        <div class="invitation-itinerary__shell">
            @include('invitations.partials.lottie-framed-icon', ['name' => 'itinerar-people'])
            <p class="invitation-itinerary__eyebrow">El recorrido de la noche</p>
            <div class="invitation-itinerary__title-row">
                <h2 class="invitation-itinerary__title">{{ $itinerario['titulo'] ?? 'Itinerario' }}</h2>
            </div>
            <div class="invitation-itinerary__rule" aria-hidden="true"></div>

            @if($totalEventos > 0)
                <div class="invitation-itinerary__track" x-ref="track" :class="{ 'is-mobile': isMobile, 'is-desktop': !isMobile }">
                    <div class="invitation-itinerary__spine" aria-hidden="true">
                        <div class="invitation-itinerary__spine-base"></div>
                        <div class="invitation-itinerary__spine-progress" :style="spineProgressStyle()"></div>
                        <div class="invitation-itinerary__spine-light" :style="lightStyle()"></div>
                    </div>

                    @foreach($eventos as $index => $evento)
                        @php
                            $iconName = $evento['icono'] ?? 'star';
                            $usePeopleLottie = in_array($iconName, ['users', 'people'], true);
                        @endphp
                        <article
                            class="invitation-itinerary__item"
                            data-step="{{ $index }}"
                            :class="itemClass({{ $index }})"
                        >
                            <div class="invitation-itinerary__panel" :class="panelClass({{ $index }})" :style="panelStyle({{ $index }})">
                                <time class="invitation-itinerary__time">{{ $evento['hora'] }}</time>
                                <span class="invitation-itinerary__index" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <h3 class="invitation-itinerary__event-title">{{ $evento['titulo'] }}</h3>
                                @if(!empty($evento['descripcion']))
                                    <p class="invitation-itinerary__description">{{ $evento['descripcion'] }}</p>
                                @endif
                            </div>

                            <div
                                class="invitation-itinerary__node"
                                data-itinerary-node="{{ $index }}"
                                :style="nodeRingStyle({{ $index }})"
                            >
                                <div class="invitation-itinerary__node-halo" :class="nodeHaloClass({{ $index }})" aria-hidden="true"></div>
                                <div class="invitation-itinerary__node-ring" aria-hidden="true"></div>
                                <div class="invitation-itinerary__node-core">
                                    <div class="invitation-itinerary__node-icon" :style="nodeIconStyle({{ $index }})">
                                        @if($usePeopleLottie)
                                            @include('invitations.partials.lottie-icon', ['name' => 'itinerar-people', 'class' => 'invitation-itinerary__lottie'])
                                        @else
                                            @include('invitations.partials.icon', ['name' => $iconName, 'class' => 'w-5 h-5', 'animated' => false])
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="invitation-itinerary__spacer" aria-hidden="true"></div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="invitation-itinerary__empty">Aún no hay momentos en el itinerario.</p>
            @endif
        </div>
    </div>
</section>
