{{--
    Encabezado común de sección: lottie enmarcado, eyebrow, título, filete y ayuda.
    Uso: @include('invitations.partials.section-header', [
        'lottie' => 'clock', 'eyebrow' => 'El gran día se acerca', 'title' => 'Faltan', 'intro' => 'Texto opcional',
    ])
--}}
<header class="inv-head">
    @if(!empty($lottie))
        @include('invitations.partials.lottie-framed-icon', ['name' => $lottie])
    @endif
    @if(!empty($eyebrow))
        <p class="inv-head__eyebrow">{{ $eyebrow }}</p>
    @endif
    <h2 class="inv-head__title">{{ $title }}</h2>
    <div class="inv-head__rule" aria-hidden="true"></div>
    @if(!empty($intro))
        <p class="inv-head__intro">{{ $intro }}</p>
    @endif
</header>
