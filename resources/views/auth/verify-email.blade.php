@extends('layouts.auth')

@section('title', __('Confirma tu correo').' | '.config('bida.brand'))
@section('description', __('Confirma tu correo para activar tu cuenta de :brand.', ['brand' => config('bida.brand')]))
@section('robots', 'noindex, nofollow')

@section('auth')
    @php($forPlan = str_contains((string) session('url.intended'), '/client/suscripcion'))

    <x-phosphor-envelope-simple-open-light class="site-enter size-9 text-site-accent" style="--enter-index: 0" aria-hidden="true" />
    <h1 class="site-enter site-display site-display--md mt-3" style="--enter-index: 1">{{ __('Confirma tu correo') }}</h1>
    <p class="site-enter mt-2 text-site-muted" style="--enter-index: 2">
        {!! __('Te enviamos un enlace a :email.', ['email' => '<span class="font-medium text-site-ink">'.e($email).'</span>']) !!}
        {{ $forPlan
            ? __('Ábrelo y tu cuenta queda lista: después sigues con el pago de tu plan. Si no llega, revisa el spam.')
            : __('Ábrelo y tu cuenta queda lista. Si no llega, revisa el spam.') }}
    </p>

    @include('auth.partials.status')

    <form method="POST" action="{{ route('verification.send') }}" class="site-enter mt-5" style="--enter-index: 4"
        x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <button type="submit" class="site-btn site-btn--ghost site-btn--lg w-full justify-center" :class="{ 'is-loading': submitting }" :disabled="submitting">
            <x-phosphor-arrow-clockwise-light x-show="!submitting" aria-hidden="true" />
            <span x-text="submitting ? @js(__('Enviando')) : @js(__('Enviarme otro enlace'))">{{ __('Enviarme otro enlace') }}</span>
        </button>
    </form>

    <div class="site-enter mt-4 flex flex-wrap items-center justify-between gap-x-5" style="--enter-index: 5">
        <a href="{{ route('client.account') }}" class="site-help" title="{{ __('¿Escribiste mal el correo? Cámbialo en tu cuenta') }}">
            <x-phosphor-pencil-simple-light aria-hidden="true" />
            {{ __('Cambiar correo') }}
        </a>
        <a href="{{ route('dashboard') }}" class="site-help">
            {{ __('Mi panel') }}
            <x-phosphor-arrow-right-light aria-hidden="true" />
        </a>
    </div>
@endsection