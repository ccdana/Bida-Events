@extends('layouts.client')

@section('title', 'Mi cuenta')

{{--
    La cuenta del cliente o del revendedor: una lista corta de sus datos. Lo que se puede cambiar (el correo y la
    contraseña) tiene su fila con «Cambiar», que despliega solo ese formulario; si el envío falló, la fila vuelve
    abierta con su error. Campos de una línea, íconos en vez de frases. Estilos: home.css («Filas»).
--}}
@section('content')
    @php($emailVerified = filled($user->email) && $user->hasVerifiedEmail())
    @php($openEmail = $errors->has('email') || $errors->has('email_current_password'))
    @php($openPassword = $errors->has('current_password') || $errors->has('password') || $errors->has('password_link'))

    <div class="grid max-w-xl gap-5">
        <header class="site-enter">
            <h1 class="site-display site-display--md">Mi cuenta</h1>
        </header>

        @if(session('success'))
            <p class="site-enter flex items-start gap-2 border-l-2 border-site-accent py-1 pl-3 text-sm" role="status">
                <x-phosphor-check-circle-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                {{ session('success') }}
            </p>
        @endif

        <div class="site-rows site-enter" style="--enter-index: 1">
            {{-- Quién eres: nombre, usuario y, si tiene, el nombre comercial --}}
            <div class="site-row">
                <div class="site-row__summary">
                    <x-phosphor-user-light class="site-row__icon" aria-hidden="true" />
                    <span class="site-row__main">
                        <span class="site-row__label">Nombre</span>
                        <span class="site-row__value">{{ $user->name }}</span>
                    </span>
                </div>
            </div>
            <div class="site-row">
                <div class="site-row__summary">
                    <x-phosphor-at-light class="site-row__icon" aria-hidden="true" />
                    <span class="site-row__main">
                        <span class="site-row__label">Usuario</span>
                        <span class="site-row__value font-mono">{{ $user->username }}</span>
                    </span>
                </div>
            </div>
            @if($user->business_name)
                <div class="site-row">
                    <div class="site-row__summary">
                        <x-phosphor-storefront-light class="site-row__icon" aria-hidden="true" />
                        <span class="site-row__main">
                            <span class="site-row__label">Nombre comercial</span>
                            <span class="site-row__value">{{ $user->business_name }}</span>
                        </span>
                    </div>
                </div>
            @endif

            {{-- Correo: confirmado o no, con un ícono; «Cambiar» abre su formulario --}}
            <details class="site-row" @if($openEmail) open @endif>
                <summary class="site-row__summary">
                    <x-phosphor-envelope-simple-light class="site-row__icon" aria-hidden="true" />
                    <span class="site-row__main">
                        <span class="site-row__label">Correo</span>
                        <span class="site-row__value">
                            @if(filled($user->email))
                                <span class="break-all">{{ $user->email }}</span>
                                @if($emailVerified)
                                    <x-phosphor-check-circle-light class="site-row__state is-ok" aria-hidden="true" />
                                    <span class="sr-only">Confirmado</span>
                                @else
                                    <x-phosphor-warning-circle-light class="site-row__state is-warn" aria-hidden="true" />
                                    <span class="sr-only">Sin confirmar</span>
                                @endif
                                @if($user->google_id)
                                    <x-phosphor-google-logo-light class="site-row__state" aria-hidden="true" />
                                    <span class="sr-only">Entras con Google</span>
                                @endif
                            @else
                                <span class="text-site-muted">Sin correo</span>
                            @endif
                        </span>
                    </span>
                    <span class="site-row__change" title="{{ filled($user->email) ? 'Cambiar el correo' : 'Agregar un correo' }}">
                        <x-phosphor-pencil-simple-light class="site-row__open" aria-hidden="true" />
                        <x-phosphor-x-light class="site-row__close" aria-hidden="true" />
                        <span>{{ filled($user->email) ? 'Cambiar' : 'Agregar' }}</span>
                    </span>
                </summary>

                <form method="POST" action="{{ route('client.account.email') }}" class="site-row__body grid gap-4">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-1">
                        <label for="email" class="site-label">Correo nuevo</label>
                        <div class="site-field">
                            <x-phosphor-envelope-simple-light class="site-field__icon" aria-hidden="true" />
                            <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email', $user->email) }}"
                                autocomplete="email" autocapitalize="none" spellcheck="false" @class(['site-input', 'is-invalid' => $errors->has('email')]) @error('email') aria-invalid="true" @enderror>
                        </div>
                        @error('email')
                            <p class="text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-1">
                        <label for="email_current_password" class="site-label">Tu contraseña</label>
                        <div class="site-field">
                            <x-phosphor-lock-key-light class="site-field__icon" aria-hidden="true" />
                            <input id="email_current_password" name="email_current_password" type="password" required
                                autocomplete="current-password" @class(['site-input', 'is-invalid' => $errors->has('email_current_password')]) @error('email_current_password') aria-invalid="true" @enderror>
                        </div>
                        @error('email_current_password')
                            <p class="text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        <button type="submit" class="admin-primary-button min-h-11">
                            <x-phosphor-check-bold aria-hidden="true" />
                            Guardar
                        </button>
                        <span class="flex items-center gap-1.5 text-sm text-site-muted">
                            <x-phosphor-paper-plane-tilt-light class="size-4 text-site-accent" aria-hidden="true" />
                            Te enviamos un enlace para confirmarlo.
                        </span>
                    </div>
                </form>
            </details>

            {{-- Contraseña: «Cambiar» abre sus tres campos --}}
            <details class="site-row" @if($openPassword) open @endif>
                <summary class="site-row__summary">
                    <x-phosphor-lock-key-light class="site-row__icon" aria-hidden="true" />
                    <span class="site-row__main">
                        <span class="site-row__label">Contraseña</span>
                        <span class="site-row__value" aria-hidden="true">••••••••</span>
                    </span>
                    <span class="site-row__change" title="Cambiar la contraseña">
                        <x-phosphor-pencil-simple-light class="site-row__open" aria-hidden="true" />
                        <x-phosphor-x-light class="site-row__close" aria-hidden="true" />
                        <span>Cambiar</span>
                    </span>
                </summary>

                <form method="POST" action="{{ route('client.account.password') }}" class="site-row__body grid gap-4" x-data="{ show: false }">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-1">
                        <label for="current_password" class="site-label">Actual</label>
                        <div class="site-field">
                            <x-phosphor-lock-key-light class="site-field__icon" aria-hidden="true" />
                            <input id="current_password" name="current_password" :type="show ? 'text' : 'password'" type="password" required
                                autocomplete="current-password" @class(['site-input', 'is-invalid' => $errors->has('current_password')]) @error('current_password') aria-invalid="true" @enderror>
                        </div>
                        @error('current_password')
                            <p class="text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-1">
                            <label for="password" class="site-label">Nueva</label>
                            <div class="site-field">
                                <x-phosphor-key-light class="site-field__icon" aria-hidden="true" />
                                <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" required minlength="8" maxlength="72"
                                    autocomplete="new-password" aria-describedby="password-help" @class(['site-input', 'is-invalid' => $errors->has('password')]) @error('password') aria-invalid="true" @enderror>
                            </div>
                            @error('password')
                                <p class="text-sm text-site-danger">{{ $message }}</p>
                            @else
                                <p id="password-help" class="text-sm text-site-muted">Mínimo 8, con letras y números.</p>
                            @enderror
                        </div>
                        <div class="grid gap-1">
                            <label for="password_confirmation" class="site-label">Repítela</label>
                            <div class="site-field">
                                <x-phosphor-key-light class="site-field__icon" aria-hidden="true" />
                                <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" type="password" required
                                    autocomplete="new-password" class="site-input">
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                        <button type="submit" class="admin-primary-button min-h-11">
                            <x-phosphor-check-bold aria-hidden="true" />
                            Guardar
                        </button>
                        <button type="button" class="site-help" @click="show = !show" :aria-pressed="show.toString()">
                            <x-phosphor-eye-light x-show="!show" aria-hidden="true" />
                            <x-phosphor-eye-slash-light x-show="show" x-cloak aria-hidden="true" />
                            <span x-text="show ? 'Ocultar' : 'Mostrar'">Mostrar</span>
                        </button>
                        <span class="flex items-center gap-1.5 text-sm text-site-muted">
                            <x-phosphor-devices-light class="size-4 text-site-accent" aria-hidden="true" />
                            Se cierra tu sesión en los demás dispositivos.
                        </span>
                    </div>
                </form>

                {{-- Sin la actual (entró con Google o la olvidó): enlace al correo confirmado --}}
                @if($emailVerified)
                    <form method="POST" action="{{ route('client.account.password-link') }}" class="site-row__body border-t border-site-line text-sm text-site-muted">
                        @csrf
                        <button type="submit" class="site-help" title="{{ $user->google_id ? '¿Entras con Google y quieres una contraseña propia?' : '¿No recuerdas la actual?' }}">
                            <x-phosphor-envelope-simple-light aria-hidden="true" />
                            {{ $user->google_id ? 'Crear una contraseña propia' : '¿No la recuerdas? Envíame un enlace' }}
                        </button>
                        @error('password_link')
                            <span class="mt-1.5 block text-site-danger">{{ $message }}</span>
                        @enderror
                    </form>
                @endif
            </details>

            {{-- Plan y pagos: para revendedores, o para quien puede empezar uno pagando en línea --}}
            @if($user->isReseller() || $payOnline)
                <div class="site-row">
                    <div class="site-row__summary">
                        <x-phosphor-seal-check-light class="site-row__icon" aria-hidden="true" />
                        <span class="site-row__main">
                            <span class="site-row__label">Plan</span>
                            <span class="site-row__value">
                                @if($user->isReseller())
                                    {{ $user->planConfig()['name'] ?? 'Sin elegir' }}
                                    @if($user->subscription_renews_at)
                                        <span class="site-row__sub">
                                            @if($user->renewsAutomatically())
                                                <x-phosphor-arrow-clockwise-light aria-hidden="true" /> se renueva el
                                            @elseif($user->hasActiveSubscription())
                                                <x-phosphor-check-circle-light aria-hidden="true" /> pagado hasta el
                                            @else
                                                <x-phosphor-warning-circle-light aria-hidden="true" /> venció el
                                            @endif
                                            {{ $user->subscription_renews_at->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-site-muted">Sin plan</span>
                                @endif
                            </span>
                        </span>
                        @if($payOnline)
                            <a href="{{ route('client.subscription') }}" class="site-row__change" title="{{ $user->renewsAutomatically() ? 'Administrar mi suscripción' : ($user->isReseller() ? 'Renovar o cambiar de plan' : 'Elegir un plan') }}">
                                <span>{{ $user->renewsAutomatically() ? 'Administrar' : ($user->isReseller() ? 'Renovar' : 'Elegir') }}</span>
                                <x-phosphor-arrow-right-light aria-hidden="true" />
                            </a>
                        @endif
                    </div>
                </div>

                @if($payments->isNotEmpty())
                    <details class="site-row">
                        <summary class="site-row__summary">
                            <x-phosphor-receipt-light class="site-row__icon" aria-hidden="true" />
                            <span class="site-row__main">
                                <span class="site-row__label">Pagos</span>
                                <span class="site-row__value">{{ $payments->count() }} {{ $payments->count() === 1 ? 'reciente' : 'recientes' }}</span>
                            </span>
                            <span class="site-row__change" title="Ver los últimos pagos">
                                <x-phosphor-caret-down-light class="site-row__open" aria-hidden="true" />
                                <x-phosphor-caret-up-light class="site-row__close" aria-hidden="true" />
                            </span>
                        </summary>
                        <table class="site-row__body w-full text-sm">
                            <caption class="sr-only">Últimos pagos</caption>
                            <thead class="sr-only">
                                <tr><th scope="col">Fecha</th><th scope="col">Plan</th><th scope="col">Monto</th></tr>
                            </thead>
                            <tbody class="divide-y divide-site-line">
                                @foreach($payments as $payment)
                                    <tr>
                                        <td class="py-2 tabular-nums text-site-muted">{{ $payment->paid_at->format('d/m/Y') }}</td>
                                        <td class="py-2">
                                            {{ config("bida.reseller_plans.{$payment->plan}.name", $payment->plan) }}
                                            <span class="text-site-muted">· {{ $payment->providerLabel() }}</span>
                                        </td>
                                        <td class="py-2 text-right tabular-nums">{{ \App\Support\Money::format($payment->amount, $payment->currency) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                @endif
            @endif
        </div>
    </div>
@endsection
