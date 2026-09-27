{{--
    Itinerario de «Partitura a dos voces»: el programa del concierto en movimientos. Cada momento es
    un movimiento con su número romano, su nombre y la indicación de tempo que le toca (Andante,
    Allegro, Adagio…: la última siempre es Finale), la hora y, debajo, un compás de la melodía.
    Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
    $roman = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    $tempos = ['Andante', 'Allegro', 'Adagio', 'Vivace', 'Moderato', 'Cantabile', 'Presto', 'Allegretto', 'Largo', 'Scherzo', 'Grazioso'];
@endphp

<section class="inv-section reveal pt-movements" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Programa del concierto',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="pt-program">
                @foreach($eventos as $index => $evento)
                    @php
                        $tempo = $loop->last && count($eventos) > 1 ? 'Finale' : $tempos[$index % count($tempos)];
                        // Un compás distinto para cada movimiento: la melodía sube o baja según su lugar
                        $bar = collect(range(0, 3))->map(fn ($note) => [40 + $note * 70, [40, 30, 25, 35, 20, 30][($index + $note) % 6]])->all();
                    @endphp
                    <li class="pt-movement" data-step style="--step: {{ $index }}">
                        <span class="pt-movement__num" aria-hidden="true">{{ $roman[$index] ?? ($index + 1) }}</span>
                        <div class="pt-movement__body">
                            <p class="pt-movement__line">
                                <span class="pt-movement__title">{{ $evento['titulo'] ?? '' }}</span>
                                <em class="pt-movement__tempo">{{ $tempo }}</em>
                            </p>
                            @if(!empty($evento['hora']))
                                <time class="pt-movement__time">{{ $evento['hora'] }}</time>
                            @endif
                            @if(!empty($evento['descripcion']))
                                <p class="pt-movement__text">{{ $evento['descripcion'] }}</p>
                            @endif
                            @include('invitations.partials.tendencias.partitura.staff', [
                                'notes' => $bar,
                                'stem' => $index % 2 === 0 ? 'up' : 'down',
                                'class' => 'pt-movement__staff pt-movement__staff--'.($index % 2 + 1),
                                'final' => $loop->last,
                            ])
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto publicaremos el programa del concierto.' }}</p>
        @endif
    </div>
</section>
