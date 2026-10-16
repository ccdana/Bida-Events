@extends('layouts.auth')

@section('title', __('Crea tu cuenta').' | '.config('bida.brand'))

@section('auth')
    {{-- Con un plan elegido, el recorrido del plan; si no, el de «Hazlo tú» (cuenta, invitación, publicar) --}}
    @if($chosenPlan)
        <x-site.journey :current="2" class="site-enter mb-8" style="--enter-index: 0" />
    @else
        <x-site.journey flow="diy" :current="1" class="site-enter mb-8" style="--enter-index: 0" />
    @endif

    <h1 class="site-enter text-3xl font-semibold tracking-tight sm:text-4xl" style="--enter-index: 1">{{ __('Crea tu cuenta') }}</h1>
    @if($chosenPlan)
        {{-- Quien eligió un plan en «Únete» lo ve aquí: sabe qué va a pagar después de confirmar su correo --}}
        <p class="site-enter mt-3 leading-relaxed text-site-muted" style="--enter-index: 2">
            {{ __('Tu plan:') }} <span class="font-medium text-site-ink">{{ $chosenPlan['name'] }}</span>{{ __(', :price al mes · :quota.', ['price' => $chosenPlan['price'], 'quota' => $chosenPlan['quota']]) }}
            <a href="{{ lroute('diy') }}#planes" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">{{ __('Cambiar') }}</a>
        </p>
        <p class="site-enter mt-2 text-sm leading-relaxed text-site-muted" style="--enter-index: 2">
            {{ __('Creas tu cuenta, confirmas tu correo y pagas el plan. Después armas tu invitación.') }}
        </p>
    @else
        <p class="site-enter mt-3 leading-relaxed text-site-muted" style="--enter-index: 2">
            {{ __('Con tu cuenta armas tu invitación gratis con nuestras plantillas. Pagas solo si al publicarla quieres más que lo básico.') }}
        </p>
    @endif

    @include('auth.partials.google', ['index' => 3])

    <form method="POST" action="{{ lroute('register') }}" @class(['grid gap-6', 'mt-10' => ! \App\Support\AuthOptions::googleEnabled(), 'mt-6' => \App\Support\AuthOptions::googleEnabled(), 'site-shake' => $errors->any()])
        x-data="{ showPassword: false, submitting: false, capsLock: false }" @submit="submitting = true">
        @csrf

        <div class="site-enter grid gap-2" style="--enter-index: 4">
            <label for="name" class="text-[0.95rem] font-medium">{{ __('Tu nombre') }}</label>
            <div class="site-field">
                <x-phosphor-user class="site-field__icon" aria-hidden="true" />
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus maxlength="120"
                    autocomplete="name" placeholder="{{ __('María Valenzuela') }}"
                    @class(['site-input', 'is-invalid' => $errors->has('name')])
                    @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            </div>
            @error('name')
                <p id="name-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="site-enter grid gap-2" style="--enter-index: 5">
            <label for="email" class="text-[0.95rem] font-medium">{{ __('Correo') }}</label>
            <div class="site-field">
                <x-phosphor-envelope-simple class="site-field__icon" aria-hidden="true" />
                <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255"
                    autocomplete="email" autocapitalize="none" spellcheck="false" placeholder="{{ __('maria@correo.com') }}"
                    @class(['site-input', 'is-invalid' => $errors->has('email')])
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            </div>
            @error('email')
                <p id="email-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
            @enderror
        </div>

        @include('auth.partials.password', ['index' => 6, 'label' => __('Elige una contraseña'), 'autocomplete' => 'new-password', 'hint' => __('Al menos 8 caracteres.')])

        <div class="site-enter mt-2 grid gap-4" style="--enter-index: 7">
            <button type="submit" class="site-btn site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
                <span x-text="submitting ? @js(__('Creando tu cuenta')) : @js(__('Crear mi cuenta'))">{{ __('Crear mi cuenta') }}</span>
                <x-phosphor-arrow-right class="site-btn__arrow" x-show="!submitting" aria-hidden="true" />
            </button>
            <p class="text-sm leading-relaxed text-site-muted">
                {{ __('Al crear tu cuenta aceptas los') }}
                <a href="{{ lroute('legal', 'terminos') }}" class="underline underline-offset-4 transition-colors hover:text-site-ink">{{ __('términos') }}</a>
                {{ __('y la') }}
                <a href="{{ lroute('legal', 'privacidad') }}" class="underline underline-offset-4 transition-colors hover:text-site-ink">{{ __('política de privacidad') }}</a>.
            </p>
        </div>
    </form>

    <p class="site-enter mt-8 text-[0.95rem] text-site-muted" style="--enter-index: 8">
        {{ __('¿Ya tienes cuenta?') }}
        <a href="{{ lroute('login') }}" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">{{ __('Ingresa') }}</a>
    </p>
@endsection
