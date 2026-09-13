@extends('layouts.site')

@section('title', 'Ingresar | '.config('bida.brand'))

@section('content')
    @php($showcase = config('bida.showcase'))

    {{-- El rotador sincroniza la frase del subtítulo con las fotos del panel derecho --}}
    <main data-rotator data-rotator-interval="3600" class="grid min-h-[100dvh] lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)]">
        <div class="flex flex-col px-5 py-6 sm:px-10 lg:px-16">
            <a href="{{ route('home') }}" class="self-start text-lg">
                <x-brand.logo animated />
            </a>

            <div class="mx-auto my-auto w-full max-w-sm py-14 lg:mx-0">
                <h1 class="site-enter text-3xl font-semibold tracking-tight sm:text-4xl" style="--enter-index: 1">Ingresa a tu cuenta</h1>
                <p class="site-enter mt-3 leading-relaxed text-site-muted" style="--enter-index: 2">
                    <span class="sr-only">Administra tu invitación y las confirmaciones de tu evento.</span>
                    <span aria-hidden="true">
                        Administra tu invitación y las confirmaciones de
                        <span class="site-rotator font-medium text-site-ink" data-rotator-group>
                            @foreach($showcase as $index => $event)
                                <span @class(['is-active' => $index === 0])>{{ $event['phrase'] }}</span>
                            @endforeach
                        </span>
                    </span>
                </p>

                <form method="POST" action="{{ route('login') }}" @class(['mt-10 grid gap-6', 'site-shake' => $errors->any()])
                    x-data="{ showPassword: false, submitting: false, capsLock: false }" @submit="submitting = true">
                    @csrf

                    <div class="site-enter grid gap-2" style="--enter-index: 3">
                        <label for="email" class="text-[0.95rem] font-medium">Correo electrónico</label>
                        <div class="site-field">
                            <x-phosphor-envelope-simple-light class="site-field__icon" aria-hidden="true" />
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                autocomplete="username" inputmode="email"
                                @class(['site-input', 'is-invalid' => $errors->has('email')])
                                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        </div>
                        @error('email')
                            <p id="email-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="site-enter grid gap-2" style="--enter-index: 4">
                        <label for="password" class="text-[0.95rem] font-medium">Contraseña</label>
                        <div class="site-field">
                            <x-phosphor-lock-simple-light class="site-field__icon" aria-hidden="true" />
                            <input id="password" type="password" :type="showPassword ? 'text' : 'password'" name="password" required
                                autocomplete="current-password"
                                @keydown="capsLock = $event.getModifierState('CapsLock')" @keyup="capsLock = $event.getModifierState('CapsLock')"
                                @class(['site-input pr-12', 'is-invalid' => $errors->has('password')])
                                @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                            <button type="button" class="absolute inset-y-0 right-0 grid w-12 place-items-center rounded-r-[12px] text-site-muted transition-colors hover:text-site-ink"
                                @click="showPassword = !showPassword" aria-controls="password"
                                :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" aria-label="Mostrar contraseña">
                                <x-phosphor-eye class="site-swap is-on" x-bind:class="{ 'is-on': !showPassword }" aria-hidden="true" />
                                <x-phosphor-eye-slash class="site-swap" x-bind:class="{ 'is-on': showPassword }" aria-hidden="true" />
                            </button>
                        </div>
                        <p class="flex items-center gap-2 text-sm text-site-muted" x-show="capsLock" x-cloak x-transition.opacity aria-live="polite">
                            <x-phosphor-arrow-fat-line-up-light class="size-4" aria-hidden="true" />
                            Bloq Mayús está activado
                        </p>
                        @error('password')
                            <p id="password-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="site-enter flex cursor-pointer items-center gap-3 text-[0.95rem]" style="--enter-index: 5">
                        <input type="checkbox" name="remember" value="1" class="site-checkbox" @checked(old('remember'))>
                        Mantener la sesión iniciada
                    </label>

                    <div class="site-enter mt-2" style="--enter-index: 6">
                        <button type="submit" class="site-btn site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
                            <span x-text="submitting ? 'Ingresando' : 'Ingresar'">Ingresar</span>
                            <x-phosphor-arrow-right class="site-btn__arrow" x-show="!submitting" aria-hidden="true" />
                        </button>
                    </div>
                </form>

                <p class="site-enter mt-8 text-[0.95rem] text-site-muted" style="--enter-index: 7">
                    ¿Todavía no tienes tu invitación?
                    <a href="{{ route('home') }}#precios" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">Ver paquetes</a>
                </p>

                @if(app()->environment('local'))
                    <p class="mt-8 rounded-[12px] border border-dashed border-site-line px-4 py-3 text-sm text-site-muted">
                        Solo en local: admin@test.com o cliente@test.com, contraseña «password».
                    </p>
                @endif
            </div>

            <p class="text-sm text-site-muted">© {{ now()->year }} {{ config('bida.brand') }}</p>
        </div>

        <div class="hidden p-4 lg:flex" aria-hidden="true">
            <div class="site-stage size-full" data-rotator-group>
                @foreach($showcase as $index => $event)
                    <x-site.image :key="$event['image']" :priority="$index === 0" :class="$index === 0 ? 'is-active' : ''" />
                @endforeach
            </div>
        </div>
    </main>
@endsection
