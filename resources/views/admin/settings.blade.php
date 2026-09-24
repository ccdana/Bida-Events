@extends('layouts.admin')

@section('title', 'Ajustes')

{{--
    Ajustes del sitio, en tres pestañas: lo que se cobra por invitación (paquetes y promoción), las
    temporadas (cada una por separado) y «Hazlo tú» (planes mensuales). Un solo formulario: las
    pestañas solo ordenan la pantalla, se guarda todo junto desde la barra fija de abajo. Si al
    guardar algo no pasa la validación, se abre la pestaña donde está el error.
--}}
@php
    // Los <input type="datetime-local"> quieren «2026-09-21T23:59»
    $forInput = fn (?string $value) => $value ? str_replace(' ', 'T', substr($value, 0, 16)) : '';
    $errorKeys = collect($errors->keys());
    $startTab = match (true) {
        $errorKeys->contains(fn ($key) => str_starts_with($key, 'seasons')) => 'temporadas',
        $errorKeys->contains(fn ($key) => str_starts_with($key, 'reseller_plans')) => 'hazlo',
        default => 'paquetes',
    };
    $tabs = ['paquetes' => 'Paquetes y promoción', 'temporadas' => 'Temporadas', 'hazlo' => 'Hazlo tú'];
    $enabledTemplates = collect(old('templates', collect($seasonalTemplates)->filter(fn ($t) => $t['enabled'])->keys()->all()));
    $statusLabels = [
        'selling' => ['En el sitio', 'is-success'],
        'off' => ['Apagada', 'is-primary'],
        'ended' => ['Terminada', 'is-primary'],
        'no_designs' => ['Sin diseños activos', 'is-primary'],
    ];
    $currency = \App\Support\Money::code();
@endphp

