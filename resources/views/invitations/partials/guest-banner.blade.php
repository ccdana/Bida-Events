@php
    $passes = (int) ($guest?->passes_allocated ?? 1);
    $status = $guest?->status ?: 'pending';
    $statusLabel = [
        'confirmed' => 'Confirmada',
        'declined' => 'No asistirá',
    ][$status] ?? 'Pendiente';
@endphp

<section class="inv-section reveal inv-guest" id="guest-banner">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'lottie' => 'invitation',
            'eyebrow' => 'Esta invitación es para',
            'title' => $guest?->name ?? 'Invitado especial',
        ])

        <dl class="inv-guest__facts">
            <div>
                <dt class="inv-label">Pases</dt>
                <dd>{{ $passes }} {{ $passes === 1 ? 'persona' : 'personas' }}</dd>
            </div>
            <div>
                <dt class="inv-label">Asistencia</dt>
                <dd>{{ $statusLabel }}</dd>
            </div>
        </dl>

        @if($status === 'pending')
            <div class="inv-actions">
                <a href="#rsvp" class="inv-btn inv-btn--block">Confirmar asistencia</a>
                <p class="inv-help">Te toma menos de un minuto y nos ayuda a organizar la noche.</p>
            </div>
        @endif
    </div>
</section>
