@extends('layouts.client')

@section('title', 'Publicar mi invitación')

{{--
    «Hazlo tú», antes de publicar (Client\DiyInvitationController::publish): el nivel que piden las
    partes encendidas y las dos salidas, gratis (apaga lo que la gratuita no trae) o con ese nivel.
    También sirve para volver a publicar una oculta y para subir de nivel una ya publicada.
--}}
@section('content')
    @php($isFreeTier = $tier === \App\Support\Packages::FREE)

    <div class="grid max-w-4xl gap-8">
        <header class="site-enter">
            <p class="text-sm text-site-muted">{{ $invitation->title }}</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight md:text-4xl">
                @if($covered)
                    Publícala otra vez
                @elseif($currentTierName)
                    Sube tu invitación a {{ $tierName }}
                @else
                    Publica tu invitación
                @endif
            </h1>
            <p class="mt-2 max-w-[60ch] text-site-muted">
                @if($covered)
                    Tiene el nivel {{ $currentTierName }}: tus invitados verán todo lo que tiene encendido.
                @elseif($isFreeTier)
                    Tu invitación usa solo lo que trae la gratuita: la portada, la cuenta regresiva y la ubicación.
                @else
                    Con lo que encendiste, tu invitación es nivel <strong class="font-semibold text-site-ink">{{ $tierName }}</strong>.
                    Publícala así, o gratis apagando lo que la gratuita no trae.
                @endif
            </p>
        </header>

        @error('publish')
            <p class="site-enter flex items-start gap-2 rounded-[12px] border border-site-danger/40 bg-site-danger/10 px-4 py-3 text-sm text-site-danger" role="alert">
                <x-phosphor-warning-circle class="size-5 shrink-0" aria-hidden="true" />
                {{ $message }}
            </p>
        @enderror

        @if($needsEmail)
            <p class="site-enter flex items-start gap-2 rounded-[12px] border border-site-line bg-site-tint px-4 py-3 text-sm leading-relaxed" role="status">
                <x-phosphor-envelope-simple class="mt-0.5 size-5 shrink-0 text-site-accent" aria-hidden="true" />
                <span>
                    Para publicar necesitas un correo confirmado: ahí te avisamos de las confirmaciones y del vencimiento de tu invitación.
                    <a href="{{ $hasEmail ? route('verification.notice') : route('client.account') }}" class="font-medium underline underline-offset-4">{{ $hasEmail ? 'Confirmar mi correo' : 'Agregar mi correo' }}</a>
                </span>
            </p>
        @endif

        @if($covered)
            {{-- Ya tiene el nivel que piden sus partes: solo hace falta volver a encenderla --}}
            <section class="site-enter admin-card p-6 lg:p-8" style="--enter-index: 1">
                <p class="text-sm text-site-muted">Queda en línea hasta el {{ $paidUntil->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}.</p>
                <form method="POST" action="{{ route('client.diy.publish.store', $invitation) }}" class="mt-5">
                    @csrf
                    <input type="hidden" name="mode" value="covered">
                    <button type="submit" class="admin-primary-button min-h-11" @disabled($needsEmail)>
                        <x-phosphor-paper-plane-tilt aria-hidden="true" />
                        Publicar otra vez
                    </button>
                </form>
            </section>
        @else
            {{-- Lo que hace que no sea gratuita: cada parte con el nivel desde el que viene --}}
            @if($paidParts !== [])
                <section class="site-enter" style="--enter-index: 1" aria-labelledby="partes-nivel">
                    <h2 id="partes-nivel" class="border-b border-site-line pb-3 text-lg font-semibold tracking-tight">Lo que encendiste además de lo gratuito</h2>
                    <ul class="divide-y divide-site-line">
                        @foreach($paidParts as $part)
                            <li class="flex items-baseline justify-between gap-4 py-2.5">
                                <span>{{ $part['label'] }}</span>
                                <span class="text-sm text-site-muted">{{ $part['tierName'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <div @class(['site-enter grid gap-5', 'md:grid-cols-2' => ! $isFreeTier && $canGoFree]) style="--enter-index: 2">
                {{-- Con su nivel: todo lo que armó, tal cual --}}
                @unless($isFreeTier && $canGoFree)
                    <section class="admin-card flex flex-col p-6 lg:p-8" aria-labelledby="opcion-nivel">
                        <p class="text-sm text-site-muted">{{ $currentTierName ? 'Subir de nivel' : 'Tal como la armaste' }}</p>
                        <h2 id="opcion-nivel" class="mt-1 text-2xl font-semibold tracking-tight">{{ $tierName }}</h2>
                        @if($tierFeatures !== [])
                            <ul class="mt-4 grid gap-1.5 text-sm">
                                @foreach($tierFeatures as $feature)
                                    <li class="flex items-start gap-2">
                                        <x-phosphor-check class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <p class="mt-4 text-sm text-site-muted">En línea hasta el {{ $paidUntil->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}, sin la franja de invitación gratuita.</p>

                        {{-- Pago único: el precio del nivel, la diferencia si ya tenía otro o el precio de su temporada --}}
                        <p class="mt-5 flex flex-wrap items-baseline gap-x-2">
                            <span class="text-3xl font-semibold tracking-tight tabular-nums">{{ $dueLabel }}</span>
                            <span class="text-sm text-site-muted">
                                @if($isSeasonPrice)
                                    precio de temporada, con todo
                                @elseif($isUpgrade)
                                    la diferencia con tu nivel {{ $currentTierName }}
                                @else
                                    pago único
                                @endif
                            </span>
                        </p>

                        <div class="mt-auto pt-6">
                            @if($due === 0)
                                {{-- Sin nada que cobrar (por ejemplo, con un descuento nuevo) --}}
                                <form method="POST" action="{{ route('client.diy.checkout', $invitation) }}">
                                    @csrf
                                    <button type="submit" class="admin-primary-button min-h-11" @disabled($needsEmail)>Activar {{ $tierName }}</button>
                                </form>
                            @elseif($payMethods !== [])
                                <form method="POST" action="{{ route('client.diy.checkout', $invitation) }}" class="grid gap-3" x-data="{ sending: false }" @submit="sending = true">
                                    @csrf
                                    <fieldset class="grid gap-2">
                                        <legend class="sr-only">Cómo pagar</legend>
                                        @foreach($payMethods as $method => $option)
                                            <label class="flex cursor-pointer items-start gap-3 rounded-[12px] border border-site-line p-3 text-sm transition-colors hover:border-site-ink/30 has-[:checked]:border-site-accent has-[:checked]:bg-site-tint">
                                                <input type="radio" name="method" value="{{ $method }}" class="mt-0.5 size-4 accent-[var(--site-accent)]" @checked($loop->first) required>
                                                <span class="min-w-0 flex-1">
                                                    <span class="flex flex-wrap items-baseline justify-between gap-x-3">
                                                        <span class="font-medium">{{ $option['label'] }}</span>
                                                        <span class="font-semibold tabular-nums">{{ $option['amount'] }}</span>
                                                    </span>
                                                    <span class="mt-0.5 block text-site-muted">{{ $option['note'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </fieldset>
                                    <button type="submit" class="admin-primary-button min-h-11" :disabled="sending || @js($needsEmail)" @disabled($needsEmail)>
                                        <x-phosphor-lock-simple aria-hidden="true" />
                                        <span x-text="sending ? 'Abriendo el pago…' : @js($currentTierName ? 'Pagar y subir a '.$tierName : 'Pagar y publicar')">{{ $currentTierName ? 'Pagar y subir a '.$tierName : 'Pagar y publicar' }}</span>
                                    </button>
                                    <p class="text-sm text-site-muted">Tu invitación se publica sola en cuanto se confirma el pago.</p>
                                </form>
                            @else
                                {{-- Sin pago en línea encendido: se pide por WhatsApp y el equipo activa el nivel --}}
                                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="admin-primary-button min-h-11">
                                    <x-phosphor-whatsapp-logo aria-hidden="true" />
                                    Pedir {{ $tierName }} por WhatsApp
                                </a>
                                <p class="mt-3 text-sm text-site-muted">Escríbenos y activamos tu invitación con este nivel.</p>
                            @endif
                        </div>
                    </section>
                @endunless

                {{-- Gratis: la portada, la cuenta regresiva y la ubicación, con la marca de Bida Events --}}
                @if($canGoFree)
                    <section class="admin-card flex flex-col p-6 lg:p-8" aria-labelledby="opcion-gratis">
                        <p class="text-sm text-site-muted">{{ $invitation->package === \App\Support\Packages::FREE ? 'Seguir gratis' : 'Una por cuenta' }}</p>
                        <h2 id="opcion-gratis" class="mt-1 text-2xl font-semibold tracking-tight">Gratis</h2>
                        <ul class="mt-4 grid gap-1.5 text-sm">
                            @foreach(['Portada con foto, nombre y mensaje', 'Cuenta regresiva y botón para agendar la fecha', 'Ubicación con mapa y cómo llegar'] as $feature)
                                <li class="flex items-start gap-2">
                                    <x-phosphor-check class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        @if($freeOff !== [])
                            <p class="mt-4 text-sm">
                                <span class="font-medium">Se apagan:</span>
                                {{ collect($freeOff)->pluck('label')->join(', ', ' y ') }}.
                                <span class="text-site-muted">Lo que armaste queda guardado por si después la subes de nivel.</span>
                            </p>
                        @endif
                        <p class="mt-4 text-sm text-site-muted">Lleva la marca de Bida Events a la vista y queda en línea hasta el {{ $freeUntil->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}.</p>

                        <form method="POST" action="{{ route('client.diy.publish.store', $invitation) }}" class="mt-auto pt-6">
                            @csrf
                            <input type="hidden" name="mode" value="free">
                            <button type="submit" @class(['min-h-11', 'admin-primary-button' => $isFreeTier, 'admin-link-button' => ! $isFreeTier]) @disabled($needsEmail)>
                                <x-phosphor-paper-plane-tilt aria-hidden="true" />
                                {{ $freeOff === [] ? 'Publicar gratis' : 'Publicar gratis sin esas partes' }}
                            </button>
                        </form>
                    </section>
                @endif
            </div>

            @if($isFreeTier && $canGoFree)
                <p class="site-enter text-sm text-site-muted" style="--enter-index: 3">
                    ¿Quieres galería de fotos, música o que tus invitados confirmen asistencia? Enciéndelas en el editor: cada parte dice desde qué nivel viene.
                </p>
            @elseif($freeUsedElsewhere)
                <p class="site-enter text-sm text-site-muted" style="--enter-index: 3">
                    Ya usaste tu invitación gratuita en otro evento: esta se publica con un nivel pagado.
                </p>
            @endif
        @endif

        {{-- Los puentes: que la termine el equipo y, si ya armó otras, lo que conviene con un plan --}}
        @unless($covered)
            <div class="site-enter grid gap-2 border-t border-site-line pt-6 text-sm leading-relaxed text-site-muted" style="--enter-index: 4">
                <p>
                    ¿Quieres algo a medida?
                    <a href="{{ $doneForYouUrl }}" target="_blank" rel="noopener" class="font-medium text-site-ink underline underline-offset-4">Escríbenos</a>
                    por WhatsApp o al correo {{ config('bida.email') }} y lo conversamos.
                </p>
                @if($planBridge)
                    <p>
                        ¿Armas varias? Desde la {{ $planBridge['from'] }}.ª invitación del mes, el plan {{ $planBridge['plan'] }}
                        ({{ \App\Support\Money::format($planBridge['price']) }} al mes) te sale más barato que pagar cada una.
                        <a href="{{ lroute('pricing') }}#planes" class="font-medium text-site-ink underline underline-offset-4">Ver los planes</a>
                    </p>
                @endif
            </div>
        @endunless

        <p class="site-enter" style="--enter-index: 5">
            <a href="{{ route('client.diy.edit', $invitation) }}" class="admin-link-button">
                <x-phosphor-arrow-left aria-hidden="true" />
                Seguir editando
            </a>
        </p>
    </div>
@endsection
