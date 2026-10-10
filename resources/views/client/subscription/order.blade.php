@extends('layouts.client')

@section('title', 'Tu pago')

{{--
    A dónde vuelve el comprador desde la pasarela. Volver no prueba que se pagó: la página espera el
    aviso de la pasarela consultando cada pocos segundos, y al llegar se recarga con el plan activo.
--}}
@section('content')
    <div class="grid max-w-2xl gap-6"
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
        @if($order->isPaid())
            <header class="site-enter">
                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Listo: tu plan {{ $planName }} está activo</h1>
                <p class="mt-2 text-site-muted">
                    @if($user->subscription_renews_at)
                        Puedes crear invitaciones hasta el {{ $user->subscription_renews_at->locale('es')->translatedFormat('j \d\e F \d\e Y') }}{{ $user->renewsAutomatically() ? ', y se renueva solo' : '' }}.
                    @endif
                    @if($order->provider === 'lemonsqueezy')
                        Te enviamos el recibo a {{ $user->email }}.
                    @endif
                </p>
            </header>
            <div class="site-enter flex flex-wrap gap-3" style="--enter-index: 1">
                @if($user->can('create', \App\Models\Invitation::class))
                    <a href="{{ route('client.invitations.create') }}" class="admin-primary-button min-h-11">Crear una invitación</a>
                @endif
                <a href="{{ route('client.dashboard') }}" class="admin-link-button min-h-11">Ir a mi panel</a>
            </div>
        @elseif($order->isPending() && ! empty($order->meta['qr_image']))
            {{-- QR de Bolivia: se escanea desde la app de cualquier banco; Tigo Money y tarjeta, en la página de la pasarela --}}
            <header class="site-enter">
                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Escanea el QR para pagar</h1>
                <p class="mt-2 text-site-muted">
                    Abre la app de tu banco, elige pagar con QR y escanéalo (o guarda la imagen y súbela desde la app).
                    Tu plan se activa solo cuando se confirma el pago.
                </p>
            </header>

            <section class="site-enter admin-card flex flex-col gap-6 sm:flex-row sm:items-center" style="--enter-index: 1" aria-label="QR de pago">
                <img src="{{ $order->meta['qr_image'] }}" alt="QR para pagar {{ \App\Support\Money::format($order->amount, $order->currency) }}"
                    width="220" height="220" class="size-[220px] shrink-0 rounded-[12px] bg-white p-2">
                <div class="grid gap-2">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums">{{ \App\Support\Money::format($order->amount, $order->currency) }}</p>
                    <p class="text-site-muted">
                        Plan {{ $planName }} · {{ $order->months === 1 ? '1 mes' : "{$order->months} meses" }}
                        @if($order->expires_at)
                            · vale hasta el {{ $order->expires_at->timezone(config('app.timezone'))->locale('es')->translatedFormat('j \d\e F') }}
                        @endif
                    </p>
                    <p class="text-sm text-site-muted">Código de tu compra: <span class="font-mono font-medium text-site-ink">{{ $order->reference_code }}</span></p>
                    @if(! empty($order->meta['payment_page_url']))
                        <a href="{{ $order->meta['payment_page_url'] }}" class="mt-2 text-sm font-medium underline underline-offset-4">Pagar con Tigo Money o tarjeta</a>
                    @endif
                </div>
            </section>

            <p class="site-enter flex items-center gap-2 text-sm text-site-muted" style="--enter-index: 2" x-show="tries < 60" aria-live="polite">
                <span class="size-2 animate-pulse rounded-full bg-site-accent" aria-hidden="true"></span>
                Esperando la confirmación del pago…
            </p>
            <p class="site-enter text-sm text-site-muted" style="--enter-index: 2" x-show="tries >= 60" x-cloak>
                ¿Ya pagaste? La confirmación puede tardar unos minutos; tu plan se activa solo. Si en una hora no lo ves, escríbenos con el código {{ $order->reference_code }}.
            </p>
        @elseif($order->isPending())
            <header class="site-enter">
                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Estamos confirmando tu pago</h1>
                <p class="mt-2 text-site-muted" x-show="tries < 60">
                    Suele tardar unos segundos. Puedes cerrar esta página: tu plan se activa igual en cuanto llegue la confirmación.
                </p>
                <p class="mt-2 text-site-muted" x-show="tries >= 60" x-cloak>
                    La confirmación está tardando más de lo normal. Si ya pagaste, tu plan se activará solo; si en una hora no lo ves, escríbenos con el código {{ $order->reference_code }}.
                </p>
            </header>
            <p class="site-enter text-sm text-site-muted" style="--enter-index: 1">
                Código de tu compra: <span class="font-mono font-medium text-site-ink">{{ $order->reference_code }}</span>
                · Plan {{ $planName }} · {{ \App\Support\Money::format($order->amount, $order->currency) }}
            </p>
        @else
            <header class="site-enter">
                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">El pago no se completó</h1>
                <p class="mt-2 text-site-muted">
                    {{ match ($order->status) {
                        \App\Models\PaymentOrder::REFUNDED => 'Este pago se devolvió.',
                        \App\Models\PaymentOrder::EXPIRED => 'Este cobro venció sin pagarse. Genera uno nuevo: no se cobró nada.',
                        default => 'No se cobró nada. Puedes intentarlo otra vez, con la misma forma de pago o con otra.',
                    } }}
                </p>
            </header>
            <div class="site-enter" style="--enter-index: 1">
                <a href="{{ route('client.subscription', ['plan' => $order->plan]) }}" class="admin-primary-button min-h-11">Intentar de nuevo</a>
            </div>
        @endif
    </div>
@endsection
