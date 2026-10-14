@extends('layouts.client')

@section('title', 'Tu pago')

{{--
    «Hazlo tú»: a dónde vuelve quien pagó su invitación (Client\DiyInvitationController::order). Volver
    no prueba que se pagó: la página espera el aviso de la pasarela consultando cada pocos segundos y,
    al llegar, se recarga con la invitación publicada.
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
                        const response = await fetch(@js(route('client.diy.order', $order->public_id)), { headers: { Accept: 'application/json' } })
                        this.status = (await response.json()).status
                    } catch (error) {}
                    if (this.status !== 'pending') return window.location.reload()
                    if (this.tries < 60) this.poll()
                }, 3000)
            },
        }">
        @if($order->isPaid())
            <header class="site-enter">
                <p class="text-sm text-site-muted">{{ $invitation?->title }}</p>
                <h1 class="mt-1 text-3xl font-semibold tracking-tight md:text-4xl">Listo: tu invitación está publicada con el nivel {{ $tierName }}</h1>
                <p class="mt-2 text-site-muted">
                    Copia el enlace y compártelo con tus invitados.
                    @if($order->provider === 'lemonsqueezy' && $order->user?->email)
                        Te enviamos el recibo a {{ $order->user->email }}.
                    @endif
                </p>
            </header>
            <div class="site-enter flex flex-wrap items-center gap-3" style="--enter-index: 1">
                @if($publicUrl)
                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="admin-primary-button min-h-11">
                        <x-phosphor-arrow-square-out aria-hidden="true" />
                        Abrir mi invitación
                    </a>
                    <x-ui.copy-button :text="$publicUrl" label="Copiar enlace" />
                @endif
                <a href="{{ route('client.dashboard') }}" class="admin-link-button min-h-11">Ir a mi panel</a>
            </div>
        @elseif($order->isPending() && ! empty($order->meta['qr_image']))
            {{-- QR de Bolivia: se escanea desde la app de cualquier banco; Tigo Money y tarjeta, en la página de la pasarela --}}
            <header class="site-enter">
                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Escanea el QR para pagar</h1>
                <p class="mt-2 text-site-muted">
                    Abre la app de tu banco, elige pagar con QR y escanéalo (o guarda la imagen y súbela desde la app).
                    Tu invitación se publica sola cuando se confirma el pago.
                </p>
            </header>

            <section class="site-enter admin-card flex flex-col gap-6 sm:flex-row sm:items-center" style="--enter-index: 1" aria-label="QR de pago">
                <img src="{{ $order->meta['qr_image'] }}" alt="QR para pagar {{ \App\Support\Money::format($order->amount, $order->currency) }}"
                    width="220" height="220" class="size-[220px] shrink-0 rounded-[12px] bg-white p-2">
                <div class="grid gap-2">
                    <p class="text-3xl font-semibold tracking-tight tabular-nums">{{ \App\Support\Money::format($order->amount, $order->currency) }}</p>
                    <p class="text-site-muted">
                        {{ $order->concept() }}
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
                ¿Ya pagaste? La confirmación puede tardar unos minutos; tu invitación se publica sola. Si en una hora no la ves, escríbenos con el código {{ $order->reference_code }}.
            </p>
        @elseif($order->isPending())
            <header class="site-enter">
                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Estamos confirmando tu pago</h1>
                <p class="mt-2 text-site-muted" x-show="tries < 60">
                    Suele tardar unos segundos. Puedes cerrar esta página: tu invitación se publica igual en cuanto llegue la confirmación.
                </p>
                <p class="mt-2 text-site-muted" x-show="tries >= 60" x-cloak>
                    La confirmación está tardando más de lo normal. Si ya pagaste, tu invitación se publicará sola; si en una hora no la ves, escríbenos con el código {{ $order->reference_code }}.
                </p>
            </header>
            <p class="site-enter text-sm text-site-muted" style="--enter-index: 1">
                Código de tu compra: <span class="font-mono font-medium text-site-ink">{{ $order->reference_code }}</span>
                · {{ $order->concept() }} · {{ \App\Support\Money::format($order->amount, $order->currency) }}
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
            @if($invitation)
                <div class="site-enter" style="--enter-index: 1">
                    <a href="{{ route('client.diy.publish', $invitation) }}" class="admin-primary-button min-h-11">Intentar de nuevo</a>
                </div>
            @endif
        @endif
    </div>
@endsection
