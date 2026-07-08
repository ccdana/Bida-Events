@php
    $guestTitle = $guest?->name ?? 'Invitado Especial';
    $guestSubtitle = $guest?->status === 'pending'
        ? 'Tu asistencia todavía está pendiente'
        : ($guest?->status === 'confirmed' ? 'Tu asistencia fue confirmada' : 'Celebremos juntos este día');
@endphp

<section class="invitation-section reveal invitation-guest-banner" id="guest-banner">
    <div class="section-inner-wide">
        @include('invitations.partials.lottie-framed-icon', ['name' => 'invitation'])

        <p class="invitation-guest-banner__eyebrow">
            Esta invitación es para
        </p>

        <h2 class="invitation-guest-banner__title">
            {{ $guestTitle }}
        </h2>

        <div class="invitation-guest-banner__divider" aria-hidden="true"></div>

        @if($guest)
            <p class="invitation-guest-banner__subtitle">
                {{ $guestSubtitle }}
            </p>
        @else
            <p class="invitation-guest-banner__subtitle">
                La experiencia está pensada para una persona muy especial
            </p>
        @endif

    </div>
</section>
