@extends('layouts.client')

@section('title', 'Publicar mi invitación')

{{--
    «Hazlo tú», antes de publicar (Client\DiyInvitationController::publish): el nivel que piden las
    partes encendidas y las dos salidas, gratis (apaga lo que la gratuita no trae) o con ese nivel.
    También sirve para volver a publicar una oculta y para subir de nivel una ya publicada.
    Sin tarjetas: dos columnas separadas por un filete (una sola en el celular), con lo que incluye, el precio, la forma
    de pago como filas con un punto dorado (como en «Mi plan») y un botón. Estilos: home.css («Elegir»).
--}}
@section('content')
    @php($isFreeTier = $tier === \App\Support\Packages::FREE)
    @php($showTier = ! ($isFreeTier && $canGoFree))

    <div class="mx-auto grid w-full max-w-5xl gap-6">
        <header class="site-enter">
            <p class="text-sm text-site-muted">{{ $invitation->title }}</p>
            <h1 class="site-display site-display--md mt-1">
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
                    Usa solo lo que trae la gratuita: la portada, la cuenta regresiva y la ubicación.
                @else
                    Con lo que encendiste, tu invitación es nivel <strong class="font-semibold text-site-ink">{{ $tierName }}</strong>.
                @endif
            </p>
        </header>

        @error('publish')
            <p class="site-enter flex items-start gap-2 border-l-2 border-site-danger py-1 pl-3 text-sm text-site-danger" role="alert">
                <x-phosphor-warning-circle-light class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                {{ $message }}
            </p>
        @enderror

        @if($needsEmail)
            <p class="site-enter flex items-start gap-2 border-l-2 border-site-accent py-1 pl-3 text-sm lg:max-w-xl" role="status">
                <x-phosphor-envelope-simple-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                <span>
                    Para publicar necesitas un correo confirmado: ahí te avisamos de las confirmaciones y del vencimiento de tu invitación.
                    <a href="{{ $hasEmail ? route('verification.notice') : route('client.account') }}" class="font-medium underline underline-offset-4">{{ $hasEmail ? 'Confirmar mi correo' : 'Agregar mi correo' }}</a>
                </span>
            </p>
        @endif

        @if($covered)
            {{-- Ya tiene el nivel que piden sus partes: solo hace falta volver a encenderla --}}
            <section class="site-enter grid content-start gap-4 lg:max-w-xl" style="--enter-index: 1">
                <p class="flex items-start gap-2 text-sm text-site-muted">
                    <x-phosphor-calendar-check-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                    Queda en línea hasta el {{ $paidUntil->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}.
                </p>
                <form method="POST" action="{{ route('client.diy.publish.store', $invitation) }}">
                    @csrf
                    <input type="hidden" name="mode" value="covered">
                    <button type="submit" class="site-btn site-btn--lg" @disabled($needsEmail)>
                        <x-phosphor-paper-plane-tilt-light aria-hidden="true" />
                        Publicar otra vez
                    </button>
                </form>
            </section>
        @else
            {{-- Lo que hace que no sea gratuita: cada parte con el nivel desde el que viene --}}
            @if($paidParts !== [])
                <section class="site-enter lg:max-w-xl" style="--enter-index: 1" aria-labelledby="partes-nivel">
                    <h2 id="partes-nivel" class="site-legend">Lo que encendiste además de lo gratuito</h2>
                    <ul class="site-choices">
                        @foreach($paidParts as $part)
                            <li class="flex items-baseline justify-between gap-4 border-b border-site-line py-2.5">
                                <span>{{ $part['label'] }}</span>
                                <span class="text-sm text-site-muted">{{ $part['tierName'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <div @class(['site-enter grid items-start gap-x-12 gap-y-10', 'lg:grid-cols-2' => $showTier && $canGoFree, 'lg:max-w-xl' => ! ($showTier && $canGoFree)]) style="--enter-index: 2">
                {{-- Con su nivel: todo lo que armó, tal cual --}}
                @if($showTier)
                    <section class="grid content-start gap-4" aria-labelledby="opcion-nivel">
                        <div>
                            <p class="text-sm text-site-muted">{{ $currentTierName ? 'Subir de nivel' : 'Tal como la armaste' }}</p>
                            <h2 id="opcion-nivel" class="site-display site-display--sm mt-1">{{ $tierName }}</h2>
                        </div>
                        @if($tierFeatures !== [])
                            <ul class="site-checks">
                                @foreach($tierFeatures as $feature)
                                    <li><x-phosphor-check-light aria-hidden="true" /> <span>{{ $feature }}</span></li>
                                @endforeach
                            </ul>
                        @endif
                        <p class="flex items-start gap-2 text-sm text-site-muted">
                            <x-phosphor-calendar-check-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                            En línea hasta el {{ $paidUntil->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}, sin la franja de invitación gratuita.
                        </p>

                        {{-- Pago único: el precio del nivel, la diferencia si ya tenía otro o el precio de su temporada --}}
                        <p class="site-total">
                            <span class="site-total__amount tabular-nums">{{ $dueLabel }}</span>
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

                        @if($due === 0)
                            {{-- Sin nada que cobrar (por ejemplo, con un descuento nuevo) --}}
                            <form method="POST" action="{{ route('client.diy.checkout', $invitation) }}">
                                @csrf
                                <button type="submit" class="site-btn site-btn--lg" @disabled($needsEmail)>Activar {{ $tierName }}</button>
                            </form>
                        @elseif($payMethods !== [])
                            <form method="POST" action="{{ route('client.diy.checkout', $invitation) }}" class="grid gap-4" x-data="{ sending: false }" @submit="sending = true">
                                @csrf
                                <fieldset>
                                    <legend class="sr-only">Cómo pagar</legend>
                                    <div class="site-choices">
                                        @foreach($payMethods as $method => $option)
                                            <label class="site-choice">
                                                <input type="radio" name="method" value="{{ $method }}" class="sr-only" @checked($loop->first) required>
                                                <span class="site-choice__dot" aria-hidden="true"></span>
                                                <span class="site-choice__body">
                                                    <span class="site-choice__head">
                                                        <span class="site-choice__name">{{ $option['label'] }}</span>
                                                        <span class="font-semibold tabular-nums">{{ $option['amount'] }}</span>
                                                    </span>
                                                    <span class="site-choice__note">{{ $option['note'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <div>
                                    <button type="submit" class="site-btn site-btn--lg" :disabled="sending || @js($needsEmail)" @disabled($needsEmail)>
                                        <x-phosphor-lock-simple-light aria-hidden="true" />
                                        <span x-text="sending ? 'Abriendo el pago…' : @js($currentTierName ? 'Pagar y subir a '.$tierName : 'Pagar y publicar')">{{ $currentTierName ? 'Pagar y subir a '.$tierName : 'Pagar y publicar' }}</span>
                                    </button>
                                </div>
                                <p class="flex items-start gap-2 text-sm text-site-muted">
                                    <x-phosphor-info-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                                    Tu invitación se publica sola en cuanto se confirma el pago.
                                </p>
                            </form>
                        @else
                            {{-- Sin pago en línea encendido: se pide por WhatsApp y el equipo activa el nivel --}}
                            <div>
                                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn site-btn--lg">
                                    <x-phosphor-whatsapp-logo-light aria-hidden="true" />
                                    Pedir {{ $tierName }} por WhatsApp
                                </a>
                            </div>
                        @endif
                    </section>
                @endif

                {{-- Gratis: la portada, la cuenta regresiva y la ubicación, con la marca de Bida Events --}}
                @if($canGoFree)
                    <section @class(['grid content-start gap-4', 'lg:border-l lg:border-site-line lg:pl-12' => $showTier]) aria-labelledby="opcion-gratis">
                        <div>
                            <p class="text-sm text-site-muted">{{ $invitation->package === \App\Support\Packages::FREE ? 'Seguir gratis' : 'Una por cuenta' }}</p>
                            <h2 id="opcion-gratis" class="site-display site-display--sm mt-1">Gratis</h2>
                        </div>
                        <ul class="site-checks">
                            @foreach(['Portada con foto, nombre y mensaje', 'Cuenta regresiva y botón para agendar la fecha', 'Ubicación con mapa y cómo llegar'] as $feature)
                                <li><x-phosphor-check-light aria-hidden="true" /> <span>{{ $feature }}</span></li>
                            @endforeach
                        </ul>
                        @if($freeOff !== [])
                            <p class="flex items-start gap-2 text-sm">
                                <x-phosphor-eye-slash-light class="mt-0.5 size-4 shrink-0 text-site-muted" aria-hidden="true" />
                                <span>
                                    <span class="font-medium">Se apagan:</span>
                                    {{ collect($freeOff)->pluck('label')->join(', ', ' y ') }}.
                                    <span class="text-site-muted">Lo que armaste queda guardado por si después la subes de nivel.</span>
                                </span>
                            </p>
                        @endif
                        <p class="flex items-start gap-2 text-sm text-site-muted">
                            <x-phosphor-calendar-check-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                            Lleva la marca de Bida Events a la vista y queda en línea hasta el {{ $freeUntil->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}.
                        </p>

                        <form method="POST" action="{{ route('client.diy.publish.store', $invitation) }}">
                            @csrf
                            <input type="hidden" name="mode" value="free">
                            <button type="submit" @class(['site-btn site-btn--lg', 'site-btn--ghost' => ! $isFreeTier]) @disabled($needsEmail)>
                                <x-phosphor-paper-plane-tilt-light aria-hidden="true" />
                                {{ $freeOff === [] ? 'Publicar gratis' : 'Publicar gratis sin esas partes' }}
                            </button>
                        </form>
                    </section>
                @endif
            </div>

            @if($freeUsedElsewhere)
                <p class="site-enter flex items-start gap-2 border-l-2 border-site-accent py-1 pl-3 text-sm text-site-muted lg:max-w-xl" style="--enter-index: 3">
                    <x-phosphor-info-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                    Ya usaste tu invitación gratuita en otro evento: esta se publica con un nivel pagado.
                </p>
            @endif
        @endif

        {{-- Los puentes: que la termine el equipo y, si ya armó otras, lo que conviene con un plan --}}
        @unless($covered)
            <div class="site-enter grid gap-2 border-t border-site-line pt-5 text-sm leading-relaxed text-site-muted lg:max-w-xl" style="--enter-index: 4">
                <p class="flex items-start gap-2">
                    <x-phosphor-whatsapp-logo-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                    <span>
                        ¿Quieres algo a medida?
                        <a href="{{ $doneForYouUrl }}" target="_blank" rel="noopener" class="font-medium text-site-ink underline underline-offset-4">Escríbenos</a>
                    </span>
                </p>
                @if($planBridge)
                    <p class="flex items-start gap-2">
                        <x-phosphor-stack-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                        <span>
                            ¿Armas varias? Desde la {{ $planBridge['from'] }}.ª invitación del mes, el plan {{ $planBridge['plan'] }}
                            ({{ \App\Support\Money::format($planBridge['price']) }} al mes) te sale más barato que pagar cada una.
                            <a href="{{ lroute('pricing') }}#planes" class="font-medium text-site-ink underline underline-offset-4">Ver los planes</a>
                        </span>
                    </p>
                @endif
            </div>
        @endunless

        <p class="site-enter" style="--enter-index: 5">
            <a href="{{ route('client.diy.edit', $invitation) }}" class="site-help">
                <x-phosphor-arrow-left-light aria-hidden="true" />
                Seguir editando
            </a>
        </p>
    </div>
@endsection
