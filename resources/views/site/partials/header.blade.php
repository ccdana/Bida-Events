{{--
    Cabecera del sitio público. Recibe $navLinks (href => texto), $contactUrl, $accountUrl y $accountLabel.
--}}
<header data-site-header x-data="{ open: false }" @keydown.escape.window="open = false"
    class="site-header sticky top-0 z-40" :class="{ 'is-open': open }">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-5 lg:h-[72px] lg:px-8">
        <a href="{{ route('home') }}" class="text-lg">
            <x-brand.logo animated />
        </a>

        <nav class="hidden items-center gap-8 text-[0.95rem] text-site-muted lg:flex" aria-label="Secciones">
            @foreach($navLinks as $href => $label)
                <a href="{{ $href }}" class="site-nav-link hover:text-site-ink">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-5 lg:flex">
            @include('layouts.partials.theme-toggle')
            <a href="{{ $accountUrl }}" class="site-nav-link text-[0.95rem] font-medium text-site-muted hover:text-site-ink">{{ $accountLabel }}</a>
            <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn" data-magnetic>
                <x-phosphor-whatsapp-logo aria-hidden="true" />
                Escríbenos
            </a>
        </div>

        <div class="flex items-center gap-1 lg:hidden">
            @include('layouts.partials.theme-toggle')
            <button type="button" class="relative grid size-11 place-items-center rounded-full border border-site-line"
                @click="open = !open" :aria-expanded="open.toString()" aria-controls="menu-movil">
                <span class="sr-only" x-text="open ? 'Cerrar menú' : 'Abrir menú'">Abrir menú</span>
                <x-phosphor-list class="site-swap is-on" x-bind:class="{ 'is-on': !open }" aria-hidden="true" />
                <x-phosphor-x class="site-swap" x-bind:class="{ 'is-on': open }" aria-hidden="true" />
            </button>
        </div>
    </div>

    <div id="menu-movil" x-show="open" x-cloak
        x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-y-2 opacity-0"
        x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="-translate-y-2 opacity-0"
        class="px-5 pb-6 lg:hidden">
        <nav class="flex flex-col text-lg" aria-label="Secciones">
            @foreach($navLinks as $href => $label)
                <a href="{{ $href }}" @click="open = false" class="border-b border-site-line py-3.5">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="mt-6 grid gap-3">
            <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg justify-center">
                <x-phosphor-whatsapp-logo aria-hidden="true" />
                Escríbenos
            </a>
            <a href="{{ $accountUrl }}" class="site-btn site-btn--ghost site-btn--lg justify-center">{{ $accountLabel }}</a>
        </div>
    </div>
</header>
