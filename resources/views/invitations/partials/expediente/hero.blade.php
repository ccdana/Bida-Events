{{--
    Portada de «Expediente abierto»: la hoja del caso dentro de su carpeta, a oscuras. El dedo (o el
    mouse) es una linterna que la ilumina; el botón «Encender las luces» la deja leer entera, y así
    empieza en la vista previa del editor, sin JavaScript y con movimiento reducido. El texto está
    siempre en la página: la oscuridad es solo una capa encima. Estilos en themes/expediente.css.
--}}
@php
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Fiesta de Halloween');
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
@endphp

<header id="inicio" class="inv-hero ex-hero"
    x-data="caseFlashlight(@js(! empty($isPreview)))"
    :class="{ 'is-lit': lit, 'is-aiming': aiming }"
    @pointermove="aim($event)"
    @pointerdown="aim($event)">
    <div class="ex-folder inv-fade-up">
        <span class="ex-folder__tab" aria-hidden="true">{{ $invCopy['case_label'] ?? 'Expediente' }}</span>

        <article class="ex-doc">
            <p class="ex-doc__head">
                <span>{{ $invCopy['case_label'] ?? 'Expediente' }} N.º {{ $page->eventDate->format('d-m') }}</span>
                <span class="ex-stamp">{{ $invCopy['case_open'] ?? 'Caso abierto' }}</span>
            </p>

            <p class="ex-doc__kicker">{{ $heroEyebrow }}</p>

            <figure class="ex-photo">
                <span class="ex-photo__clip" aria-hidden="true"></span>
                @if($page->heroImage)
                    @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768]))
                    <img
                        src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 768) }}"
                        @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="10rem" @endif
                        alt=""
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                    >
                @else
                    <span class="ex-photo__initials">{{ $page->initials() }}</span>
                @endif
            </figure>

            <p class="ex-field">
                <span class="ex-field__label">{{ $invCopy['case_lead'] ?? 'Investigador a cargo' }}</span>
            </p>
            <h1 class="ex-name">{{ $page->displayName }}</h1>

            <dl class="ex-fields">
                <div>
                    <dt class="ex-field__label">{{ $invCopy['case_seen'] ?? 'Cita' }}</dt>
                    <dd>{{ $heroDay }}, {{ $page->eventDate->format('H:i') }}</dd>
                </div>
                @if($page->placeName)
                    <div>
                        <dt class="ex-field__label">{{ $invCopy['case_place'] ?? 'Lugar' }}</dt>
                        <dd>{{ $page->placeName }}</dd>
                    </div>
                @endif
                @if(!empty($heroMessage))
                    <div>
                        <dt class="ex-field__label">{{ $invCopy['case_notes'] ?? 'Notas del caso' }}</dt>
                        <dd class="ex-notes">{{ $heroMessage }}</dd>
                    </div>
                @endif
            </dl>

            {{-- Renglones tachados de archivo: solo adorno --}}
            <p class="ex-redacted" aria-hidden="true"><i style="--w: 38%"></i><i style="--w: 22%"></i><i style="--w: 30%"></i></p>
        </article>
    </div>

    {{-- La oscuridad con el agujero de la linterna --}}
    <div class="ex-dark" aria-hidden="true"></div>

    <button type="button" class="ex-lights" @click="lit = !lit" :aria-pressed="lit.toString()" aria-pressed="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.4 1 1.1 1 1.8V16h5v-.3c0-.7.4-1.4 1-1.8A6 6 0 0 0 12 3z"/></svg>
        <span x-text="lit ? @js($invCopy['lights_off'] ?? 'Usar la linterna') : @js($invCopy['lights_on'] ?? 'Encender las luces')">{{ $invCopy['lights_on'] ?? 'Encender las luces' }}</span>
    </button>

    <a href="#contenido" class="inv-hero__scroll ex-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>

<script>
// Linterna de la portada: el haz sigue al dedo o al mouse; con las luces encendidas se lee todo
function caseFlashlight(startLit) {
    return {
        lit: startLit || window.matchMedia?.('(prefers-reduced-motion: reduce)').matches,
        aiming: false,
        aim(event) {
            if (this.lit) {
                return;
            }

            const box = this.$el.getBoundingClientRect();

            this.aiming = true;
            this.$el.style.setProperty('--ex-x', `${event.clientX - box.left}px`);
            this.$el.style.setProperty('--ex-y', `${event.clientY - box.top}px`);
        },
    };
}
</script>
