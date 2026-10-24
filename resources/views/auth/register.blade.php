@extends('layouts.auth')

@section('title', __('Crea tu cuenta').' | '.config('bida.brand'))
@section('description', __('Crea tu cuenta gratis en :brand: arma tu invitación digital con plantillas profesionales y paga solo si quieres más que lo básico.', ['brand' => config('bida.brand')]))

@section('auth')
    <h1 class="site-enter site-display site-display--md" style="--enter-index: 1">{{ __('Crea tu cuenta') }}</h1>
    @if($chosenPlan)
        {{-- Quien eligió un plan en /precios lo ve aquí: sabe qué va a pagar después de confirmar su correo --}}
        <p class="site-enter mt-2 text-site-muted" style="--enter-index: 2">
            {{ __('Tu plan:') }} <span class="font-medium text-site-ink">{{ $chosenPlan['name'] }}</span>{{ __(', :price al mes · :quota.', ['price' => $chosenPlan['price'], 'quota' => $chosenPlan['quota']]) }}
            <a href="{{ lroute('pricing') }}#planes" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">{{ __('Cambiar') }}</a>
        </p>
        <p class="site-enter mt-1 text-sm text-site-muted" style="--enter-index: 2">
            {{ __('Después de confirmar tu correo, pagas el plan.') }}
        </p>
    @else
        <p class="site-enter mt-2 text-site-muted" style="--enter-index: 2">
            {{ __('Gratis para armarla. Pagas solo si al publicarla quieres más que lo básico.') }}
        </p>
    @endif

    {{-- El correo se corrige a la vista: si el dominio es una errata conocida (gmial.com), se ofrece la forma correcta --}}
    <form method="POST" action="{{ lroute('register') }}" @class(['mt-5 grid gap-4', 'site-shake' => $errors->any()])
        x-data="{ showPassword: false, submitting: false, capsLock: false, email: @js(old('email', '')), touched: false, typos: @js(\App\Support\EmailTypos::DOMAINS),
            get suggestion() {
                const at = this.email.trim().toLowerCase().lastIndexOf('@')
                const fixed = at > 0 ? this.typos[this.email.trim().toLowerCase().slice(at + 1)] : null
                return fixed ? this.email.trim().toLowerCase().slice(0, at + 1) + fixed : null
            } }" @submit="submitting = true">
        @csrf

        <div class="site-enter grid gap-1" style="--enter-index: 4">
            <label for="name" class="site-label">{{ __('Nombre') }}</label>
            <div class="site-field">
                <x-phosphor-user-light class="site-field__icon" aria-hidden="true" />
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus minlength="2" maxlength="120"
                    autocomplete="name" placeholder="{{ __('María Valenzuela') }}"
                    @class(['site-input', 'is-invalid' => $errors->has('name')])
                    @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            </div>
            @error('name')
                <p id="name-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="site-enter grid gap-1" style="--enter-index: 5">
            <label for="email" class="site-label">{{ __('Correo') }}</label>
            <div class="site-field">
                <x-phosphor-envelope-simple-light class="site-field__icon" aria-hidden="true" />
                <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255"
                    x-model="email" @blur="touched = true" @input="touched = false"
                    autocomplete="email" autocapitalize="none" spellcheck="false" placeholder="{{ __('maria@correo.com') }}"
                    @class(['site-input', 'is-invalid' => $errors->has('email')])
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            </div>
            @error('email')
                <p id="email-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
                {{-- Un correo que ya tiene cuenta: en vez de explicar, los dos caminos --}}
                @if($message === __('Este correo ya tiene una cuenta.'))
                    <p class="flex flex-wrap gap-x-5">
                        <a href="{{ lroute('login') }}" class="site-help"><x-phosphor-sign-in-light aria-hidden="true" /> {{ __('Ingresar') }}</a>
                        <a href="{{ lroute('password.request') }}" class="site-help"><x-phosphor-lock-key-light aria-hidden="true" /> {{ __('¿Olvidaste tu contraseña?') }}</a>
                    </p>
                @endif
            @enderror
            <p class="flex items-center gap-2 text-sm text-site-muted" x-show="touched && suggestion" x-cloak aria-live="polite">
                <x-phosphor-lightbulb-light class="size-4 shrink-0 text-site-accent" aria-hidden="true" />
                <span>{{ __('¿Quisiste decir') }}
                    <button type="button" class="font-medium text-site-ink underline underline-offset-4" @click="email = suggestion; touched = false" x-text="suggestion"></button>?</span>
            </p>
        </div>

        @include('auth.partials.password', ['index' => 6, 'autocomplete' => 'new-password', 'hint' => __('Mínimo 8, con letras y números.')])

        <div class="site-enter grid gap-3" style="--enter-index: 7">
            <button type="submit" class="site-btn site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
                <span x-text="submitting ? @js(__('Creando tu cuenta')) : @js(__('Crear mi cuenta'))">{{ __('Crear mi cuenta') }}</span>
                <x-phosphor-arrow-right class="site-btn__arrow" x-show="!submitting" aria-hidden="true" />
            </button>
            <p class="text-sm text-site-muted">
                {{ __('Al crear tu cuenta aceptas los') }}
                <a href="{{ lroute('legal', 'terminos') }}" class="underline underline-offset-4 transition-colors hover:text-site-ink">{{ __('términos') }}</a>
                {{ __('y la') }}
                <a href="{{ lroute('legal', 'privacidad') }}" class="underline underline-offset-4 transition-colors hover:text-site-ink">{{ __('política de privacidad') }}</a>.
            </p>
        </div>
    </form>

    @include('auth.partials.google', ['index' => 8])

    <p class="site-enter mt-4 flex justify-center" style="--enter-index: 9">
        <a href="{{ lroute('login') }}" class="site-help">
            <x-phosphor-sign-in-light aria-hidden="true" />
            {{ __('Ingresar') }}
        </a>
    </p>
@endsection