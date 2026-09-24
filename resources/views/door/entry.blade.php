@extends('door.layout')

{{--
    Resultado de un pase en la puerta. Tres estados, de un vistazo y a la distancia:
    - puede pasar (verde), con cuántas personas entran ahora;
    - pasa, pero no confirmó asistencia (ámbar): decide el personal;
    - ya entró todo el pase (rojo), con la hora del primer ingreso.
--}}
@php
    $arrivedAll = $remaining === 0;
    $confirmed = $guest->status === 'confirmed';
    $state = $arrivedAll ? 'used' : ($confirmed ? 'ok' : 'warn');
@endphp

@section('title', $guest->name)

@section('content')
    <section class="door-result is-{{ $state }}" x-data="{ people: {{ $remaining }} }">
        @if(session('door_success'))
            <p class="door-alert is-ok" role="status">{{ session('door_success') }}</p>
        @endif
        @if(session('door_error'))
            <p class="door-alert is-error" role="alert">{{ session('door_error') }}</p>
        @endif

        <p class="door-result__state">
            @if($state === 'used')
                <x-phosphor-x-circle-fill aria-hidden="true" />
                Pase ya usado
            @elseif($state === 'ok')
                <x-phosphor-check-circle-fill aria-hidden="true" />
                Puede pasar
            @else
                <x-phosphor-warning-circle-fill aria-hidden="true" />
                No confirmó asistencia
            @endif
        </p>

        <h1 class="door-result__name">{{ $guest->name }}</h1>

        <dl class="door-result__facts">
            <div>
                <dt>{{ $confirmed ? 'Confirmó' : 'Invitados' }}</dt>
                <dd>{{ $expected }} {{ $expected === 1 ? 'persona' : 'personas' }}</dd>
            </div>
            <div>
                <dt>Ya ingresaron</dt>
                <dd>{{ $guest->checked_in_passes }}</dd>
            </div>
            @if($guest->table_number)
                <div>
                    <dt>Mesa</dt>
                    <dd>{{ $guest->table_number }}</dd>
                </div>
            @endif
        </dl>

        @if($guest->checked_in_at)
            <p class="door-hint">Primer ingreso: {{ $guest->checked_in_at->timezone(config('app.timezone'))->format('H:i') }}</p>
        @endif

        @unless($arrivedAll)
            <form method="POST" action="{{ route('door.check-in', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]) }}" class="door-checkin">
                @csrf
                <p class="door-checkin__label" id="door-people-label">¿Cuántas personas entran ahora?</p>
                <div class="door-stepper" role="group" aria-labelledby="door-people-label">
                    <button type="button" class="door-stepper__btn" @click="people = Math.max(1, people - 1)" :disabled="people <= 1" aria-label="Una persona menos">−</button>
                    <output class="door-stepper__value" x-text="people">{{ $remaining }}</output>
                    <button type="button" class="door-stepper__btn" @click="people = Math.min({{ $remaining }}, people + 1)" :disabled="people >= {{ $remaining }}" aria-label="Una persona más">+</button>
                </div>
                <input type="hidden" name="people" :value="people" value="{{ $remaining }}">
                <button type="submit" class="site-btn site-btn--lg door-checkin__submit">
                    Registrar ingreso
                </button>
            </form>
        @endunless

        <div class="door-actions">
            <a href="{{ route('door.open', $doorToken) }}" class="site-btn site-btn--ghost site-btn--lg">
                <x-phosphor-qr-code aria-hidden="true" />
                Escanear otro pase
            </a>
            @if($guest->checked_in_passes > 0)
                <form method="POST" action="{{ route('door.undo', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]) }}"
                    onsubmit="return confirm('¿Deshacer el ingreso de este pase?')">
                    @csrf
                    <button type="submit" class="door-undo">Deshacer el ingreso</button>
                </form>
            @endif
        </div>
    </section>
@endsection
