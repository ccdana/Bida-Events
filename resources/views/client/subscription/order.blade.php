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
                    Te enviamos el recibo a {{ $user->email }}.
                </p>
            </header>
            <div class="site-enter flex flex-wrap gap-3" style="--enter-index: 1">
                @if($user->can('create', \App\Models\Invitation::class))
                    <a href="{{ route('client.invitations.create') }}" class="admin-primary-button min-h-11">Crear una invitación</a>
                @endif
                <a href="{{ route('client.dashboard') }}" class="admin-link-button min-h-11">Ir a mi panel</a>
            </div>
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
                    {{ $order->status === \App\Models\PaymentOrder::REFUNDED ? 'Este pago se devolvió.' : 'No se cobró nada. Puedes intentarlo otra vez, con la misma tarjeta o con otra forma de pago.' }}
                </p>
            </header>
            <div class="site-enter" style="--enter-index: 1">
                <a href="{{ route('client.subscription', ['plan' => $order->plan]) }}" class="admin-primary-button min-h-11">Intentar de nuevo</a>
            </div>
        @endif
    </div>
@endsection
