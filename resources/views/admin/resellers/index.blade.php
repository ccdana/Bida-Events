@extends('layouts.admin')

@section('title', 'Revendedores')

@section('content')
    <div class="grid gap-10">
        <header class="site-enter">
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Revendedores</h1>
            <p class="mt-2 max-w-[60ch] text-site-muted">
                Fotógrafos, wedding planners y emprendedores que arman sus propias invitaciones con una
                suscripción mensual. El cobro es manual: registra cada pago cuando lo recibas.
            </p>
        </header>

        @if($errors->any())
            <div class="site-enter rounded-[12px] border border-site-danger/40 bg-site-danger/10 p-4" role="alert">
                <p class="font-medium text-site-danger">No se guardó</p>
                <ul class="mt-2 list-disc pl-5 text-sm text-site-danger">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Las credenciales del revendedor recién creado se ven una sola vez --}}
        @if(session('reseller_credentials'))
            @php($credentials = session('reseller_credentials'))
            <section class="site-enter admin-card p-6" role="status">
                <h2 class="text-lg font-semibold tracking-tight">Datos de acceso de {{ $credentials['name'] }}</h2>
                <p class="mt-1 text-sm text-site-muted">Cópialos ahora: la contraseña no se vuelve a mostrar.</p>
                <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-site-muted">Usuario</dt>
                        <dd class="font-mono font-semibold">{{ $credentials['username'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-site-muted">Contraseña</dt>
                        <dd class="font-mono font-semibold">{{ $credentials['password'] }}</dd>
                    </div>
                </dl>
                <div class="mt-4">
                    <x-ui.copy-button
                        :text="'Hola '.$credentials['name'].', ya tienes tu cuenta de '.config('bida.brand').'. Entra en '.route('login').' con el usuario '.$credentials['username'].' y la contraseña '.$credentials['password'].'.'"
                        label="Copiar mensaje para enviar" done="Mensaje copiado" icon="whatsapp-logo" />
                </div>
            </section>
        @endif

        {{-- Vencen pronto: el administrador les escribe él mismo; no hay envíos automáticos --}}
        @if($dueSoon->isNotEmpty())
            <section class="site-enter rounded-[16px] border border-site-accent/50 bg-site-tint p-5" style="--enter-index: 1">
                <h2 class="flex items-center gap-2 text-lg font-semibold tracking-tight">
                    <x-phosphor-bell-ringing class="size-5 text-site-accent" aria-hidden="true" />
                    Vencen en los próximos {{ App\Support\ResellerSubscription::WARNING_DAYS }} días o ya vencieron
                </h2>
                <ul class="mt-3 divide-y divide-site-line">
                    @foreach($dueSoon as $row)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-2.5">
                            <p>
                                <span class="font-medium">{{ $row['reseller']->name }}</span>
                                <span class="text-sm text-site-muted">
                                    · {{ $row['planName'] }} ·
                                    @if($row['daysLeft'] < 0)
                                        venció el {{ $row['renewsLabel'] }}
                                    @elseif($row['daysLeft'] === 0)
                                        vence hoy
                                    @else
                                        vence el {{ $row['renewsLabel'] }}
                                    @endif
                                </span>
                            </p>
                            <a href="{{ $row['reminderUrl'] }}" target="_blank" rel="noopener" class="admin-link-button">
                                <x-phosphor-whatsapp-logo aria-hidden="true" />
                                Escribirle
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_21rem] lg:items-start">
            <section class="site-enter overflow-hidden rounded-[16px] border border-site-line bg-site-surface" style="--enter-index: 2">
                <div class="overflow-x-auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th scope="col">Revendedor</th>
                                <th scope="col">Plan</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Renueva el</th>
                                <th scope="col">Este mes</th>
                                <th scope="col"><span class="sr-only">Pago</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr x-data="{ open: false }">
                                    <td class="font-medium">
                                        {{ $row['reseller']->name }}
                                        <span class="mt-0.5 block font-mono text-xs font-normal text-site-muted">{{ $row['reseller']->username }}</span>
                                        @if($row['reseller']->business_name)
                                            <span class="mt-0.5 block text-xs font-normal text-site-muted">{{ $row['reseller']->business_name }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $row['planName'] }}</td>
                                    <td>
                                        <span class="admin-status-badge {{ $row['statusClass'] }}">
                                            <span class="admin-status-dot"></span>
                                            {{ $row['statusLabel'] }}
                                        </span>
                                    </td>
                                    <td class="tabular-nums">
                                        {{ $row['renewsLabel'] }}
                                        @if($row['lastPayment'])
                                            <span class="mt-0.5 block text-xs text-site-muted">
                                                Último pago: {{ \App\Support\Money::format($row['lastPayment']->amount, $row['lastPayment']->currency) }}, {{ $row['lastPayment']->paid_at->format('d/m/Y') }}
                                            </span>
                                            {{-- Un pago cargado por error se anula y la fecha vuelve a la anterior --}}
                                            <form method="POST" action="{{ route('admin.resellers.payments.destroy', [$row['reseller'], $row['lastPayment']]) }}"
                                                onsubmit="return confirm('¿Anular el último pago de {{ e($row['reseller']->name) }}? La fecha de renovación vuelve a la anterior.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="mt-1 text-xs text-site-danger underline underline-offset-2">Anular este pago</button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="tabular-nums">{{ $row['quotaLabel'] }}</td>
                                    <td>
                                        <button type="button" class="admin-link-button" @click="open = !open" :aria-expanded="open ? 'true' : 'false'">
                                            <x-phosphor-receipt aria-hidden="true" />
                                            Registrar pago
                                        </button>
                                        {{-- El botón se apaga al enviar y el código único evita que un doble clic registre dos pagos --}}
                                        <form x-show="open" x-cloak method="POST" action="{{ route('admin.resellers.payments.store', $row['reseller']) }}"
                                            class="mt-3 grid min-w-[15rem] gap-3" x-data="{ sending: false }" @submit="sending = true">
                                            @csrf
                                            <input type="hidden" name="request_token" value="{{ Illuminate\Support\Str::uuid() }}">
                                            <div>
                                                <label class="admin-label" for="plan-{{ $row['reseller']->id }}">Plan</label>
                                                <select id="plan-{{ $row['reseller']->id }}" name="plan" class="admin-input">
                                                    @foreach($plans as $key => $plan)
                                                        <option value="{{ $key }}" @selected($key === $row['planKey'])>{{ $plan['name'] }} · {{ \App\Support\Money::format(\App\Support\Offers::planPrice($plan)) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="admin-label" for="monto-{{ $row['reseller']->id }}">Monto ({{ \App\Support\Money::code() }})</label>
                                                <input id="monto-{{ $row['reseller']->id }}" type="number" name="amount" min="0" step="0.01" required class="admin-input"
                                                    value="{{ $row['planPrice'] }}">
                                            </div>
                                            <div>
                                                <label class="admin-label" for="nota-{{ $row['reseller']->id }}">Nota <span class="font-normal text-site-muted">(opcional)</span></label>
                                                <input id="nota-{{ $row['reseller']->id }}" type="text" name="note" maxlength="500" class="admin-input" placeholder="Transferencia BNB">
                                            </div>
                                            <button type="submit" class="admin-primary-button" :disabled="sending">
                                                <span x-text="sending ? 'Guardando…' : 'Guardar pago'">Guardar pago</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="flex flex-col items-center py-12 text-center">
                                            <x-phosphor-storefront-light class="size-12 text-site-accent" aria-hidden="true" />
                                            <p class="mt-4 font-medium">Todavía no hay revendedores</p>
                                            <p class="mt-1 text-site-muted">Agrega el primero con el formulario.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <aside class="site-enter admin-card p-6 lg:sticky lg:top-24" style="--enter-index: 3">
                <h2 class="text-lg font-semibold tracking-tight">Nuevo revendedor</h2>
                <p class="mt-1 text-sm text-site-muted">Se le genera usuario y contraseña. Queda activo al registrar su primer pago.</p>

                <form method="POST" action="{{ route('admin.resellers.store') }}" class="mt-6 grid gap-4">
                    @csrf
                    <div>
                        <label for="reseller-name" class="admin-label">Nombre</label>
                        <input id="reseller-name" type="text" name="name" value="{{ old('name') }}" required class="admin-input" placeholder="Carla Mendoza">
                    </div>
                    <div>
                        <label for="reseller-business" class="admin-label">Nombre comercial <span class="font-normal text-site-muted">(opcional)</span></label>
                        <input id="reseller-business" type="text" name="business_name" value="{{ old('business_name') }}" maxlength="120" class="admin-input" placeholder="Estudio Luz de Tarde">
                        <p class="mt-1.5 text-xs text-site-muted">Con marca blanca, es lo que aparece al pie de sus invitaciones.</p>
                    </div>
                    <div>
                        <label for="reseller-plan" class="admin-label">Plan</label>
                        <select id="reseller-plan" name="plan" class="admin-input">
                            @foreach($plans as $key => $plan)
                                <option value="{{ $key }}" @selected(old('plan') === $key)>
                                    {{ $plan['name'] }} · {{ \App\Support\Money::format(\App\Support\Offers::planPrice($plan)) }} · {{ $plan['quota_per_month'] ?? 'sin tope de' }} invitaciones/mes{{ $plan['white_label'] ? ' · marca blanca' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="admin-primary-button mt-2 min-h-11 w-full">
                        <x-phosphor-user-plus aria-hidden="true" />
                        Crear revendedor
                    </button>
                </form>
            </aside>
        </div>
    </div>
@endsection
