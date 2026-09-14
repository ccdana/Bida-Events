{{--
    Pie común de las plantillas: adorno, nombre, fecha, volver al inicio y crédito de Bida Events.
    Parámetros: footerClass, footerName, footerDate y footerOrnament ('crown', 'dove', 'line' o null).
    En celular todo va apilado y centrado; en pantallas anchas la barra inferior se reparte a los lados.
--}}
<footer class="inv-footer {{ $footerClass ?? '' }}">
    <div class="inv-footer__inner">
        @if(($footerOrnament ?? null) === 'crown')
            @include('invitations.partials.xv.crown', ['class' => 'inv-footer__ornament'])
        @elseif(($footerOrnament ?? null) === 'dove')
            @include('invitations.partials.bautizo.dove', ['class' => 'inv-footer__ornament'])
        @elseif(($footerOrnament ?? null) === 'balloons')
            <span class="inv-footer__ornament inv-cumple-trio" aria-hidden="true">
                @for($balloon = 0; $balloon < 3; $balloon++)
                    @include('invitations.partials.cumple.balloon')
                @endfor
            </span>
        @elseif(($footerOrnament ?? null) === 'line')
            <span class="inv-footer__ornament inv-footer__ornament--line" aria-hidden="true"></span>
        @endif

        <p class="inv-footer__name">{{ $footerName ?? $page->displayName }}</p>
        @if(!empty($footerDate))
            <p class="inv-footer__date">{{ $footerDate }}</p>
        @endif

        <div class="inv-footer__bar">
            <a href="#inicio" class="inv-footer__top">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 19V5M6 11l6-6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Volver al inicio
            </a>

            {{-- Crédito que lleva al sitio de Bida Events en otra pestaña, para que el invitado no pierda la invitación --}}
            <a href="{{ route('home') }}" class="inv-footer__credit" target="_blank" rel="noopener">
                <span class="inv-footer__pitch">
                    ¿Te gustó esta invitación? Crea la tuya
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="inv-footer__text">Hecho con cariño por <span class="inv-footer__brand">Bida Events</span></span>
            </a>
        </div>
    </div>
</footer>
