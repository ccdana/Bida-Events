{{--
    Pie del sitio público, con los canales de contacto. Recibe $navLinks, $socials, $landings, $contactUrl,
    $accountUrl, $accountLabel y $bida.
--}}
<footer class="border-t border-site-line">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-10 text-[0.95rem] text-site-muted lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <div class="flex items-center gap-4">
            <x-brand.mark class="h-8 w-auto" />
            <p>© {{ now()->year }} {{ $bida['brand'] }}. Invitaciones y tarjetas digitales hechas en Bolivia.</p>
        </div>
        <nav class="flex flex-wrap gap-x-6 gap-y-2" aria-label="Pie de página">
            @foreach($navLinks as $href => $label)
                <a href="{{ $href }}" class="site-nav-link hover:text-site-ink">{{ $label }}</a>
            @endforeach
            <a href="{{ $accountUrl }}" class="site-nav-link font-medium text-site-ink">{{ $accountLabel }}</a>
        </nav>
        <ul class="-ml-2.5 flex flex-wrap items-center gap-1 lg:ml-0" aria-label="Contacto y redes sociales">
            <li>
                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-social" aria-label="WhatsApp">
                    <x-phosphor-whatsapp-logo class="size-5" aria-hidden="true" />
                </a>
            </li>
            @if(! empty($bida['email']))
                <li>
                    <a href="mailto:{{ $bida['email'] }}" class="site-social" aria-label="Correo: {{ $bida['email'] }}">
                        <x-phosphor-envelope-simple class="size-5" aria-hidden="true" />
                    </a>
                </li>
            @endif
            @foreach($socials as $social)
                <li>
                    <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="site-social" aria-label="{{ $social['label'] }}">
                        <x-dynamic-component :component="'phosphor-'.$social['icon']" class="size-5" aria-hidden="true" />
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Profesionales y páginas legales: en todas las páginas del sitio --}}
    <nav class="mx-auto flex max-w-7xl flex-wrap gap-x-6 gap-y-2 border-t border-site-line px-5 py-6 text-sm text-site-muted lg:px-8" aria-label="Más información">
        @unless(isset($navLinks[route('professionals')]))
            <a href="{{ route('professionals') }}" class="site-nav-link font-medium text-site-ink">Para profesionales</a>
        @endunless
        @foreach(\App\Support\LegalPages::links() as $legalLink)
            <a href="{{ $legalLink['url'] }}" class="site-nav-link hover:text-site-ink">{{ $legalLink['label'] }}</a>
        @endforeach
    </nav>

    @if(count($landings ?? []))
        {{-- Enlazado interno: ayuda a que los buscadores encuentren cada página por evento --}}
        <nav class="mx-auto max-w-7xl border-t border-site-line px-5 py-6 text-sm text-site-muted lg:px-8" aria-label="Invitaciones por evento">
            <ul class="flex flex-wrap gap-x-6 gap-y-2">
                @foreach($landings as $landing)
                    <li><a href="{{ $landing['url'] }}" class="site-nav-link hover:text-site-ink">{{ $landing['label'] }}</a></li>
                @endforeach
            </ul>
        </nav>
    @endif
</footer>
