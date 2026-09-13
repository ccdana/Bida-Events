@php
    $tag = $hashtag['hashtag'] ?? '#Evento';
    $isTiktok = ($hashtag['plataforma'] ?? 'instagram') === 'tiktok';
    $cleanTag = trim(ltrim($tag, '#'));
    $platformLabel = $isTiktok ? 'TikTok' : 'Instagram';
    $searchUrl = $isTiktok
        ? 'https://www.tiktok.com/tag/' . rawurlencode($cleanTag)
        : 'https://www.instagram.com/explore/tags/' . rawurlencode($cleanTag) . '/';
@endphp

<section class="inv-section reveal inv-hashtag" id="hashtag" x-data="copyButton()">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'lottie' => 'hashtag',
            'eyebrow' => 'Redes sociales',
            'title' => $hashtag['texto_boton'] ?? 'Usa nuestro hashtag',
            'intro' => "Publica tus fotos y videos en {$platformLabel} con esta etiqueta para verlos todos juntos.",
        ])

        <p class="inv-hashtag__tag">{{ $tag }}</p>

        <div class="inv-actions inv-actions--split">
            <button type="button" class="inv-btn" @click="copy(@js($tag))">
                <span x-text="copied ? '¡Copiado!' : 'Copiar hashtag'">Copiar hashtag</span>
            </button>
            <a href="{{ $searchUrl }}" target="_blank" rel="noopener" class="inv-btn inv-btn--ghost">Ver en {{ $platformLabel }}</a>
        </div>
    </div>
</section>
