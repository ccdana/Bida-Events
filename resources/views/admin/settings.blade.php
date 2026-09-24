@extends('layouts.admin')

@section('title', 'Ajustes')

@php
    // Los <input type="datetime-local"> quieren «2026-09-21T23:59»
    $forInput = fn (?string $value) => $value ? str_replace(' ', 'T', substr($value, 0, 16)) : '';
@endphp

@section('content')
    <div class="grid gap-10">
        <header class="site-enter">
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Ajustes</h1>
            <p class="mt-2 max-w-[60ch] text-site-muted">
                Los precios que ve la gente en la página, hasta cuándo dura cada promoción y qué temporadas
                (Día del Amor, Halloween…) se están ofreciendo. Se aplica al instante, sin tocar el código.
            </p>
        </header>

        @if($errors->any())
            <div class="site-enter rounded-[12px] border border-site-danger/40 bg-site-danger/10 p-4" style="--enter-index: 1">
                <p class="font-medium text-site-danger">No se guardaron los cambios</p>
                <ul class="mt-2 list-disc pl-5 text-sm text-site-danger">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-10">
            @csrf
            @method('PUT')

            {{-- ── Promoción de los paquetes ───────────────────────────────── --}}
            <section class="site-enter admin-card p-6 lg:p-8" style="--enter-index: 2">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold tracking-tight">Promoción</h2>
                        <p class="mt-1 max-w-[52ch] text-sm text-site-muted">
                            Con la promoción encendida, la página muestra el precio normal tachado y cobra el
                            precio con descuento. Al pasar la fecha de término vuelve sola a los precios normales.
                        </p>
                    </div>
                    <span class="admin-status-badge {{ $promoActive ? 'is-success' : 'is-primary' }}">
                        <span class="admin-status-dot"></span>
                        {{ $promoActive ? 'Activa ahora' : 'Sin promoción' }}
                    </span>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-center gap-1 rounded-[12px] border border-site-line p-4">
                        <input type="checkbox" name="promo_active" value="1" class="adm-switch-input"
                            @checked(old('promo_active', $settings['promo']['active']))>
                        <span class="adm-switch" aria-hidden="true"></span>
                        <span>
                            <span class="font-medium">Promoción encendida</span>
                            <span class="mt-0.5 block text-sm text-site-muted">{{ $settings['promo']['label'] }}</span>
                        </span>
                    </label>
                    <div>
                        <label for="promo-ends" class="admin-label">Hasta cuándo dura <span class="font-normal text-site-muted">(opcional)</span></label>
                        <input id="promo-ends" type="datetime-local" name="promo_ends_at" class="admin-input"
                            value="{{ old('promo_ends_at', $forInput($settings['promo']['ends_at'])) }}">
                        <p class="mt-1.5 text-xs text-site-muted">Vacío: la promoción dura hasta que la apagues.</p>
                    </div>
                </div>
            </section>

            {{-- ── Precios de los tres paquetes ────────────────────────────── --}}
            <section class="site-enter admin-card p-6 lg:p-8" style="--enter-index: 3">
                <h2 class="text-xl font-semibold tracking-tight">Precios de los paquetes</h2>
                <p class="mt-1 max-w-[52ch] text-sm text-site-muted">
                    En bolivianos. El precio con descuento es el que se cobra mientras la promoción esté activa.
                </p>

                <div class="mt-6 grid gap-4">
                    @foreach($settings['packages'] as $package)
                        <div class="grid gap-4 rounded-[12px] border border-site-line p-4 sm:grid-cols-[minmax(0,1fr)_9rem_9rem] sm:items-end">
                            <div class="min-w-0">
                                <p class="font-medium">{{ $package['name'] }}</p>
                                <p class="mt-0.5 text-sm text-site-muted">{{ $package['summary'] }}</p>
                            </div>
                            <div>
                                <label for="precio-{{ $package['key'] }}" class="admin-label">Precio normal</label>
                                <input id="precio-{{ $package['key'] }}" type="number" min="0" max="100000" required class="admin-input"
                                    name="packages[{{ $package['key'] }}][price]"
                                    value="{{ old('packages.'.$package['key'].'.price', $package['price']) }}">
                            </div>
                            <div>
                                <label for="promo-{{ $package['key'] }}" class="admin-label">Con descuento</label>
                                <input id="promo-{{ $package['key'] }}" type="number" min="0" max="100000" class="admin-input"
                                    name="packages[{{ $package['key'] }}][promo_price]"
                                    value="{{ old('packages.'.$package['key'].'.promo_price', $package['promo_price']) }}">
                                @error('packages.'.$package['key'].'.promo_price')
                                    <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ── Planes de revendedor ────────────────────────────────────── --}}
            <section class="site-enter admin-card p-6 lg:p-8" style="--enter-index: 4">
                <h2 class="text-xl font-semibold tracking-tight">Planes de revendedor</h2>
                <p class="mt-1 max-w-[56ch] text-sm text-site-muted">
                    La suscripción mensual de fotógrafos y planners. El precio se ve en la página para
                    profesionales y el cupo es cuántas invitaciones pueden crear por mes (vacío: sin tope).
                </p>

                <div class="mt-6 grid gap-4">
                    @foreach($settings['reseller_plans'] as $plan)
                        <div class="grid gap-4 rounded-[12px] border border-site-line p-4 sm:grid-cols-[minmax(0,1fr)_9rem_9rem] sm:items-end">
                            <div class="min-w-0">
                                <p class="font-medium">{{ $plan['name'] }}</p>
                                <p class="mt-0.5 text-sm text-site-muted">{{ $plan['summary'] }}</p>
                            </div>
                            <div>
                                <label for="plan-precio-{{ $plan['key'] }}" class="admin-label">Precio al mes</label>
                                <input id="plan-precio-{{ $plan['key'] }}" type="number" min="0" max="100000" required class="admin-input"
                                    name="reseller_plans[{{ $plan['key'] }}][price]"
                                    value="{{ old('reseller_plans.'.$plan['key'].'.price', $plan['price']) }}">
                            </div>
                            <div>
                                <label for="plan-cupo-{{ $plan['key'] }}" class="admin-label">Invitaciones/mes</label>
                                <input id="plan-cupo-{{ $plan['key'] }}" type="number" min="1" max="10000" class="admin-input" placeholder="Sin tope"
                                    name="reseller_plans[{{ $plan['key'] }}][quota_per_month]"
                                    value="{{ old('reseller_plans.'.$plan['key'].'.quota_per_month', $plan['quota_per_month']) }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ── Temporadas: cada una se enciende, se apaga y se ajusta por separado ─────── --}}
            @php
                $enabledTemplates = collect(old('templates', collect($seasonalTemplates)->filter(fn ($t) => $t['enabled'])->keys()->all()));
                $statusLabels = [
                    'selling' => ['En el sitio', 'is-success'],
                    'off' => ['Apagada', 'is-primary'],
                    'ended' => ['Terminada', 'is-primary'],
                    'no_designs' => ['Sin diseños activos', 'is-primary'],
                ];
            @endphp
            @foreach($settings['seasons'] as $season)
                @php([$statusLabel, $statusClass] = $statusLabels[$seasonStatuses[$season['key']] ?? 'off'])
                <section class="site-enter admin-card p-6 lg:p-8" style="--enter-index: 4">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="admin-eyebrow">Temporada · {{ $season['product'] === 'tarjeta' ? 'tarjetas' : 'invitaciones' }}</p>
                            <h2 class="text-xl font-semibold tracking-tight">{{ $season['name'] }}</h2>
                            <p class="mt-1 max-w-[56ch] text-sm text-site-muted">
                                Mientras esté encendida y antes de su fecha de término, tiene su botón en la portada y su
                                página se vende a este precio. No depende de las demás temporadas.
                            </p>
                        </div>
                        <span class="admin-status-badge {{ $statusClass }}">
                            <span class="admin-status-dot"></span>
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <label class="flex cursor-pointer items-center gap-1 rounded-[12px] border border-site-line p-4 lg:col-span-4">
                            <input type="hidden" name="seasons[{{ $season['key'] }}][active]" value="0">
                            <input type="checkbox" name="seasons[{{ $season['key'] }}][active]" value="1" class="adm-switch-input"
                                @checked(old('seasons.'.$season['key'].'.active', $season['active']))>
                            <span class="adm-switch" aria-hidden="true"></span>
                            <span>
                                <span class="font-medium">Temporada encendida</span>
                                <span class="mt-0.5 block text-sm text-site-muted">Apagada, deja de ofrecerse en el sitio aunque todavía no haya llegado su fecha.</span>
                            </span>
                        </label>
                        <div>
                            <label for="season-price-{{ $season['key'] }}" class="admin-label">Precio normal</label>
                            <input id="season-price-{{ $season['key'] }}" type="number" min="0" max="100000" required class="admin-input"
                                name="seasons[{{ $season['key'] }}][price]" value="{{ old('seasons.'.$season['key'].'.price', $season['price']) }}">
                        </div>
                        <div>
                            <label for="season-promo-{{ $season['key'] }}" class="admin-label">Con descuento</label>
                            <input id="season-promo-{{ $season['key'] }}" type="number" min="0" max="100000" class="admin-input"
                                name="seasons[{{ $season['key'] }}][promo_price]" value="{{ old('seasons.'.$season['key'].'.promo_price', $season['promo_price']) }}">
                            @error('seasons.'.$season['key'].'.promo_price')
                                <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="season-ends-{{ $season['key'] }}" class="admin-label">Hasta cuándo se vende</label>
                            <input id="season-ends-{{ $season['key'] }}" type="datetime-local" class="admin-input"
                                name="seasons[{{ $season['key'] }}][ends_at]"
                                value="{{ old('seasons.'.$season['key'].'.ends_at', $forInput($season['ends_at'])) }}">
                            @error('seasons.'.$season['key'].'.ends_at')
                                <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @php($seasonTemplates = collect($seasonalTemplates)->filter(fn ($template) => $template['season'] === $season['key']))
                    @if($seasonTemplates->isNotEmpty())
                        <div class="mt-8 border-t border-site-line pt-6">
                            <h3 class="text-lg font-medium">Diseños de esta temporada</h3>
                            <p class="mt-1 max-w-[52ch] text-sm text-site-muted">
                                Los apagados dejan de mostrarse en el sitio. Las invitaciones ya creadas con ese diseño siguen funcionando igual.
                            </p>

                            <div class="mt-4 grid gap-3 md:grid-cols-2">
                                @foreach($seasonTemplates as $key => $template)
                                    <label class="flex cursor-pointer items-center gap-1 rounded-[12px] border border-site-line p-4">
                                        <input type="checkbox" name="templates[]" value="{{ $key }}" class="adm-switch-input" @checked($enabledTemplates->contains($key))>
                                        <span class="adm-switch" aria-hidden="true"></span>
                                        <span class="min-w-0">
                                            <span class="font-medium">{{ $template['label'] }}</span>
                                            <span class="mt-0.5 block text-sm text-site-muted">{{ $template['tagline'] }}</span>
                                            @if($template['event'] !== $template['label'])
                                                <span class="mt-1 block text-xs text-site-muted">{{ $template['event'] }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            @endforeach

            <div class="site-enter flex flex-wrap items-center gap-3" style="--enter-index: 5">
                <button type="submit" class="admin-primary-button">
                    <x-phosphor-check-bold aria-hidden="true" />
                    Guardar ajustes
                </button>
                <p class="text-sm text-site-muted">Los cambios se ven de inmediato en la página pública.</p>
            </div>
        </form>
    </div>
@endsection
