@extends('layouts.client')

@section('title', 'Mi cuenta')

{{-- La cuenta del revendedor: sus datos de acceso y el cambio de contraseña --}}
@section('content')
    <div class="grid max-w-2xl gap-8">
        <header class="site-enter">
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Mi cuenta</h1>
            <p class="mt-2 text-site-muted">Con este usuario entras a tu panel. Cambia la contraseña que te dimos por una que recuerdes.</p>
        </header>

        @if(session('success'))
            <p class="site-enter flex items-center gap-2 rounded-[12px] border border-site-line bg-site-surface px-4 py-3 text-sm" role="status">
                <x-phosphor-check-circle class="size-5 text-site-accent" aria-hidden="true" />
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
                @if($user->business_name)
                    <div>
                        <dt class="text-sm text-site-muted">Nombre comercial</dt>
                        <dd class="mt-0.5 font-medium">{{ $user->business_name }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm text-site-muted">Plan</dt>
                    <dd class="mt-0.5 font-medium">{{ $user->planConfig()['name'] ?? 'Sin plan' }}</dd>
                </div>
            </dl>
        </section>

        <section class="site-enter admin-card" style="--enter-index: 2" aria-labelledby="cambiar-contrasena">
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
        </section>
    </div>
@endsection
