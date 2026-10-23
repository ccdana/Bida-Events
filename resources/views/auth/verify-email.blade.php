@extends('layouts.auth')

@section('title', __('Confirma tu correo').' | '.config('bida.brand'))
@section('description', __('Confirma tu correo para activar tu cuenta de :brand.', ['brand' => config('bida.brand')]))
@section('robots', 'noindex, nofollow')

@section('auth')
    @if(str_contains((string) session('url.intended'), '/client/suscripcion'))
        <x-site.journey :current="2" class="site-enter mb-8" style="--enter-index: 0" />
    @else
        <x-site.journey flow="diy" :current="1" class="site-enter mb-8" style="--enter-index: 0" />
    @endif

    <h1 class="site-enter text-3xl font-semibold tracking-tight sm:text-4xl" style="--enter-index: 1">{{ __('Confirma tu correo') }}</h1>
    <p class="site-enter mt-3 leading-relaxed text-site-muted" style="--enter-index: 2">
        {!! __('Te enviamos un enlace a :email.', ['email' => '<span class="font-medium text-site-ink">'.e($email).'</span>']) !!}
        {{ str_contains((string) session('url.intended'), '/client/suscripcion')
            ? __('Ábrelo desde tu correo y tu cuenta queda lista: después sigues con el pago de tu plan. Si no lo ves en unos minutos, revisa la carpeta de spam.')
            : __('Ábrelo desde tu correo y tu cuenta queda lista. Si no lo ves en unos minutos, revisa la carpeta de spam.') }}
    </p>

    @include('auth.partials.status')

    <form method="POST" action="{{ route('verification.send') }}" class="site-enter mt-10" style="--enter-index: 4"
        x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <button type="submit" class="site-btn site-btn--ghost site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
            <span x-text="submitting ? @js(__('Enviando')) : @js(__('Enviarme otro enlace'))">{{ __('Enviarme otro enlace') }}</span>
        </button>
    </form>

    <div class="site-enter mt-8 grid gap-2 text-[0.95rem] text-site-muted" style="--enter-index: 5">
        <p>
            {{ __('¿Escribiste mal el correo?') }}
            <a href="{{ route('client.account') }}" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">{{ __('Cámbialo en tu cuenta') }}</a>
        </p>
        <p>
            <a href="{{ route('dashboard') }}" class="font-medium text-site-ink underline underline-offset-4 transition-colors hover:text-site-accent">{{ __('Ir a mi panel') }}</a>
        </p>
    </div>
@endsection
