@extends('layouts.auth')

@section('title', __('Recuperar la contraseña').' | '.config('bida.brand'))
@section('description', __('Recupera el acceso a tu cuenta de :brand: escribe tu correo y te enviamos un enlace para crear una contraseña nueva.', ['brand' => config('bida.brand')]))
@section('robots', 'noindex, nofollow')

@section('auth')
    <h1 class="site-enter text-3xl font-semibold tracking-tight sm:text-4xl" style="--enter-index: 1">{{ __('¿Olvidaste tu contraseña?') }}</h1>
    <p class="site-enter mt-3 leading-relaxed text-site-muted" style="--enter-index: 2">
        {{ __('Escribe el correo de tu cuenta y te enviamos un enlace para crear una nueva.') }}
    </p>

    @include('auth.partials.status')

    <form method="POST" action="{{ lroute('password.email') }}" @class(['mt-10 grid gap-6', 'site-shake' => $errors->any()])
        x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div class="site-enter grid gap-2" style="--enter-index: 4">
            <label for="email" class="text-[0.95rem] font-medium">{{ __('Correo') }}</label>
            <div class="site-field">
                <x-phosphor-envelope-simple class="site-field__icon" aria-hidden="true" />
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus maxlength="255"
                    autocomplete="email" autocapitalize="none" spellcheck="false" placeholder="{{ __('maria@correo.com') }}"
                    @class(['site-input', 'is-invalid' => $errors->has('email')])
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            </div>
            @error('email')
                <p id="email-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
            @enderror
        </div>

        <div class="site-enter mt-2" style="--enter-index: 5">
            <button type="submit" class="site-btn site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
                <span x-text="submitting ? @js(__('Enviando')) : @js(__('Enviarme el enlace'))">{{ __('Enviarme el enlace') }}</span>
                <x-phosphor-arrow-right class="site-btn__arrow" x-show="!submitting" aria-hidden="true" />
            </button>
        </div>
    </form>

    <div class="site-enter mt-8 grid gap-2 text-[0.95rem] text-site-muted" style="--enter-index: 6">
        <p>
            {{ __('¿Te la dio nuestro equipo y tu cuenta no tiene correo?') }}
            <a href="{{ lroute('home') }}#contacto" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">{{ __('Escríbenos') }}</a>
            {{ __('y te damos una nueva.') }}
        </p>
        <p>
            <a href="{{ lroute('login') }}" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">{{ __('Volver a ingresar') }}</a>
        </p>
    </div>
@endsection
