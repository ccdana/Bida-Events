{{--
    Selector de idioma del sitio: «ES · EN» en letra pequeña, sin botones. Solo aparece si la página
    tiene versión en el otro idioma (App\Support\Localization::switchUrl): las invitaciones y los
    paneles no la tienen. El idioma activo no es un enlace. Los demás idiomas los resuelve el
    traductor del navegador, que lee el lang de cada página.
--}}
@props(['touch' => false])
@php
    $localizations = collect(\App\Support\Localization::locales())
        ->mapWithKeys(fn (string $code) => [$code => \App\Support\Localization::switchUrl($code)])
        ->filter();
    $languageNames = \App\Support\Localization::names();
@endphp
@if($localizations->count() > 1)
    <nav {{ $attributes->class(['inline-flex items-center gap-1.5 text-[0.8rem] font-medium tracking-[0.08em] text-site-muted']) }} aria-label="{{ __('Idioma') }}" translate="no">
        @foreach($localizations as $code => $url)
            {{-- «touch»: en celulares y tablets cada opción mide al menos 44 px (clase site-touch) --}}
            @if($code === app()->getLocale())
                <span @class(['text-site-ink', 'site-touch' => $touch]) aria-current="true" lang="{{ $code }}">{{ \App\Support\Localization::short($code) }}</span>
            @else
                <a href="{{ $url }}" hreflang="{{ $code }}" lang="{{ $code }}" title="{{ $languageNames[$code] }}" @class(['transition-colors hover:text-site-ink', 'site-touch' => $touch])>{{ \App\Support\Localization::short($code) }}</a>
            @endif
            @unless($loop->last)
                <span aria-hidden="true">·</span>
            @endunless
        @endforeach
    </nav>
@endif
