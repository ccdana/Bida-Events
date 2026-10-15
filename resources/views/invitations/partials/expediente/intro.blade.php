{{--
    Apertura de «Expediente abierto»: una carpeta de cartulina con el sello del caso, bajo el haz de
    una linterna que busca. Al tocarla, la hoja del expediente sale de la carpeta y la carpeta cae.
    Lógica en shell/cover-component; estilos en themes/expediente.css.
--}}
<div class="inv-themed-intro ex-intro"
    x-data="invitationCover({ part: 700, reveal: 1500, close: 2400 })"
    x-show="!closed"
    :class="{ 'is-pulling': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="{{ __('Invitación a la fiesta de Halloween de :displayName', ['displayName' => $page->displayName]) }}">
    <p class="ex-intro__eyebrow">
        @if($guest)
            {{ $guest->name }}, {{ mb_strtolower($invCopy['intro_eyebrow'] ?? __('Tienes un caso asignado')) }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? __('Tienes un caso asignado') }}
        @endif
    </p>

    <button type="button" class="ex-closed" data-cover-trigger @click="open()" aria-label="{{ __('Abrir la carpeta del caso') }}">
        <span class="ex-closed__sheet" aria-hidden="true">
            <i></i><i></i><i></i><i></i>
        </span>
        <span class="ex-closed__folder">
            <span class="ex-closed__tab">{{ $invCopy['case_label'] ?? __('Expediente') }}</span>
            <span class="ex-closed__label">{{ $page->displayName }}</span>
            <span class="ex-closed__stamp">{{ $invCopy['case_open'] ?? __('Caso abierto') }}</span>
        </span>
    </button>

    <p class="ex-intro__hint">{{ $invCopy['intro_hint'] ?? __('Toca la carpeta para abrirla') }}</p>
</div>
