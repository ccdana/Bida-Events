{{-- Apertura de XV años: telón oscuro, corona que se dibuja, nombre y destellos; luego el telón se abre en dos --}}
@php($introEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV Años'))
<div class="inv-xv-intro" aria-hidden="true">
    <span class="inv-xv-intro__panel inv-xv-intro__panel--top"></span>
    <span class="inv-xv-intro__panel inv-xv-intro__panel--bottom"></span>
    <span class="inv-xv-intro__line"></span>

    <div class="inv-xv-intro__sparks">
        @for($i = 0; $i < 16; $i++)
            <span style="--a: {{ $i * 22.5 }}deg; --r: {{ 90 + ($i * 37) % 70 }}px"></span>
        @endfor
    </div>

    <div class="inv-xv-intro__center">
        @include('invitations.partials.xv.crown', ['class' => 'inv-xv-intro__crown'])
        <p class="inv-xv-intro__eyebrow">{{ $introEyebrow }}</p>
        <p class="inv-xv-intro__name">{{ $page->displayName }}</p>
    </div>
</div>