@section('content')
    <div class="grid gap-8" x-data="{ tab: @js($startTab) }">
        <header class="site-enter">
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Ajustes</h1>
            <p class="mt-2 max-w-[62ch] text-site-muted">
                Los precios que ve la gente, hasta cuándo dura cada promoción, qué temporadas se ofrecen y los planes de
                Hazlo tú. Todo en dólares ({{ $currency }}) y se aplica al instante, sin tocar el código.
            </p>
        </header>

        @if($errors->any())
            <div class="site-enter rounded-[12px] border border-site-danger/40 bg-site-danger/10 p-4" role="alert">
                <p class="font-medium text-site-danger">No se guardaron los cambios</p>
                <ul class="mt-2 list-disc pl-5 text-sm text-site-danger">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <nav class="set-tabs site-enter" role="tablist" aria-label="Secciones de ajustes">
            @foreach($tabs as $key => $label)
                <button type="button" role="tab" id="ajustes-{{ $key }}" aria-controls="panel-{{ $key }}"
                    @click="tab = @js($key)" :aria-selected="(tab === @js($key)).toString()"
                    aria-selected="{{ $key === $startTab ? 'true' : 'false' }}"
                    :class="{ 'is-active': tab === @js($key) }" @class(['set-tab', 'is-active' => $key === $startTab])>
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="set-form">
            @csrf
            @method('PUT')

            {{-- ══ Paquetes y promoción ══ --}}
            <div id="panel-paquetes" role="tabpanel" aria-labelledby="ajustes-paquetes" x-show="tab === 'paquetes'" @if($startTab !== 'paquetes') x-cloak @endif>
                <section class="set-block">
                    <div class="set-block__head">
                        <div>
                            <h2 class="set-block__title">Promoción de los paquetes</h2>
                            <p class="set-block__text">
                                Encendida, la página muestra el precio normal tachado y cobra el precio con descuento. Al pasar
                                la fecha de término vuelve sola a los precios normales.
                            </p>
                        </div>
                        <span class="admin-status-badge {{ $promoActive ? 'is-success' : 'is-primary' }}">
                            <span class="admin-status-dot"></span>
                            {{ $promoActive ? 'Activa ahora' : 'Sin promoción' }}
                        </span>
                    </div>

                    <div class="set-row">
                        <label class="set-switch">
                            <input type="checkbox" name="promo_active" value="1" class="adm-switch-input" @checked(old('promo_active', $settings['promo']['active']))>
                            <span class="adm-switch" aria-hidden="true"></span>
                            <span>
                                <span class="font-medium">Promoción encendida</span>
                                <span class="block text-sm text-site-muted">{{ $settings['promo']['label'] }}</span>
                            </span>
                        </label>
                        <div class="set-field">
                            <label for="promo-ends" class="admin-label">Hasta cuándo dura <span class="font-normal text-site-muted">(opcional)</span></label>
                            <input id="promo-ends" type="datetime-local" name="promo_ends_at" class="admin-input"
                                value="{{ old('promo_ends_at', $forInput($settings['promo']['ends_at'])) }}">
                            <p class="mt-1.5 text-xs text-site-muted">Vacío: dura hasta que la apagues.</p>
                        </div>
                    </div>
                </section>

                <section class="set-block">
                    <div class="set-block__head">
                        <div>
                            <h2 class="set-block__title">Precios de los paquetes</h2>
                            <p class="set-block__text">Lo que cuesta cada invitación hecha por el equipo. El precio con descuento se cobra mientras la promoción esté activa.</p>
                        </div>
                    </div>

                    <div class="set-list">
                        <div class="set-list__head" aria-hidden="true">
                            <span>Paquete</span><span>Precio ({{ $currency }})</span><span>Con descuento</span>
                        </div>
                        @foreach($settings['packages'] as $package)
                            <div class="set-list__row">
                                <div class="min-w-0">
                                    <p class="font-medium">{{ $package['name'] }}</p>
                                    <p class="text-sm text-site-muted">{{ $package['summary'] }}</p>
                                </div>
                                <div>
                                    <label for="precio-{{ $package['key'] }}" class="admin-label set-list__label">Precio ({{ $currency }})</label>
                                    <input id="precio-{{ $package['key'] }}" type="number" min="0" max="100000" required class="admin-input"
                                        name="packages[{{ $package['key'] }}][price]" value="{{ old('packages.'.$package['key'].'.price', $package['price']) }}">
                                </div>
                                <div>
                                    <label for="promo-{{ $package['key'] }}" class="admin-label set-list__label">Con descuento</label>
                                    <input id="promo-{{ $package['key'] }}" type="number" min="0" max="100000" class="admin-input" placeholder="Sin descuento"
                                        name="packages[{{ $package['key'] }}][promo_price]" value="{{ old('packages.'.$package['key'].'.promo_price', $package['promo_price']) }}">
                                    @error('packages.'.$package['key'].'.promo_price')
                                        <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            {{-- ══ Temporadas: cada una se enciende, se apaga y se ajusta por separado ══ --}}
            <div id="panel-temporadas" role="tabpanel" aria-labelledby="ajustes-temporadas" x-show="tab === 'temporadas'" @if($startTab !== 'temporadas') x-cloak @endif>
                @foreach($settings['seasons'] as $season)
                    @php([$statusLabel, $statusClass] = $statusLabels[$seasonStatuses[$season['key']] ?? 'off'])
                    @php($seasonTemplates = collect($seasonalTemplates)->filter(fn ($template) => $template['season'] === $season['key']))
                    <section class="set-block">
                        <div class="set-block__head">
                            <div>
                                <p class="admin-eyebrow">Temporada · {{ $season['product'] === 'tarjeta' ? 'tarjetas' : 'invitaciones' }}</p>
                                <h2 class="set-block__title">{{ $season['name'] }}</h2>
                                <p class="set-block__text">
                                    Encendida y antes de su fecha de término, aparece en el botón de temporadas de la portada y su página
                                    se vende a este precio. No depende de las demás temporadas.
                                </p>
                            </div>
                            <span class="admin-status-badge {{ $statusClass }}">
                                <span class="admin-status-dot"></span>
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="set-row">
                            <label class="set-switch">
                                <input type="hidden" name="seasons[{{ $season['key'] }}][active]" value="0">
                                <input type="checkbox" name="seasons[{{ $season['key'] }}][active]" value="1" class="adm-switch-input"
                                    @checked(old('seasons.'.$season['key'].'.active', $season['active']))>
                                <span class="adm-switch" aria-hidden="true"></span>
                                <span>
                                    <span class="font-medium">Temporada encendida</span>
                                    <span class="block text-sm text-site-muted">Apagada, deja de ofrecerse aunque no haya llegado su fecha.</span>
                                </span>
                            </label>
                        </div>

                        <div class="set-fields">
                            <div>
                                <label for="season-price-{{ $season['key'] }}" class="admin-label">Precio ({{ $currency }})</label>
                                <input id="season-price-{{ $season['key'] }}" type="number" min="0" max="100000" required class="admin-input"
                                    name="seasons[{{ $season['key'] }}][price]" value="{{ old('seasons.'.$season['key'].'.price', $season['price']) }}">
                            </div>
                            <div>
                                <label for="season-promo-{{ $season['key'] }}" class="admin-label">Con descuento</label>
                                <input id="season-promo-{{ $season['key'] }}" type="number" min="0" max="100000" class="admin-input" placeholder="Sin descuento"
                                    name="seasons[{{ $season['key'] }}][promo_price]" value="{{ old('seasons.'.$season['key'].'.promo_price', $season['promo_price']) }}">
                                @error('seasons.'.$season['key'].'.promo_price')
                                    <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="set-fields__wide">
                                <label for="season-ends-{{ $season['key'] }}" class="admin-label">Hasta cuándo se vende</label>
                                <input id="season-ends-{{ $season['key'] }}" type="datetime-local" class="admin-input"
                                    name="seasons[{{ $season['key'] }}][ends_at]" value="{{ old('seasons.'.$season['key'].'.ends_at', $forInput($season['ends_at'])) }}">
                                @error('seasons.'.$season['key'].'.ends_at')
                                    <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        @if($seasonTemplates->isNotEmpty())
                            <div class="set-sub">
                                <p class="set-sub__title">Diseños de esta temporada</p>
                                <p class="set-block__text">Los apagados dejan de mostrarse en el sitio; las invitaciones ya creadas siguen igual.</p>
                                <div class="set-designs">
                                    @foreach($seasonTemplates as $key => $template)
                                        <label class="set-switch set-switch--row">
                                            <input type="checkbox" name="templates[]" value="{{ $key }}" class="adm-switch-input" @checked($enabledTemplates->contains($key))>
                                            <span class="adm-switch" aria-hidden="true"></span>
                                            <span class="min-w-0">
                                                <span class="font-medium">{{ $template['label'] }}</span>
                                                <span class="block text-sm text-site-muted">{{ $template['tagline'] }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>

            {{-- ══ Hazlo tú: planes mensuales ══ --}}
            <div id="panel-hazlo" role="tabpanel" aria-labelledby="ajustes-hazlo" x-show="tab === 'hazlo'" @if($startTab !== 'hazlo') x-cloak @endif>
                <section class="set-block">
                    <div class="set-block__head">
                        <div>
                            <h2 class="set-block__title">Planes de Hazlo tú</h2>
                            <p class="set-block__text">
                                La suscripción mensual para quien arma sus propias invitaciones. El precio y el descuento se ven en
                                la página Hazlo tú; el cupo es cuántas invitaciones (y accesos de cliente) puede crear por mes.
                            </p>
                        </div>
                        <a href="{{ route('diy') }}#planes" target="_blank" rel="noopener" class="admin-link-button">
                            Ver la página
                            <x-phosphor-arrow-square-out aria-hidden="true" />
                        </a>
                    </div>

                    <div class="set-list set-list--plans">
                        <div class="set-list__head" aria-hidden="true">
                            <span>Plan</span><span>Al mes ({{ $currency }})</span><span>Con descuento</span><span>Invitaciones/mes</span>
                        </div>
                        @foreach($settings['reseller_plans'] as $plan)
                            <div class="set-list__row">
                                <div class="min-w-0">
                                    <p class="font-medium">{{ $plan['name'] }}</p>
                                    <p class="text-sm text-site-muted">{{ $plan['summary'] }}</p>
                                </div>
                                <div>
                                    <label for="plan-precio-{{ $plan['key'] }}" class="admin-label set-list__label">Al mes ({{ $currency }})</label>
                                    <input id="plan-precio-{{ $plan['key'] }}" type="number" min="0" max="100000" required class="admin-input"
                                        name="reseller_plans[{{ $plan['key'] }}][price]" value="{{ old('reseller_plans.'.$plan['key'].'.price', $plan['price']) }}">
                                </div>
                                <div>
                                    <label for="plan-promo-{{ $plan['key'] }}" class="admin-label set-list__label">Con descuento</label>
                                    <input id="plan-promo-{{ $plan['key'] }}" type="number" min="0" max="100000" class="admin-input" placeholder="Sin descuento"
                                        name="reseller_plans[{{ $plan['key'] }}][promo_price]" value="{{ old('reseller_plans.'.$plan['key'].'.promo_price', $plan['promo_price']) }}">
                                    @error('reseller_plans.'.$plan['key'].'.promo_price')
                                        <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="plan-cupo-{{ $plan['key'] }}" class="admin-label set-list__label">Invitaciones/mes</label>
                                    <input id="plan-cupo-{{ $plan['key'] }}" type="number" min="1" max="10000" class="admin-input" placeholder="Sin tope"
                                        name="reseller_plans[{{ $plan['key'] }}][quota_per_month]" value="{{ old('reseller_plans.'.$plan['key'].'.quota_per_month', $plan['quota_per_month']) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            {{-- Barra fija: se guarda todo junto, esté en la pestaña que esté --}}
            <div class="set-savebar">
                <p class="text-sm text-site-muted">Se guardan las tres pestañas juntas y se ve al instante en el sitio.</p>
                <button type="submit" class="admin-primary-button">
                    <x-phosphor-check-bold aria-hidden="true" />
                    Guardar ajustes
                </button>
            </div>
        </form>
    </div>
@endsection
