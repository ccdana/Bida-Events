{{--
    Pie común de las plantillas: adorno, nombre, fecha, volver al inicio y crédito de Bida Events.
    Parámetros: footerClass, footerName, footerDate y footerOrnament ('crown', 'dove', 'balloons', 'cap', 'pumpkin', 'line' o null).
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
        @elseif(($footerOrnament ?? null) === 'cap')
            @include('invitations.partials.graduacion.cap', ['class' => 'inv-footer__ornament'])
        @elseif(($footerOrnament ?? null) === 'pumpkin')
            @include('invitations.partials.halloween.pumpkin', ['class' => 'inv-footer__ornament'])
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

            {{-- Crédito del pie (InvitationPage::footerBrand): el de Bida Events, que lleva a su sitio en otra
                 pestaña para que el invitado no pierda la invitación, o el del revendedor con marca blanca --}}
            @php($footerBrand = $page->footerBrand())
            @if($footerBrand['url'])
                <a href="{{ $footerBrand['url'] }}" class="inv-footer__credit" target="_blank" rel="noopener">
                    <span class="inv-footer__pitch">
                        {{ $footerBrand['pitch'] }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="inv-footer__text">Hecho con cariño por <span class="inv-footer__brand">{{ $footerBrand['name'] }}</span></span>
                </a>
            @else
                <p class="inv-footer__credit">
                    <span class="inv-footer__text">Hecho con cariño por <span class="inv-footer__brand">{{ $footerBrand['name'] }}</span></span>
                </p>
            @endif
        </div>
    </div>
</footer>
