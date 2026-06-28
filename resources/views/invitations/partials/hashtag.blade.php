@php
    $tag = $hashtag['hashtag'] ?? '#Evento';
    $platform = $hashtag['plataforma'] ?? 'instagram';
    $cleanTag = trim(ltrim($tag, '#'));
    $searchUrl = $platform === 'tiktok'
        ? 'https://www.tiktok.com/tag/' . rawurlencode($cleanTag)
        : 'https://www.instagram.com/explore/tags/' . rawurlencode($cleanTag) . '/';
@endphp
<section class="invitation-section reveal text-center">
    <div class="section-inner">
        <header class="section-header">
            <span class="section-eyebrow">Redes sociales</span>
            <h2 class="section-title">Comparte el momento</h2>
            <div class="section-ornament"></div>
        </header>
        <a href="{{ $searchUrl }}" target="_blank" rel="noopener"
            class="inline-block px-10 py-5 rounded-2xl glass-card hover:scale-[1.02] active:scale-[0.98] transition-transform">
            <p class="text-[10px] uppercase tracking-widest opacity-50 mb-2">{{ $hashtag['texto_boton'] ?? 'Usa nuestro hashtag' }}</p>
            <p class="font-title text-2xl text-primary">{{ $tag }}</p>
        </a>
    </div>
</section>
