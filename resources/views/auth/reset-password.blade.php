@extends('layouts.auth')

@section('title', 'Contraseña nueva | '.config('bida.brand'))

@section('auth')
    <h1 class="site-enter text-3xl font-semibold tracking-tight sm:text-4xl" style="--enter-index: 1">Crea una contraseña nueva</h1>
    <p class="site-enter mt-3 leading-relaxed text-site-muted" style="--enter-index: 2">
        Al guardarla se cierran todas tus sesiones abiertas, y entras con la nueva.
    </p>

    <form method="POST" action="{{ route('password.store') }}" @class(['mt-10 grid gap-6', 'site-shake' => $errors->any()])
        x-data="{ showPassword: false, submitting: false, capsLock: false }" @submit="submitting = true">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="site-enter grid gap-2" style="--enter-index: 3">
            <label for="email" class="text-[0.95rem] font-medium">Correo de tu cuenta</label>
            <div class="site-field">
                <x-phosphor-envelope-simple class="site-field__icon" aria-hidden="true" />
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required maxlength="255"
                    autocomplete="username" autocapitalize="none" spellcheck="false"
                    @class(['site-input', 'is-invalid' => $errors->has('email')])
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            </div>
            @error('email')
                <p id="email-error" class="site-enter text-sm text-site-danger">
                    {{ $message }}
                    <a href="{{ route('password.request') }}" class="font-medium underline underline-offset-4">Pedir otro enlace</a>
                </p>
            @enderror
        </div>

        @include('auth.partials.password', ['index' => 4, 'label' => 'Contraseña nueva', 'autocomplete' => 'new-password', 'hint' => 'Al menos 8 caracteres.'])

        <div class="site-enter mt-2" style="--enter-index: 5">
            <button type="submit" class="site-btn site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
                <span x-text="submitting ? 'Guardando' : 'Guardar la contraseña'">Guardar la contraseña</span>
                <x-phosphor-arrow-right class="site-btn__arrow" x-show="!submitting" aria-hidden="true" />
            </button>
        </div>
    </form>
@endsection
