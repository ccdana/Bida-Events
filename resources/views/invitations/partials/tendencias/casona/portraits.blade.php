{{--
    Anfitriones de «Casa de muñecas de medianoche»: la galería de retratos del pasillo. Cada uno cuelga
    de su clavo con el alambre: un marco de arco con su inicial sobre terciopelo, la placa de latón con
    su papel, sus nombres y su mensaje. Los demás grupos (jurado, DJ…) firman en el libro de visitas,
    en dos columnas. Al tocar un retrato, se mece. Recibe $data (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $guests = array_filter([
        ($invCopy['court_women'] ?? 'Invitados de honor') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_men'] ?? 'También en casa') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
    $initialOf = fn (string $names) => mb_strtoupper(mb_substr(trim($names), 0, 1));
@endphp

<section class="inv-section reveal cs-portraits-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'La galería de retratos',
            'title' => $invCopy['court_title'] ?? 'Los dueños de casa',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($guests))
            @if(count($padrinos))
                <ul class="cs-portraits">
                    @foreach($padrinos as $index => $padrino)
                        <li class="cs-portrait" data-step data-poke="swing" style="--step: {{ $index }}">
                            <span class="cs-portrait__wire" aria-hidden="true"></span>
                            <span class="cs-portrait__frame" aria-hidden="true">
                                <span class="cs-portrait__velvet">{{ $initialOf($padrino['nombres']) }}</span>
                            </span>
                            @if(!empty($padrino['rol']))
                                <span class="cs-portrait__plate">{{ $padrino['rol'] }}</span>
                            @endif
                            <h3 class="cs-portrait__names">{{ $padrino['nombres'] }}</h3>
                            @if(!empty($padrino['mensaje']))
                                <p class="cs-portrait__note">{{ $padrino['mensaje'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($guests))
                <div class="cs-guestbook">
                    @foreach($guests as $label => $people)
                        <div class="cs-guestbook__page">
                            <h3 class="cs-guestbook__title">{{ $label }}</h3>
                            <ul class="cs-guestbook__list">
                                @foreach($people as $person)
                                    <li style="--i: {{ $loop->index }}">
                                        <span class="cs-guestbook__name">{{ $person['nombre'] }}</span>
                                        @if(!empty($person['detalle']))
                                            <small>{{ $person['detalle'] }}</small>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Muy pronto presentaremos a los dueños de casa.' }}</p>
        @endif
    </div>
</section>
