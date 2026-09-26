{{--
    Apertura de «La gota»: el agua quieta de la pila vista desde arriba. Al tocarla cae una gota,
    se abren tres ondas y el agua se aclara para dejar ver la portada.
    Lógica en shell/cover-component; estilos en themes/gota.css.
--}}
<div class="inv-themed-intro gt-intro"
    x-data="invitationCover({ part: 650, reveal: 1400, close: 2400 })"
    x-show="!closed"
    :class="{ 'is-drop': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al bautizo de {{ $page->displayName }}">
    <p class="gt-intro__eyebrow">
        @if($guest)
            {{ $guest->name }}, {{ mb_strtolower($invCopy['intro_eyebrow'] ?? 'Tienes una invitación') }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Tienes una invitación' }}
        @endif
    </p>

    <button type="button" class="gt-water" data-cover-trigger @click="open()" aria-label="Tocar el agua y abrir la invitación">
        <span class="gt-water__drop" aria-hidden="true"></span>
        <span class="gt-water__ripple" aria-hidden="true"></span>
        <span class="gt-water__ripple" aria-hidden="true"></span>
        <span class="gt-water__ripple" aria-hidden="true"></span>
        <span class="gt-water__splash" aria-hidden="true">
            @foreach([-70, -45, -20, 0, 20, 45, 70, -90, 90] as $index => $angle)
                <i style="--a: {{ $angle }}deg; --h: {{ 2.2 + ($index % 3) * 0.7 }}rem"></i>
            @endforeach
        </span>
        <span class="gt-water__name">{{ $page->displayName }}</span>
    </button>

    <p class="gt-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el agua' }}</p>
</div>
