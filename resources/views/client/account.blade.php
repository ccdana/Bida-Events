@extends('layouts.client')

@section('title', 'Mi cuenta')

{{-- La cuenta del cliente o del revendedor: sus datos de acceso, el correo y la contraseña --}}
@section('content')
    @php($emailVerified = filled($user->email) && $user->hasVerifiedEmail())

    <div class="grid max-w-2xl gap-8">
        <header class="site-enter">
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Mi cuenta</h1>
            <p class="mt-2 text-site-muted">Tus datos para entrar al panel. Con tu correo confirmado puedes recuperar la contraseña sin pedírnosla.</p>
        </header>

        @if(session('success'))
            <p class="site-enter flex items-center gap-2 rounded-[12px] border border-site-line bg-site-surface px-4 py-3 text-sm" role="status">
                <x-phosphor-check-circle class="size-5 shrink-0 text-site-accent" aria-hidden="true" />
                {{ session('success') }}
            </p>
        @endif

        <section class="site-enter admin-card" style="--enter-index: 1">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-site-muted">Nombre</dt>
                    <dd class="mt-0.5 font-medium">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-site-muted">Usuario</dt>
                    <dd class="mt-0.5 font-mono font-medium">{{ $user->username }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-site-muted">Correo</dt>
                    <dd class="mt-0.5 font-medium">
                        @if(filled($user->email))
                            <span class="break-all">{{ $user->email }}</span>
                            <span class="block text-sm font-normal text-site-muted">{{ $emailVerified ? 'Confirmado' : 'Sin confirmar' }}{{ $user->google_id ? ' · entras con Google' : '' }}</span>
                        @else
                            <span class="text-site-muted">Sin correo</span>
                        @endif
                    </dd>
                </div>
                @if($user->business_name)
                    <div>
                        <dt class="text-sm text-site-muted">Nombre comercial</dt>
                        <dd class="mt-0.5 font-medium">{{ $user->business_name }}</dd>
                    </div>
                @endif
                @if($user->isReseller())
                    <div>
                        <dt class="text-sm text-site-muted">Plan</dt>
                        <dd class="mt-0.5 font-medium">{{ $user->planConfig()['name'] ?? 'Sin plan' }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        {{-- Plan y pagos: para revendedores, o para quien puede empezar uno pagando en línea --}}
        @if($user->isReseller() || $payOnline)
            <section class="site-enter admin-card" style="--enter-index: 2" aria-labelledby="plan-pagos">
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2">
                    <h2 id="plan-pagos" class="text-lg font-semibold tracking-tight">Plan y pagos</h2>
                    @if($payOnline)
                        <a href="{{ route('client.subscription') }}" class="text-sm font-medium underline underline-offset-4">
                            {{ $user->renewsAutomatically() ? 'Administrar mi suscripción' : ($user->isReseller() ? 'Renovar o cambiar de plan' : 'Elegir un plan') }}
                        </a>
                    @endif
                </div>

                @if($user->isReseller())
                    <p class="mt-1 text-sm text-site-muted">
                        Plan {{ $user->planConfig()['name'] ?? 'sin elegir' }}
                        @if($user->subscription_renews_at)
                            · {{ $user->renewsAutomatically() ? 'se renueva solo con tarjeta el' : ($user->hasActiveSubscription() ? 'pagado hasta el' : 'venció el') }}
                            {{ $user->subscription_renews_at->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}
                        @endif
                    </p>
                @else
                    <p class="mt-1 text-sm text-site-muted">Con un plan armas tus propias invitaciones con nuestras plantillas.</p>
                @endif

                @if($payments->isNotEmpty())
                    <table class="mt-5 w-full text-sm">
                        <caption class="sr-only">Últimos pagos</caption>
                        <thead class="text-left text-site-muted">
                            <tr>
                                <th scope="col" class="pb-2 font-normal">Fecha</th>
                                <th scope="col" class="pb-2 font-normal">Plan</th>
                                <th scope="col" class="pb-2 text-right font-normal">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-site-line">
                            @foreach($payments as $payment)
                                <tr>
                                    <td class="py-2 tabular-nums">{{ $payment->paid_at->format('d/m/Y') }}</td>
                                    <td class="py-2">
                                        {{ config("bida.reseller_plans.{$payment->plan}.name", $payment->plan) }}
                                        <span class="text-site-muted">· {{ $payment->providerLabel() }}</span>
                                    </td>
                                    <td class="py-2 text-right tabular-nums">{{ \App\Support\Money::format($payment->amount, $payment->currency) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        @endif

        <section class="site-enter admin-card" style="--enter-index: 2" aria-labelledby="cambiar-correo">
            <h2 id="cambiar-correo" class="text-lg font-semibold tracking-tight">{{ filled($user->email) ? 'Cambiar el correo' : 'Agregar un correo' }}</h2>
            <p class="mt-1 text-sm text-site-muted">Te enviamos un enlace para confirmarlo. Ahí te llegarán también los avisos de tu cuenta.</p>

            <form method="POST" action="{{ route('client.account.email') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PUT')

                <div>
                    <label for="email" class="admin-label">Correo</label>
                    <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email', $user->email) }}"
                        autocomplete="email" autocapitalize="none" spellcheck="false" class="admin-input" @error('email') aria-invalid="true" @enderror>
                    @error('email')
                        <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email_current_password" class="admin-label">Tu contraseña actual</label>
                    <input id="email_current_password" name="email_current_password" type="password" required
                        autocomplete="current-password" class="admin-input" @error('email_current_password') aria-invalid="true" @enderror>
                    @error('email_current_password')
                        <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <button type="submit" class="admin-primary-button min-h-11">
                        <x-phosphor-envelope-simple aria-hidden="true" />
                        Guardar el correo
                    </button>
                </div>
            </form>
        </section>

        <section class="site-enter admin-card" style="--enter-index: 3" aria-labelledby="cambiar-contrasena">
            <h2 id="cambiar-contrasena" class="text-lg font-semibold tracking-tight">Cambiar contraseña</h2>
            <p class="mt-1 text-sm text-site-muted">Al cambiarla se cierra tu sesión en los demás dispositivos donde estabas conectado.</p>

            <form method="POST" action="{{ route('client.account.password') }}" class="mt-5 grid gap-4" x-data="{ show: false }">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="admin-label">Contraseña actual</label>
                    <input id="current_password" name="current_password" :type="show ? 'text' : 'password'" type="password" required
                        autocomplete="current-password" class="admin-input" @error('current_password') aria-invalid="true" @enderror>
                    @error('current_password')
                        <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="admin-label">Contraseña nueva</label>
                        <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" required minlength="8"
                            autocomplete="new-password" class="admin-input" aria-describedby="password-help" @error('password') aria-invalid="true" @enderror>
                        <p id="password-help" class="mt-1.5 text-xs text-site-muted">Al menos 8 caracteres.</p>
                        @error('password')
                            <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="admin-label">Repite la nueva</label>
                        <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" type="password" required
                            autocomplete="new-password" class="admin-input">
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" x-model="show" class="size-4">
                    Mostrar las contraseñas
                </label>

                <div>
                    <button type="submit" class="admin-primary-button min-h-11">
                        <x-phosphor-key aria-hidden="true" />
                        Guardar la contraseña nueva
                    </button>
                </div>
            </form>

            {{-- Sin la actual (entró con Google o la olvidó): enlace al correo confirmado --}}
            @if($emailVerified)
                <form method="POST" action="{{ route('client.account.password-link') }}" class="mt-6 border-t border-site-line pt-5 text-sm text-site-muted">
                    @csrf
                    {{ $user->google_id ? '¿Entras con Google y quieres una contraseña propia?' : '¿No recuerdas la actual?' }}
                    <button type="submit" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">Envíame un enlace para crearla</button>
                    @error('password_link')
                        <span class="mt-1.5 block text-site-danger">{{ $message }}</span>
                    @enderror
                </form>
            @endif
        </section>
    </div>
@endsection
