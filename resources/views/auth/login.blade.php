@extends('layouts.auth')

@section('title', __('Ingresar').' | '.config('bida.brand'))
@section('description', __('Ingresa a tu cuenta de :brand para administrar tu invitación, tus invitados y las confirmaciones de tu evento.', ['brand' => config('bida.brand')]))
@section('robots', 'noindex, nofollow')

@section('auth')
    @php($showcase = config('bida.showcase'))
    {{-- Recordar la sesión viene marcado; si el intento falló, se respeta lo que la persona dejó --}}
    @php($remember = session()->hasOldInput() ? (bool) old('remember') : true)

    <h1 class="site-enter site-display site-display--md" style="--enter-index: 1">{{ __('Ingresa a tu cuenta') }}</h1>
    {{-- La frase cambia junto con las fotos del panel derecho (data-rotator en layouts.auth) --}}
    <p class="site-enter mt-2 text-site-muted" style="--enter-index: 2">
        <span class="sr-only">{{ __('Administra tu invitación y las confirmaciones de tu evento.') }}</span>
        <span aria-hidden="true">
            {{ __('Tu panel para') }}
            <span class="site-rotator font-medium text-site-ink" data-rotator-group>
                @foreach($showcase as $index => $event)
                    <span @class(['is-active' => $index === 0])>{{ $event['phrase'] }}</span>
                @endforeach
            </span>
        </span>
    </p>

    @include('auth.partials.status')

    <form method="POST" action="{{ lroute('login') }}" @class(['mt-5 grid gap-4', 'site-shake' => $errors->any()])
        x-data="{ showPassword: false, submitting: false, capsLock: false }" @submit="submitting = true">
        @csrf

        <div class="site-enter grid gap-1" style="--enter-index: 4">
            <label for="username" class="site-label">{{ __('Usuario o correo') }}</label>
            <div class="site-field">
                <x-phosphor-user-light class="site-field__icon" aria-hidden="true" />
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                    autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="{{ __('maria.valenzuela o tu correo') }}"
                    @class(['site-input', 'is-invalid' => $errors->has('username')])
                    @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>
            </div>
            @error('username')
                <p id="username-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
            @enderror
        </div>

        @include('auth.partials.password', ['index' => 5])

        {{-- Debajo de la contraseña, en una línea: recordar la sesión (solo ícono) y la ayuda si se olvidó --}}
        <div class="site-enter flex items-center justify-between gap-4" style="--enter-index: 6">
            <label class="site-remember site-touch-row" title="{{ __('Mantener la sesión iniciada') }}">
                <input type="checkbox" name="remember" value="1" aria-label="{{ __('Mantener la sesión iniciada') }}" @checked($remember)>
                <span class="site-remember__box"><x-phosphor-check-bold aria-hidden="true" /></span>
                <x-phosphor-devices-light class="site-remember__icon" aria-hidden="true" />
            </label>
            <a href="{{ lroute('password.request') }}" class="site-touch-y text-sm text-site-muted underline underline-offset-4 transition-colors hover:text-site-ink">{{ __('¿La olvidaste?') }}</a>
        </div>

        <div class="site-enter" style="--enter-index: 7">
            <button type="submit" class="site-btn site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
                <span x-text="submitting ? @js(__('Ingresando')) : @js(__('Ingresar'))">{{ __('Ingresar') }}</span>
                <x-phosphor-arrow-right class="site-btn__arrow" x-show="!submitting" aria-hidden="true" />
            </button>
        </div>
    </form>

    @include('auth.partials.google', ['index' => 8])

    @if(\App\Support\AuthOptions::registrationOpen())
        <p class="site-enter mt-4 flex justify-center" style="--enter-index: 9">
            <a href="{{ lroute('register') }}" class="site-help">
                <x-phosphor-user-plus-light aria-hidden="true" />
                {{ __('Crear cuenta') }}
            </a>
        </p>
    @endif

    @if(app()->environment('local'))
        <p class="mt-4 rounded-[12px] border border-dashed border-site-line px-4 py-3 text-sm text-site-muted">
            {{ __('Solo en local: usuario «admin» o «cliente», contraseña «password».') }}
        </p>
    @endif
@endsection