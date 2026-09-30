{{--
    Gente favorita de «Cabina de fotos»: los que salen en la foto. Cada anfitrión tiene su cuadro grande
    con sus iniciales escritas con plumón, como el cartelito que se sostiene en la cabina, y debajo su
    papel, sus nombres y su mensaje; cada grupo (familia, amigos) es una hoja de contactos con un
    cuadrito por persona. Recibe $data (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $court = array_filter([
        ($invCopy['court_women'] ?? 'Familia') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_men'] ?? 'Amigos') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
    // Las iniciales del cartelito: las dos primeras palabras que no sean tratamientos ni conectores
    $initialsOf = function (string $names): string {
        $words = array_values(array_filter(preg_split('/[\s,]+/u', trim($names)) ?: [], fn ($word) => ! preg_match('/^(sr|sra|srta|dr|dra|don|doña|lic|ing|y|e|&|de|del|la|las|los|mis|mi)\.?$/iu', $word)));

        return mb_strtoupper(implode('', array_map(fn ($word) => mb_substr($word, 0, 1), array_slice($words, 0, 2))));
    };
@endphp

<section class="inv-section reveal cb-cast-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Salen en la foto',
            'title' => $invCopy['court_title'] ?? 'Mi gente favorita',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            @if(count($padrinos))
                <ul class="cb-hosts">
                    @foreach($padrinos as $index => $padrino)
                        <li class="cb-host" data-step style="--step: {{ $index }}">
                            <span class="cb-host__frame" aria-hidden="true"><b>{{ $initialsOf($padrino['nombres']) }}</b></span>
                            @if(!empty($padrino['rol']))
                                <span class="cb-host__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="cb-host__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <span class="cb-host__message">{{ $padrino['mensaje'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @foreach($court as $label => $people)
                <div class="cb-sheet">
                    <h3 class="cb-sheet__title">{{ $label }}</h3>
                    <ul class="cb-sheet__grid">
                        @foreach($people as $person)
                            <li class="cb-contact" data-step style="--step: {{ $loop->index }}">
                                <span class="cb-contact__frame" aria-hidden="true"><b>{{ $initialsOf($person['nombre']) }}</b></span>
                                <span class="cb-contact__name">{{ $person['nombre'] }}</span>
                                @if(!empty($person['detalle']))
                                    <small>{{ $person['detalle'] }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @else
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaré a mi gente favorita.' }}</p>
        @endif
    </div>
</section>
