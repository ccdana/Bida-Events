@extends('layouts.admin')

@section('title', 'Sistema visual')

@section('content')
    <header class="site-enter">
        <p class="admin-eyebrow">Referencia interna</p>
        <h1 class="mt-1 text-3xl font-semibold tracking-tight">Sistema visual</h1>
        <p class="mt-2 max-w-2xl text-site-muted">
            Conviven dos lenguajes: el del sitio y los paneles, y el de la invitación, que se repinta con
            la paleta de cada evento. Esta página muestra los dos con sus valores reales, para no
            inventar estilos nuevos en cada pantalla.
        </p>
        <p class="mt-2 max-w-2xl text-sm text-site-muted">
            Los tokens viven en <code class="font-mono text-xs">resources/css/site/site.css</code> y
            <code class="font-mono text-xs">resources/css/invitation/base.css</code>. Si cambias una mezcla allí,
            actualiza también <code class="font-mono text-xs">App\Support\ColorContrast</code>, que es lo que mide esta página.
        </p>
    </header>

    {{-- ── Colores del sitio ────────────────────────────────────────────── --}}
    <section class="site-enter mt-12" style="--enter-index: 1">
        <h2 class="text-xl font-semibold tracking-tight">Colores del sitio y los paneles</h2>
        <p class="mt-1 text-sm text-site-muted">
            Valores del modo claro. En oscuro cambian de valor pero no de nombre, así que se usan siempre
            por token. La columna de contraste mide el color sobre el fondo de la página.
        </p>

        <div class="mt-4 overflow-hidden rounded-[16px] border border-site-line bg-site-surface">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th scope="col">Muestra</th>
                        <th scope="col">Token</th>
                        <th scope="col">Dónde se usa</th>
                        <th scope="col" class="text-right">Contraste en claro</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siteColors as $color)
                        <tr>
                            <td>
                                {{-- La muestra usa el token, así que enseña el color del modo activo; el hex es el del claro --}}
                                <span class="flex items-center gap-3">
                                    <span class="size-8 shrink-0 rounded-lg border border-site-line" style="background: var({{ $color['token'] }})"></span>
                                    <code class="font-mono text-xs text-site-muted">{{ $color['value'] }}</code>
                                </span>
                            </td>
                            <td><code class="font-mono text-xs">{{ $color['token'] }}</code></td>
                            <td class="text-site-muted">{{ $color['usage'] }}</td>
                            <td class="text-right tabular-nums">
                                @if($color['ratio'] === null)
                                    <span class="text-site-muted">—</span>
                                @else
                                    <span @class(['text-site-danger' => $color['ratio'] < 4.5])>{{ number_format($color['ratio'], 1) }}:1</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- ── Tipografía ───────────────────────────────────────────────────── --}}
    <section class="site-enter mt-12" style="--enter-index: 2">
        <h2 class="text-xl font-semibold tracking-tight">Tipografía</h2>
        <p class="mt-1 text-sm text-site-muted">
            Una sola familia en el sitio y los paneles: Outfit Variable (<code class="font-mono text-xs">--font-display</code>).
            La invitación usa las tres fuentes que elige el cliente.
        </p>

        <div class="mt-4 space-y-4 rounded-[16px] border border-site-line bg-site-surface p-6">
            @foreach([
                ['Título de pantalla', 'text-3xl font-semibold tracking-tight'],
                ['Título de sección', 'text-xl font-semibold tracking-tight'],
                ['Subtítulo', 'text-lg font-semibold'],
                ['Texto corriente', 'text-base'],
                ['Texto secundario', 'text-sm text-site-muted'],
                ['Ayuda y pies', 'text-xs text-site-muted'],
            ] as [$label, $classes])
                <div class="flex flex-col gap-1 border-b border-site-line pb-3 last:border-0 last:pb-0 sm:flex-row sm:items-baseline sm:gap-6">
                    <code class="w-56 shrink-0 font-mono text-xs text-site-muted">{{ $classes }}</code>
                    <p class="{{ $classes }}">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ── Componentes ──────────────────────────────────────────────────── --}}
    <section class="site-enter mt-12" style="--enter-index: 3">
        <h2 class="text-xl font-semibold tracking-tight">Componentes</h2>
        <p class="mt-1 text-sm text-site-muted">Las piezas que ya existen; antes de crear una nueva, conviene mirar aquí.</p>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div class="admin-card space-y-3 p-5">
                <p class="admin-eyebrow">Botones</p>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="site-btn">site-btn</button>
                    <button type="button" class="site-btn site-btn--ghost">site-btn--ghost</button>
                    <button type="button" class="admin-primary-button">
                        <x-phosphor-plus-bold aria-hidden="true" />
                        admin-primary-button
                    </button>
                    <button type="button" class="admin-link-button">admin-link-button</button>
                    <button type="button" class="admin-icon-button" aria-label="Ejemplo de botón de ícono">
                        <x-phosphor-pencil-simple aria-hidden="true" />
                    </button>
                </div>
            </div>

            <div class="admin-card space-y-3 p-5">
                <p class="admin-eyebrow">Estados</p>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="admin-status-badge is-active"><span class="admin-status-dot"></span>Activa</span>
                    <span class="admin-status-badge"><span class="admin-status-dot"></span>Inactiva</span>
                    <span class="admin-status-badge is-declined"><span class="admin-status-dot"></span>Con problema</span>
                </div>
                <p class="adm-flash" role="status">
                    <x-phosphor-check-circle class="size-5 shrink-0" aria-hidden="true" />
                    Aviso de acción completada (adm-flash)
                </p>
            </div>

            <div class="admin-card space-y-3 p-5">
                <p class="admin-eyebrow">Campos</p>
                <div>
                    <label class="admin-label" for="ds-input">Campo normal</label>
                    <input id="ds-input" type="text" class="admin-input" placeholder="admin-input" readonly>
                </div>
                <div>
                    <label class="admin-label" for="ds-input-sm">Campo compacto</label>
                    <input id="ds-input-sm" type="text" class="admin-input admin-input--sm" placeholder="admin-input--sm" readonly>
                </div>
                <label class="admin-toggle-row" for="ds-switch">
                    <input id="ds-switch" type="checkbox" class="rounded border-stone-300">
                    <span>admin-toggle-row</span>
                </label>
            </div>

            <div class="admin-card space-y-3 p-5">
                <p class="admin-eyebrow">Superficies</p>
                <p class="text-sm text-site-muted">
                    <code class="font-mono text-xs">admin-card</code> para paneles,
                    <code class="font-mono text-xs">admin-panel-soft</code> para bloques dentro de una tarjeta,
                    y <code class="font-mono text-xs">site-enter</code> con <code class="font-mono text-xs">--enter-index</code>
                    para la entrada escalonada de cada bloque.
                </p>
                <div class="admin-panel-soft p-4 text-sm">admin-panel-soft</div>
                <p class="text-sm text-site-muted">
                    Radios: píldora en botones, 16px en tarjetas y medios, 12px en campos.
                </p>
            </div>
        </div>
    </section>

    {{-- ── Temas de la invitación ───────────────────────────────────────── --}}
    <section class="site-enter mt-12" style="--enter-index: 4">
        <h2 class="text-xl font-semibold tracking-tight">Temas de la invitación</h2>
        <p class="mt-1 text-sm text-site-muted">
            La invitación recibe cinco colores del cliente y de ahí deriva el resto mezclándolos.
            Estas son las paletas con las que nace cada plantilla y el contraste que resulta.
        </p>

        @foreach($themes as $theme)
            @php($failing = collect($theme['audit'])->reject(fn ($check) => $check['passes']))
            <article class="mt-6 overflow-hidden rounded-[16px] border border-site-line bg-site-surface">
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-site-line p-5">
                    <div>
                        <h3 class="text-lg font-semibold tracking-tight">{{ $theme['label'] }}</h3>
                        <p class="mt-1 max-w-xl text-sm text-site-muted">{{ $theme['description'] }}</p>
                    </div>
                    <span class="admin-status-badge {{ $failing->isEmpty() ? 'is-active' : 'is-declined' }}">
                        <span class="admin-status-dot"></span>
                        {{ $failing->isEmpty() ? 'Contraste AA' : $failing->count().' por revisar' }}
                    </span>
                </header>

                <div class="grid gap-6 p-5 lg:grid-cols-2">
                    <div>
                        <p class="admin-eyebrow">Paleta del cliente</p>
                        <ul class="mt-2 space-y-1.5">
                            @foreach($theme['palette'] as $name => $value)
                                <li class="flex items-center gap-3 text-sm">
                                    <span class="size-6 shrink-0 rounded-md border border-site-line" style="background: {{ $value }}"></span>
                                    <span class="w-24 shrink-0">{{ $name }}</span>
                                    <code class="font-mono text-xs text-site-muted">{{ $value }}</code>
                                </li>
                            @endforeach
                        </ul>

                        <p class="admin-eyebrow mt-5">Tonos derivados</p>
                        <ul class="mt-2 space-y-1.5">
                            @foreach($theme['tokens'] as $name => $value)
                                <li class="flex items-center gap-3 text-sm">
                                    <span class="size-6 shrink-0 rounded-md border border-site-line" style="background: {{ $value }}"></span>
                                    <code class="w-28 shrink-0 font-mono text-xs">--inv-{{ $name }}</code>
                                    <code class="font-mono text-xs text-site-muted">{{ $value }}</code>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <p class="admin-eyebrow">Contraste medido</p>
                        <table class="adm-table mt-2">
                            <thead>
                                <tr>
                                    <th scope="col">Pieza</th>
                                    <th scope="col" class="text-right">Real</th>
                                    <th scope="col" class="text-right">Mínimo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($theme['audit'] as $check)
                                    <tr>
                                        <td>{{ $check['label'] }}</td>
                                        <td class="text-right tabular-nums">
                                            <span @class(['text-site-danger' => ! $check['passes']])>{{ number_format($check['ratio'], 2) }}:1</span>
                                        </td>
                                        <td class="text-right tabular-nums text-site-muted">{{ number_format($check['min'], 1) }}:1</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Muestra con los colores del tema, para ver los tonos en contexto --}}
                        <div class="mt-4 rounded-[12px] border border-site-line p-4"
                            style="background: {{ $theme['palette']['background'] }}; color: {{ $theme['palette']['text'] }}">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.34em]" style="color: {{ $theme['tokens']['accent-ink'] }}">Detalles en color</p>
                            <p class="mt-1 text-xl font-semibold">Título de sección</p>
                            <p class="mt-1 text-sm" style="color: {{ $theme['tokens']['muted'] }}">Texto apagado, como el de una descripción.</p>
                            <p class="mt-0.5 text-xs" style="color: {{ $theme['tokens']['soft'] }}">Etiqueta o ayuda debajo de un campo.</p>
                            <span class="mt-3 inline-block rounded-md px-3 py-1.5 text-xs font-semibold"
                                style="background: {{ $theme['palette']['text'] }}; color: {{ $theme['palette']['background'] }}">Confirmar asistencia</span>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </section>
@endsection
