@extends('layouts.auth')

@section('title', 'Confirma tu correo | '.config('bida.brand'))

@section('auth')
    <h1 class="site-enter text-3xl font-semibold tracking-tight sm:text-4xl" style="--enter-index: 1">Confirma tu correo</h1>
    <p class="site-enter mt-3 leading-relaxed text-site-muted" style="--enter-index: 2">
        Te enviamos un enlace a <span class="font-medium text-site-ink">{{ $email }}</span>. Ábrelo desde tu correo y tu cuenta queda lista.
        Si no lo ves en unos minutos, revisa la carpeta de spam.
    </p>

    @include('auth.partials.status')

    <form method="POST" action="{{ route('verification.send') }}" class="site-enter mt-10" style="--enter-index: 4"
        x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <button type="submit" class="site-btn site-btn--ghost site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
            <span x-text="submitting ? 'Enviando' : 'Enviarme otro enlace'">Enviarme otro enlace</span>
        </button>
    </form>

    <div class="site-enter mt-8 grid gap-2 text-[0.95rem] text-site-muted" style="--enter-index: 5">
        <p>
            ¿Escribiste mal el correo?
            <a href="{{ route('client.account') }}" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">Cámbialo en tu cuenta</a>
        </p>
        <p>
            <a href="{{ route('dashboard') }}" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">Ir a mi panel</a>
        </p>
    </div>
@endsection
