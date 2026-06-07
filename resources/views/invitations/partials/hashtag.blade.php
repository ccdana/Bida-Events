@php
    $tag = $hashtag['hashtag'] ?? '#Evento';
    $platform = $hashtag['plataforma'] ?? 'instagram';
    $searchUrl = $platform === 'tiktok'
        ? 'https://www.tiktok.com/search?q=' . urlencode($tag)
        : 'https://www.instagram.com/explore/tags/' . ltrim($tag, '#') . '/';
@endphp
<section class="invitation-section py-12 px-6 z-10 relative text-center">
    <a href="{{ $searchUrl }}" target="_blank" rel="noopener"
        class="inline-block px-8 py-4 rounded-2xl border-2 border-primary/40 hover:bg-primary/5 transition">
        <p class="text-xs uppercase tracking-widest opacity-60 mb-2">{{ $hashtag['texto_boton'] ?? 'Comparte tus fotos' }}</p>
        <p class="font-title text-2xl text-primary">{{ $tag }}</p>
    </a>
</section>
