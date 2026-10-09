@extends('layouts.client')

@section('title', 'Mi plan')

{{-- Elegir el plan de «Hazlo tú» y pagarlo en línea; con tarjeta, administrar la suscripción que se renueva sola --}}
@section('content')
    @php($methodLabels = [
        'card' => ['Tarjeta de crédito o débito', 'Visa, Mastercard y más, desde cualquier país. Se renueva sola cada mes y la cancelas cuando quieras.'],
        'qr' => ['QR desde tu banco', 'Escaneas el QR con la app de cualquier banco de Bolivia y se activa al instante.'],
    ])

    <div class="grid max-w-3xl gap-8">
        <header class="site-enter">
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">{{ $user->isReseller() ? 'Mi plan' : 'Elige tu plan' }}</h1>
            <p class="mt-2 max-w-[60ch] text-site-muted">
                Con tu plan armas invitaciones con nuestras plantillas cada mes. Tus invitaciones publicadas siguen en línea aunque cambies o dejes el plan.
            </p>
        </header>

        @if(session('success'))
            <p class="site-enter flex items-center gap-2 rounded-[12px] border border-site-line bg-site-surface px-4 py-3 text-sm" role="status">
                <x-phosphor-check-circle class="size-5 shrink-0 text-site-accent" aria-hidden="true" />
                {{ session('success') }}
            </p>
        @endif

        {{-- Ya paga con tarjeta: cambios de plan, de tarjeta y cancelación, en el portal de la pasarela --}}
        @if($recurring)
            <section class="site-enter admin-card" style="--enter-index: 1" aria-labelledby="suscripcion-tarjeta">
                <h2 id="suscripcion-tarjeta" class="text-lg font-semibold tracking-tight">Tu suscripción con tarjeta</h2>
                <p class="mt-2 text-site-muted">
                    Plan <span class="font-medium text-site-ink">{{ $user->planConfig()['name'] ?? $user->reseller_plan }}</span>
                    @if($user->subscription_renews_at)
                        ·
                        @if($user->subscription_status === 'past_due')
                            no pudimos cobrar la renovación del {{ $user->subscription_renews_at->locale('es')->translatedFormat('j \d\e F') }}: revisa tu tarjeta.
                        @elseif($user->cancel_at_period_end)
                            cancelaste la renovación: sigue activo hasta el {{ $user->subscription_renews_at->locale('es')->translatedFormat('j \d\e F \d\e Y') }}.
                        @else
                            se renueva solo el {{ $user->subscription_renews_at->locale('es')->translatedFormat('j \d\e F \d\e Y') }}.
                        @endif
                    @endif
                </p>

                @if($canOpenPortal)
                    <a href="{{ route('client.subscription.portal') }}" class="admin-primary-button mt-5 min-h-11">Administrar mi suscripción</a>
                    <p class="mt-3 text-sm text-site-muted">Cambia de plan o de tarjeta, descarga tus facturas o cancela, en el portal seguro de nuestro procesador de pagos.</p>
                @endif
                @error('portal')
                    <p class="mt-3 text-sm text-site-danger">{{ $message }}</p>
                @enderror
            </section>
        @endif

        @if($needsEmail)
            <p class="site-enter flex items-start gap-2 rounded-[12px] border border-site-line bg-site-tint px-4 py-3 text-sm leading-relaxed" role="status" style="--enter-index: 1">
                <x-phosphor-envelope-simple class="mt-0.5 size-5 shrink-0 text-site-accent" aria-hidden="true" />
                <span>
                    Para pagar en línea necesitas un correo confirmado: ahí te llegan el recibo y los avisos de tu plan.
                    <a href="{{ route('client.account') }}" class="font-medium underline underline-offset-4">{{ filled($user->email) ? 'Confirmar mi correo' : 'Agregar mi correo' }}</a>
                </span>
            </p>
        @endif

        @if(! $user->renewsAutomatically())
            @if($methods === [])
                <p class="site-enter admin-card text-site-muted" style="--enter-index: 2">
                    El pago en línea no está disponible en este momento.
                    <a href="{{ route('diy') }}#planes" class="font-medium text-site-ink underline underline-offset-4">Escríbenos</a> y lo activamos contigo.
                </p>
            @else
                <form method="POST" action="{{ route('client.subscription.store') }}" class="site-enter grid gap-8" style="--enter-index: 2"
                    x-data="{ sending: false }" @submit="sending = true">
                    @csrf

                    <fieldset class="grid gap-3">
                        <legend class="mb-3 text-lg font-semibold tracking-tight">Plan</legend>
                        @foreach($plans as $key => $plan)
                            <label class="flex cursor-pointer items-start gap-4 rounded-[14px] border border-site-line bg-site-surface p-4 transition-colors hover:border-site-ink/30 has-[:checked]:border-site-accent has-[:checked]:bg-site-tint">
                                <input type="radio" name="plan" value="{{ $key }}" class="mt-1 size-4 accent-[var(--site-accent)]" @checked($selected === $key) required>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-baseline justify-between gap-x-4">
                                        <span class="font-semibold">{{ $plan['name'] }}</span>
                                        <span class="tabular-nums">
                                            @if($plan['old_price'])
                                                <del class="mr-1 text-sm text-site-muted">{{ \App\Support\Money::format($plan['old_price']) }}</del>
                                            @endif
                                            <span class="font-semibold">{{ \App\Support\Money::format($plan['final_price']) }}</span>
                                            <span class="text-sm text-site-muted">al mes</span>
                                        </span>
                                    </span>
                                    <span class="mt-1 block text-sm text-site-muted">
                                        {{ $plan['quota_per_month'] === null ? 'Invitaciones sin tope' : $plan['quota_per_month'].' invitaciones al mes' }} · {{ $plan['summary'] ?? '' }}
                                    </span>
                                    @if($user->reseller_plan === $key && $user->isReseller())
                                        <span class="mt-1 block text-xs font-medium text-site-accent">Tu plan actual</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                        @error('plan')
                            <p class="text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <fieldset class="grid gap-3">
                        <legend class="mb-3 text-lg font-semibold tracking-tight">Cómo pagar</legend>
                        @foreach($methods as $method => $provider)
                            <label class="flex cursor-pointer items-start gap-4 rounded-[14px] border border-site-line bg-site-surface p-4 transition-colors hover:border-site-ink/30 has-[:checked]:border-site-accent has-[:checked]:bg-site-tint">
                                <input type="radio" name="method" value="{{ $method }}" class="mt-1 size-4 accent-[var(--site-accent)]" @checked(old('method', array_key_first($methods)) === $method) required>
                                <span>
                                    <span class="font-semibold">{{ $methodLabels[$method][0] ?? $method }}</span>
                                    <span class="mt-1 block text-sm text-site-muted">{{ $methodLabels[$method][1] ?? '' }}</span>
                                </span>
                            </label>
                        @endforeach
                        @error('method')
                            <p class="text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <div class="grid gap-3">
                        <div>
                            <button type="submit" class="admin-primary-button min-h-12 px-6" :disabled="sending" @disabled($needsEmail)>
                                <span x-text="sending ? 'Abriendo el pago…' : 'Continuar al pago'">Continuar al pago</span>
                            </button>
                        </div>
                        <p class="max-w-[60ch] text-sm leading-relaxed text-site-muted">
                            Los precios están en dólares; al pagar con tarjeta se suman los impuestos de tu país. Tu plan se activa en cuanto se confirma el cobro.
                            Al continuar aceptas los <a href="{{ route('legal', 'terminos') }}" class="underline underline-offset-4">términos</a>.
                        </p>
                    </div>
                </form>
            @endif
        @endif
    </div>
@endsection
