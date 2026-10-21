@extends('layouts.auth')

@section('title', __('Ingresar').' | '.config('bida.brand'))

@section('auth')
    @php($showcase = config('bida.showcase'))

    <h1 class="site-enter text-3xl font-semibold tracking-tight sm:text-4xl" style="--enter-index: 1">{{ __('Ingresa a tu cuenta') }}</h1>
    {{-- La frase cambia junto con las fotos del panel derecho (data-rotator en layouts.auth) --}}
    <p class="site-enter mt-3 leading-relaxed text-site-muted" style="--enter-index: 2">
        <span class="sr-only">{{ __('Administra tu invitación y las confirmaciones de tu evento.') }}</span>
        <span aria-hidden="true">
            {{ __('Administra tu invitación y las confirmaciones de') }}
            <span class="site-rotator font-medium text-site-ink" data-rotator-group>
                @foreach($showcase as $index => $event)
                    <span @class(['is-active' => $index === 0])>{{ $event['phrase'] }}</span>
                @endforeach
            </span>
        </span>
    </p>

    @include('auth.partials.status')
    @include('auth.partials.google', ['index' => 3])

    <form method="POST" action="{{ lroute('login') }}" @class(['grid gap-6', 'mt-10' => ! \App\Support\AuthOptions::googleEnabled(), 'mt-6' => \App\Support\AuthOptions::googleEnabled(), 'site-shake' => $errors->any()])
        x-data="{ showPassword: false, submitting: false, capsLock: false }" @submit="submitting = true">
        @csrf

        <div class="site-enter grid gap-2" style="--enter-index: 4">
            <label for="username" class="text-[0.95rem] font-medium">{{ __('Usuario o correo') }}</label>
            <div class="site-field">
                <x-phosphor-user class="site-field__icon" aria-hidden="true" />
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                    autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="{{ __('maria.valenzuela o tu correo') }}"
                    @class(['site-input', 'is-invalid' => $errors->has('username')])
                    @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>
            </div>
            @error('username')
                <p id="username-error" class="site-enter text-sm text-site-danger">{{ $message }}</p>
            @enderror
        </div>

        @include('auth.partials.password', ['index' => 5, 'forgot' => true])

        <label class="site-enter site-touch-row flex cursor-pointer items-center gap-3 text-[0.95rem]" style="--enter-index: 6">
            <input type="checkbox" name="remember" value="1" class="site-checkbox" @checked(old('remember'))>
            {{ __('Mantener la sesión iniciada') }}
        </label>

        <div class="site-enter mt-2" style="--enter-index: 7">
            <button type="submit" class="site-btn site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
                <span x-text="submitting ? @js(__('Ingresando')) : @js(__('Ingresar'))">{{ __('Ingresar') }}</span>
                <x-phosphor-arrow-right class="site-btn__arrow" x-show="!submitting" aria-hidden="true" />
            </button>
        </div>
    </form>

    <div class="site-enter mt-8 grid gap-2 text-[0.95rem] text-site-muted" style="--enter-index: 8">
        @if(\App\Support\AuthOptions::registrationOpen())
            <p>
                {{ __('¿Primera vez aquí?') }}
                <a href="{{ lroute('register') }}" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">{{ __('Crea tu cuenta') }}</a>
            </p>
        @endif
        <p>
            {{ __('¿Todavía no tienes tu invitación?') }}
            <a href="{{ lroute('pricing') }}" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">{{ __('Ver precios') }}</a>
        </p>
    </div>

    @if(app()->environment('local'))
        <p class="mt-8 rounded-[12px] border border-dashed border-site-line px-4 py-3 text-sm text-site-muted">
            {{ __('Solo en local: usuario «admin» o «cliente», contraseña «password».') }}
        </p>
    @endif
@endsection
