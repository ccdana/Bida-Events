{{--
    Acto II · La marea: subimos desde la superficie. Los momentos clave salen del agua uno a uno
    sobre una línea de marea que se llena con la lectura, y a mitad de camino, la cita elegida.
    Datos: relato.momentos[] (cuando, titulo, descripcion, foto, foto_alt) y relato.cita.
--}}
@php
    $half = (int) ceil($moments->count() / 2);
    $momentGroups = $moments->count() > 1 ? [$moments->slice(0, $half), $moments->slice($half)] : [$moments, collect()];
@endphp

<section class="story-act story-act--2" id="relato" data-act>
    <div class="story-act__body">
        @include('invitations.partials.historia.mark', ['number' => 'II', 'name' => $invCopy['act2_label']])

        <h2 class="story-title reveal">{{ $invCopy['act2_title'] }}</h2>

        <div class="story-tide" data-tide>
            <span class="story-tide__line" aria-hidden="true"><span class="story-tide__fill" data-tide-fill></span></span>

            @if($moments->isEmpty())
                <div class="story-copy reveal">
                    <p>{{ $invCopy['act2_moments_fallback'] }}</p>
                </div>
            @endif

            @foreach($momentGroups as $groupIndex => $group)
                @if($group->isNotEmpty())
                    <ol class="story-moments" start="{{ $groupIndex === 0 ? 1 : $half + 1 }}">
                        @foreach($group as $moment)
                            <li class="story-moment reveal">
                                @if($when = $filled($moment['cuando'] ?? null))
                                    <p class="story-moment__when">{{ $when }}</p>
                                @endif
                                @if($title = $filled($moment['titulo'] ?? null))
                                    <h3 class="story-moment__title">{{ $title }}</h3>
                                @endif
                                @if($text = $filled($moment['descripcion'] ?? null))
                                    <p class="story-moment__text">{!! nl2br(e($text)) !!}</p>
                                @endif
                                @if($photo = $filled($moment['foto'] ?? null))
                                    <figure class="story-moment__photo" data-surface>
                                        <img src="{{ \App\Support\CloudinaryImage::url($photo, 700) }}"
                                            alt="{{ $filled($moment['foto_alt'] ?? null) ?? ($title ?? 'Uno de nuestros momentos') }}"
                                            width="700" height="525" loading="lazy" decoding="async">
                                    </figure>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif

                @if($groupIndex === 0)
                    <figure class="story-quote reveal">
                        <blockquote><p>{{ $quote['text'] }}</p></blockquote>
                        <figcaption>{{ $quote['author'] }}, <cite>{{ $quote['work'] }}</cite></figcaption>
                    </figure>
                @endif
            @endforeach
        </div>
    </div>
</section>
