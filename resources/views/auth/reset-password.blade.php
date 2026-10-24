@extends('layouts.auth')

@section('title', __('Contraseña nueva').' | '.config('bida.brand'))
@section('description', __('Crea una contraseña nueva para entrar a tu cuenta de :brand.', ['brand' => config('bida.brand')]))
@section('robots', 'noindex, nofollow')

@section('auth')
    <h1 class="site-enter site-display site-display--md" style="--enter-index: 1">{{ __('Crea una contraseña nueva') }}</h1>
    <p class="site-enter mt-2 text-site-muted" style="--enter-index: 2">
        {{ __('Al guardarla se cierran todas tus sesiones abiertas, y entras con la nueva.') }}
    </p>

    <form method="POST" action="{{ lroute('password.store') }}" @class(['mt-5 grid gap-4', 'site-shake' => $errors->any()])
        x-data="{ showPassword: false, submitting: false, capsLock: false }" @submit="submitting = true">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="site-enter grid gap-1" style="--enter-index: 3">
            <label for="email" class="site-label">{{ __('Correo') }}</label>
            <div class="site-field">
                <x-phosphor-envelope-simple-light class="site-field__icon" aria-hidden="true" />
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required maxlength="255"
                    autocomplete="username" autocapitalize="none" spellcheck="false"
                    @class(['site-input', 'is-invalid' => $errors->has('email')])
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            </div>
            @error('email')
                <p id="email-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
                <p><a href="{{ lroute('password.request') }}" class="site-help"><x-phosphor-arrow-u-up-left-light aria-hidden="true" /> {{ __('Pedir otro enlace') }}</a></p>
            @enderror
        </div>

        @include('auth.partials.password', ['index' => 4, 'label' => __('Contraseña nueva'), 'autocomplete' => 'new-password', 'hint' => __('Mínimo 8, con letras y números.')])

        <div class="site-enter" style="--enter-index: 5">
            <button type="submit" class="site-btn site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
                <span x-text="submitting ? @js(__('Guardando')) : @js(__('Guardar la contraseña'))">{{ __('Guardar la contraseña') }}</span>
                <x-phosphor-arrow-right class="site-btn__arrow" x-show="!submitting" aria-hidden="true" />
            </button>
        </div>
    </form>
@endsection