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
                Los precios que ve la gente en la página, hasta cuándo dura cada promoción y qué tarjetas de
                temporada se están ofreciendo. Se aplica al instante, sin tocar el código.
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

            {{-- ── Temporada de tarjetas ───────────────────────────────────── --}}
            <section class="site-enter admin-card p-6 lg:p-8" style="--enter-index: 4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold tracking-tight">Temporada de tarjetas</h2>
                        <p class="mt-1 max-w-[52ch] text-sm text-site-muted">
                            {{ $settings['season']['name'] }}. Su sección aparece en la portada con la cuenta
                            regresiva y desaparece sola cuando termina.
                        </p>
                    </div>
                    <span class="admin-status-badge {{ $seasonActive ? 'is-success' : 'is-primary' }}">
                        <span class="admin-status-dot"></span>
                        {{ $seasonActive ? 'En la portada' : 'Terminada' }}
                    </span>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="season-price" class="admin-label">Precio normal</label>
                        <input id="season-price" type="number" min="0" max="100000" required class="admin-input"
                            name="season_price" value="{{ old('season_price', $settings['season']['price']) }}">
                    </div>
                    <div>
                        <label for="season-promo" class="admin-label">Con descuento</label>
                        <input id="season-promo" type="number" min="0" max="100000" class="admin-input"
                            name="season_promo_price" value="{{ old('season_promo_price', $settings['season']['promo_price']) }}">
                        @error('season_promo_price')
                            <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="season-ends" class="admin-label">Hasta cuándo se venden</label>
                        <input id="season-ends" type="datetime-local" name="season_ends_at" class="admin-input"
                            value="{{ old('season_ends_at', $forInput($settings['season']['ends_at'])) }}">
                        @error('season_ends_at')
                            <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 border-t border-site-line pt-6">
                    <h3 class="text-lg font-medium">Plantillas de temporada</h3>
                    <p class="mt-1 max-w-[52ch] text-sm text-site-muted">
                        Las que estén apagadas dejan de mostrarse en la portada y en las páginas de tarjetas.
                        Las invitaciones ya creadas con esa plantilla siguen funcionando igual.
                    </p>

                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        @foreach($seasonalTemplates as $key => $template)
                            <label class="flex cursor-pointer items-center gap-1 rounded-[12px] border border-site-line p-4">
                                <input type="checkbox" name="templates[]" value="{{ $key }}" class="adm-switch-input"
                                    @checked(collect(old('templates', collect($seasonalTemplates)->filter(fn ($t) => $t['enabled'])->keys()->all()))->contains($key))>
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
            </section>

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
