@extends('layouts.client')

@section('title', 'Tu pago')

{{--
    A dónde vuelve el comprador desde la pasarela. Volver no prueba que se pagó: la página espera el
    aviso de la pasarela consultando cada pocos segundos, y al llegar se recarga con el plan activo.
    Cada estado es un título corto con su ícono suelto, el dato que importa y, si hace falta, un botón.
--}}
@section('content')
    <div class="grid max-w-xl gap-5"
        x-data="{
            status: @js($order->status),
            tries: 0,
            init() { if (this.status === 'pending') this.poll() },
            poll() {
                setTimeout(async () => {
                    this.tries++
                    try {
                        const response = await fetch(@js(route('client.subscription.order', $order->public_id)), { headers: { Accept: 'application/json' } })
                        this.status = (await response.json()).status
                    } catch (error) {}
                    if (this.status !== 'pending') return window.location.reload()
                    if (this.tries < 60) this.poll()
                }, 3000)
            },
        }">
        <x-site.journey :current="$order->isPaid() ? 4 : 3" class="site-enter" />

        @if($order->isPaid())
            <header class="site-enter">
                <x-phosphor-check-circle-light class="size-9 text-site-accent" aria-hidden="true" />
                <h1 class="site-display site-display--md mt-2">Tu plan {{ $planName }} está activo</h1>
                <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-site-muted">
                    @if($user->subscription_renews_at)
                        <span class="inline-flex items-center gap-1.5"><x-phosphor-calendar-check-light class="size-4 text-site-accent" aria-hidden="true" /> Hasta el {{ $user->subscription_renews_at->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}</span>
                    @endif
                    @if($order->provider === 'lemonsqueezy')
                        <span class="inline-flex items-center gap-1.5"><x-phosphor-envelope-simple-light class="size-4 text-site-accent" aria-hidden="true" /> Recibo en {{ $user->email }}</span>
                    @endif
                </p>
            </header>
            <div class="site-enter flex flex-wrap gap-3" style="--enter-index: 1">
                @if($user->can('create', \App\Models\Invitation::class))
                    <a href="{{ route('client.invitations.create') }}" class="site-btn site-btn--lg">
                        Crear mi primera invitación
                        <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                    </a>
                @endif
                <a href="{{ route('client.dashboard') }}" class="site-help">Ir a mi panel</a>
            </div>
        @elseif($order->isPending() && ! empty($order->meta['qr_image']))
            {{-- QR de Bolivia: se escanea desde la app de cualquier banco; Tigo Money y tarjeta, en la página de la pasarela --}}
            <header class="site-enter">
                <h1 class="site-display site-display--md">Escanea el QR para pagar</h1>
                <p class="mt-2 text-site-muted">Con la app de tu banco. Tu plan se activa solo al confirmarse el pago.</p>
            </header>

            <section class="site-enter flex flex-col gap-5 border-y border-site-line py-5 sm:flex-row sm:items-center" style="--enter-index: 1" aria-label="QR de pago">
                <img src="{{ $order->meta['qr_image'] }}" alt="QR para pagar {{ \App\Support\Money::format($order->amount, $order->currency) }}"
                    width="200" height="200" class="size-[200px] shrink-0 rounded-[12px] bg-white p-2">
                <div class="grid gap-2">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums">{{ \App\Support\Money::format($order->amount, $order->currency) }}</p>
                    <p class="text-site-muted">
                        Plan {{ $planName }} · {{ $order->months === 1 ? '1 mes' : "{$order->months} meses" }}
                        @if($order->expires_at)
                            <span class="mt-1 flex items-center gap-1.5 text-sm"><x-phosphor-clock-light class="size-4 text-site-accent" aria-hidden="true" /> Vale hasta el {{ $order->expires_at->timezone(config('app.timezone'))->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F')) }}</span>
                        @endif
                    </p>
                    <p class="flex items-center gap-1.5 text-sm text-site-muted" title="El código de tu compra">
                        <x-phosphor-hash-light class="size-4 text-site-accent" aria-hidden="true" />
                        <span class="font-mono font-medium text-site-ink">{{ $order->reference_code }}</span>
                    </p>
                    @if(! empty($order->meta['payment_page_url']))
                        <a href="{{ $order->meta['payment_page_url'] }}" class="site-help"><x-phosphor-arrow-up-right-light aria-hidden="true" /> Tigo Money o tarjeta</a>
                    @endif
                </div>
            </section>

            <p class="site-enter flex items-center gap-2 text-sm text-site-muted" style="--enter-index: 2" x-show="tries < 60" aria-live="polite">
                <span class="size-2 animate-pulse rounded-full bg-site-accent" aria-hidden="true"></span>
                Esperando la confirmación…
            </p>
            <p class="site-enter flex items-start gap-2 text-sm text-site-muted" style="--enter-index: 2" x-show="tries >= 60" x-cloak>
                <x-phosphor-clock-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                <span>¿Ya pagaste? Puede tardar unos minutos; tu plan se activa solo. Si en una hora no lo ves, escríbenos con el código {{ $order->reference_code }}.</span>
            </p>
        @elseif($order->isPending())
            <header class="site-enter">
                <x-phosphor-hourglass-medium-light class="size-9 text-site-accent" aria-hidden="true" />
                <h1 class="site-display site-display--md mt-2">Confirmando tu pago</h1>
                <p class="mt-2 text-site-muted" x-show="tries < 60">Suele tardar unos segundos. Puedes cerrar esta página: tu plan se activa igual.</p>
                <p class="mt-2 text-site-muted" x-show="tries >= 60" x-cloak>
                    Está tardando más de lo normal. Si ya pagaste, tu plan se activará solo; si en una hora no lo ves, escríbenos con el código {{ $order->reference_code }}.
                </p>
            </header>
            <p class="site-enter flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-site-muted" style="--enter-index: 1">
                <span class="inline-flex items-center gap-1.5"><x-phosphor-hash-light class="size-4 text-site-accent" aria-hidden="true" /> <span class="font-mono font-medium text-site-ink">{{ $order->reference_code }}</span></span>
                <span>Plan {{ $planName }} · {{ \App\Support\Money::format($order->amount, $order->currency) }}</span>
            </p>
        @else
            <header class="site-enter">
                <x-phosphor-warning-circle-light class="size-9 text-site-accent" aria-hidden="true" />
                <h1 class="site-display site-display--md mt-2">El pago no se completó</h1>
                <p class="mt-2 text-site-muted">
                    {{ match ($order->status) {
                        \App\Models\PaymentOrder::REFUNDED => 'Este pago se devolvió.',
                        \App\Models\PaymentOrder::EXPIRED => 'Este cobro venció sin pagarse. Genera uno nuevo: no se cobró nada.',
                        default => 'No se cobró nada. Puedes intentarlo otra vez, con la misma forma de pago o con otra.',
                    } }}
                </p>
            </header>
            <div class="site-enter" style="--enter-index: 1">
                <a href="{{ route('client.subscription', ['plan' => $order->plan]) }}" class="site-btn site-btn--lg">
                    <x-phosphor-arrow-clockwise-light aria-hidden="true" />
                    Intentar de nuevo
                </a>
            </div>
        @endif
    </div>
@endsection
