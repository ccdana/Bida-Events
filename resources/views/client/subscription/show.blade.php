@extends('layouts.client')

@section('title', 'Mi plan')

{{--
    Elegir el plan de /precios y pagarlo en línea; con tarjeta, administrar la suscripción que se renueva sola.
    Plan, forma de pago y meses son filas con filete y un punto dorado en la elegida (como los precios del sitio), y los
    meses, texto con filete dorado debajo. Estilos: home.css («Elegir»).
--}}
@section('content')
    @php($methodLabels = [
        'card' => ['credit-card', 'Tarjeta', 'Dólares, desde cualquier país. Se renueva sola y la cancelas cuando quieras.'],
        'qr' => ['qr-code', 'QR en Bolivia', 'Bolivianos: app de cualquier banco, Tigo Money o tarjeta boliviana. No se renueva sola.'],
    ])

    {{-- Centrado: las opciones a la izquierda y, en pantallas anchas, un resumen fijo con el total y el botón a la derecha --}}
    <div class="mx-auto grid w-full max-w-5xl gap-5">
        {{-- Quien todavía no tiene plan está en el paso 3: pagar para poder crear su invitación --}}
        @unless($user->isReseller())
            <x-site.journey :current="3" class="site-enter lg:max-w-xl" />
        @endunless

        <header class="site-enter">
            <h1 class="site-display site-display--md">{{ $user->isReseller() ? 'Mi plan' : 'Elige tu plan' }}</h1>
        </header>

        @if(session('success'))
            <p class="site-enter flex items-start gap-2 border-l-2 border-site-accent py-1 pl-3 text-sm" role="status">
                <x-phosphor-check-circle-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                {{ session('success') }}
            </p>
        @endif

        {{-- Ya paga con tarjeta: cambios de plan, de tarjeta y cancelación, en el portal de la pasarela --}}
        @if($recurring)
            <section class="site-enter site-rows w-full lg:max-w-xl" style="--enter-index: 1" aria-labelledby="suscripcion-tarjeta">
                <div class="site-row">
                    <div class="site-row__summary">
                        <x-phosphor-credit-card-light class="site-row__icon" aria-hidden="true" />
                        <span class="site-row__main">
                            <span id="suscripcion-tarjeta" class="site-row__label">Suscripción con tarjeta</span>
                            <span class="site-row__value">
                                {{ $user->planConfig()['name'] ?? $user->reseller_plan }}
                                @if($user->subscription_renews_at)
                                    <span class="site-row__sub">
                                        @if($user->subscription_status === 'past_due')
                                            <x-phosphor-warning-circle-light aria-hidden="true" /> no pudimos cobrar el {{ $user->subscription_renews_at->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F')) }}: revisa tu tarjeta
                                        @elseif($user->cancel_at_period_end)
                                            <x-phosphor-clock-light aria-hidden="true" /> cancelada: activa hasta el {{ $user->subscription_renews_at->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}
                                        @else
                                            <x-phosphor-arrow-clockwise-light aria-hidden="true" /> se renueva el {{ $user->subscription_renews_at->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}
                                        @endif
                                    </span>
                                @endif
                            </span>
                        </span>
                        @if($canOpenPortal)
                            <a href="{{ route('client.subscription.portal') }}" class="site-row__change" title="Cambia de plan o de tarjeta, descarga tus facturas o cancela, en el portal seguro de nuestro procesador de pagos">
                                <span>Administrar</span>
                                <x-phosphor-arrow-up-right-light aria-hidden="true" />
                            </a>
                        @endif
                    </div>
                </div>
                @error('portal')
                    <p class="py-2 text-sm text-site-danger">{{ $message }}</p>
                @enderror
                @if($canOpenPortal)
                    <p class="flex items-start gap-2 py-3 text-sm text-site-muted">
                        <x-phosphor-info-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                        @if(config('payments.lemonsqueezy.test_mode'))
                            Con la tienda en modo de prueba, Lemon Squeezy puede pedir iniciar sesión con la cuenta de la tienda. Tus movimientos están en «Mi cuenta».
                        @else
                            Si Lemon Squeezy te pide entrar, usa el correo con el que pagaste: te manda un código.
                        @endif
                    </p>
                @endif
            </section>
        @endif

        @if($needsEmail)
            <p class="site-enter flex items-start gap-2 border-l-2 border-site-accent py-1 pl-3 text-sm lg:max-w-xl" role="status" style="--enter-index: 1">
                <x-phosphor-envelope-simple-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                <span>
                    Para pagar necesitas un correo confirmado: ahí llega tu recibo.
                    <a href="{{ route('client.account') }}" class="font-medium underline underline-offset-4">{{ filled($user->email) ? 'Confirmarlo' : 'Agregarlo' }}</a>
                </span>
            </p>
        @endif

        @if(! $user->renewsAutomatically())
            @if($methods === [])
                <p class="site-enter flex items-start gap-2 text-site-muted lg:max-w-xl" style="--enter-index: 2">
                    <x-phosphor-warning-circle-light class="mt-0.5 size-5 shrink-0 text-site-accent" aria-hidden="true" />
                    <span>
                        El pago en línea no está disponible ahora.
                        <a href="{{ lroute('pricing') }}#planes" class="font-medium text-site-ink underline underline-offset-4">Escríbenos</a> y lo activamos contigo.
                    </span>
                </p>
            @else
                {{-- El total cambia con el plan, la forma de pago (su moneda) y los meses (con tarjeta, los ciclos que tiene la pasarela) --}}
                <form method="POST" action="{{ route('client.subscription.store') }}" class="site-enter grid items-start gap-x-14 gap-y-6 lg:grid-cols-[minmax(0,1fr)_20rem]" style="--enter-index: 2"
                    x-data="{
                        sending: false,
                        plan: @js($selected),
                        method: @js(old('method', array_key_first($methods))),
                        months: {{ (int) old('months', $selectedMonths) }},
                        pricing: @js($pricing),
                        get current() { return this.pricing[this.method] ?? null },
                        // Los meses que se pueden pagar con esta forma de pago y este plan (con tarjeta: cada cuánto se renueva)
                        get cycles() { return this.current?.cycles?.[this.plan] ?? [1] },
                        get total() { return this.current?.prices?.[this.plan]?.[this.months] ?? null },
                        money(value) {
                            const number = Number(value)
                            const digits = Number.isInteger(number) ? 0 : 2
                            return number.toLocaleString('es-BO', { minimumFractionDigits: digits, maximumFractionDigits: digits })
                        },
                    }" x-effect="if (! cycles.includes(months)) months = 1" @submit="sending = true">
                    @csrf

                    <div class="grid min-w-0 gap-6">
                    <fieldset>
                        <legend class="site-legend">Plan</legend>
                        <div class="site-choices">
                            @foreach($plans as $key => $plan)
                                <label class="site-choice">
                                    <input type="radio" name="plan" value="{{ $key }}" x-model="plan" class="sr-only" @checked($selected === $key) required>
                                    <span class="site-choice__dot" aria-hidden="true"></span>
                                    <span class="site-choice__body">
                                        <span class="site-choice__head">
                                            <span class="site-choice__name">{{ $plan['name'] }}</span>
                                            <span class="tabular-nums">
                                                @if($plan['old_price'])
                                                    <del class="mr-1 text-sm text-site-muted">{{ \App\Support\Money::format($plan['old_price']) }}</del>
                                                @endif
                                                <span class="font-semibold">{{ \App\Support\Money::format($plan['final_price']) }}</span>
                                                <span class="text-sm text-site-muted">al mes</span>
                                            </span>
                                        </span>
                                        <span class="site-choice__note">
                                            {{ $plan['quota_per_month'] === null ? 'Invitaciones sin tope' : ($plan['quota_per_month'] === 1 ? '1 invitación al mes' : $plan['quota_per_month'].' invitaciones al mes') }}
                                            @if($user->reseller_plan === $key && $user->isReseller())
                                                <span class="site-choice__tag"><x-phosphor-seal-check-light aria-hidden="true" /> Tu plan</span>
                                            @endif
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('plan')
                            <p class="mt-2 text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <fieldset>
                        <legend class="site-legend">Cómo pagar</legend>
                        <div class="site-choices">
                            @foreach($methods as $method => $provider)
                                <label class="site-choice">
                                    <input type="radio" name="method" value="{{ $method }}" x-model="method" class="sr-only" @checked(old('method', array_key_first($methods)) === $method) required>
                                    <span class="site-choice__dot" aria-hidden="true"></span>
                                    <span class="site-choice__body">
                                        <span class="site-choice__head">
                                            <span class="site-choice__name flex items-center gap-2">
                                                <x-dynamic-component :component="'phosphor-'.($methodLabels[$method][0] ?? 'credit-card').'-light'" class="size-5 text-site-accent" aria-hidden="true" />
                                                {{ $methodLabels[$method][1] ?? $method }}
                                            </span>
                                        </span>
                                        <span class="site-choice__note">{{ $methodLabels[$method][2] ?? '' }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('method')
                            <p class="mt-2 text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    {{-- Cada cuánto paga: 1, 3 o 12 meses, con su descuento (con tarjeta, solo los ciclos que tiene la pasarela) --}}
                    @if(collect($pricing)->contains(fn ($method) => collect($method['cycles'])->contains(fn ($months) => count($months) > 1)))
                        <fieldset x-show="cycles.length > 1" x-cloak>
                            <legend class="site-legend" x-text="current?.recurring ? 'Se renueva cada' : 'Meses'">Meses</legend>
                            <div class="site-cycle">
                                @foreach($prepay as $count => $discount)
                                    <label x-show="cycles.includes({{ $count }})" class="site-cycle__option" :class="{ 'is-active': months === {{ $count }} }">
                                        <input type="radio" name="months" value="{{ $count }}" x-model.number="months" class="sr-only" @checked((int) old('months', $selectedMonths) === $count)>
                                        <span class="site-cycle__name">{{ $count === 1 ? '1 mes' : "{$count} meses" }}</span>
                                        @if($discount > 0)
                                            <span class="site-cycle__off">{{ $discount }} % menos</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                            @error('months')
                                <p class="mt-2 text-sm text-site-danger">{{ $message }}</p>
                            @enderror
                        </fieldset>
                    @endif

                    </div>

                    {{-- El resumen: el total, el botón y lo que hay que saber; queda a la vista al bajar --}}
                    <aside class="grid content-start gap-3 lg:sticky lg:top-24 lg:border-l lg:border-site-line lg:pl-8" aria-label="Resumen del pago">
                        <p class="site-total" x-show="total" x-cloak aria-live="polite">
                            <span class="text-site-muted">Total</span>
                            <span class="site-total__amount tabular-nums" x-text="current.symbol + ' ' + money(total)"></span>
                            <span class="text-sm text-site-muted" x-text="current.recurring ? (months === 1 ? 'al mes, se renueva sola' : 'cada ' + months + ' meses, se renueva sola') : (months === 1 ? 'por 1 mes' : 'por ' + months + ' meses')"></span>
                        </p>
                        <div>
                            <button type="submit" class="site-btn site-btn--lg" :disabled="sending" @disabled($needsEmail)>
                                <span x-text="sending ? 'Abriendo el pago…' : 'Continuar al pago'">Continuar al pago</span>
                                <x-phosphor-arrow-right class="site-btn__arrow" x-show="!sending" aria-hidden="true" />
                            </button>
                        </div>
                        <p class="flex items-start gap-2 text-sm text-site-muted">
                            <x-phosphor-info-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                            <span>
                                Se activa al confirmarse el cobro. Con tarjeta internacional se suman los impuestos de tu país.
                                Al continuar aceptas los <a href="{{ lroute('legal', 'terminos') }}" class="underline underline-offset-4">términos</a>.
                            </span>
                        </p>
                    </aside>
                </form>
            @endif
        @endif
    </div>
@endsection
