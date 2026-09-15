{{--
    Encabezado común de sección: lottie enmarcado, eyebrow, título, filete y ayuda.
    Uso: @include('invitations.partials.section-header', [
        'lottie' => 'clock', 'eyebrow' => 'El gran día se acerca', 'title' => 'Faltan', 'intro' => 'Texto opcional',
        'compact' => true, // secciones secundarias: sin ícono y con título más pequeño
    ])
--}}
@php
    $headCompact = !empty($compact);
    // Si el título ya dice lo mismo que el eyebrow ("Save the date"), el eyebrow sobra
    $headEyebrow = trim((string) ($eyebrow ?? ''));
    $showHeadEyebrow = $headEyebrow !== '' && mb_strtolower($headEyebrow) !== mb_strtolower(trim((string) $title));
@endphp
<header @class(['inv-head', 'inv-head--compact' => $headCompact])>
    @if(!empty($lottie) && ! $headCompact)
        @include('invitations.partials.lottie-framed-icon', ['name' => $lottie])
    @endif
    @if($showHeadEyebrow)
        <p class="inv-head__eyebrow">{{ $headEyebrow }}</p>
    @endif
    <h2 class="inv-head__title">{{ $title }}</h2>
    <div class="inv-head__rule" aria-hidden="true"></div>
    @if(!empty($intro))
        <p class="inv-head__intro">{{ $intro }}</p>
    @endif
</header>
