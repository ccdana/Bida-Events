{{--
    Barra lateral de los paneles (administrador y revendedor): navegación, la acción principal y, en
    la lista de invitaciones, los filtros. En pantallas anchas queda fija a la izquierda; en el
    celular se abre desde el botón «Menú» de la cabecera (sideOpen, en el <body>).

    Recibe $homeUrl, $nav (lista de ['label', 'url', 'icon', 'active', 'badge'?]), $primary
    (['label', 'url'] o null) y, opcionales, $filterGroups (App\Support\InvitationFilters::sidebar),
    $filterValues y $filterRoute.
--}}
<aside class="panel-side" :class="{ 'is-open': sideOpen }" aria-label="Menú del panel">
    <div class="panel-side__head">
        <a href="{{ $homeUrl }}" class="panel-side__logo"><x-brand.logo /></a>
        <button type="button" class="admin-icon-button panel-side__close" @click="sideOpen = false" aria-label="Cerrar el menú">
            <x-phosphor-x aria-hidden="true" />
        </button>
    </div>

    @if($primary)
        <a href="{{ $primary['url'] }}" class="admin-primary-button panel-side__primary">
            <x-phosphor-plus-bold aria-hidden="true" />
            {{ $primary['label'] }}
        </a>
    @endif

    <nav class="panel-side__nav" aria-label="Secciones">
        @foreach($nav as $item)
            <a href="{{ $item['url'] }}" @class(['panel-side__link', 'is-active' => $item['active']]) @if($item['active']) aria-current="page" @endif>
                <x-dynamic-component :component="'phosphor-'.$item['icon']" aria-hidden="true" />
                <span>{{ $item['label'] }}</span>
                @if(! empty($item['badge']))
                    <span class="panel-side__badge" title="{{ $item['badgeTitle'] ?? '' }}">{{ $item['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    @if(! empty($filterGroups))
        <form method="GET" action="{{ route($filterRoute) }}" class="panel-filters" role="search" aria-label="Filtrar invitaciones">
            <p class="panel-filters__title">Filtrar invitaciones</p>

            <label for="panel-buscar" class="sr-only">Buscar</label>
            <div class="relative">
                <x-phosphor-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-site-muted" aria-hidden="true" />
                <input id="panel-buscar" type="search" name="q" value="{{ $filterValues['q'] ?? '' }}" autocomplete="off"
                    class="admin-input has-icon" placeholder="Evento, enlace o cliente">
            </div>
            @foreach(collect($filterValues ?? [])->except('q')->filter() as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach

            @foreach($filterGroups as $key => $group)
                <div class="panel-filters__group">
                    <p class="panel-filters__label">{{ $group['label'] }}</p>
                    <ul>
                        @foreach($group['options'] as $option)
                            <li>
                                <a href="{{ $option['url'] }}" @class(['panel-filters__option', 'is-active' => $option['active']])
                                    @if($option['active']) aria-current="true" @endif>
                                    <span>{{ $option['label'] }}</span>
                                    @if($option['count'] !== null)
                                        <span class="panel-filters__count">{{ $option['count'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            @if(\App\Support\InvitationFilters::isFiltered($filterValues ?? []))
                <a href="{{ route($filterRoute) }}" class="admin-link-button panel-filters__clear">
                    <x-phosphor-x aria-hidden="true" />
                    Quitar filtros
                </a>
            @endif
        </form>
    @endif
</aside>
<div class="panel-side__backdrop" x-show="sideOpen" x-cloak x-transition.opacity @click="sideOpen = false"></div>
