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
            'compact' => true,
            'lottie' => 'hashtag',
            'eyebrow' => $invCopy['hashtag_eyebrow'] ?? 'Redes sociales',
            'title' => $hashtag['texto_boton'] ?? 'Usa nuestro hashtag',
            'intro' => str_replace(':red', $platformLabel, $invCopy['hashtag_intro'] ?? 'Publica tus fotos y videos en :red con esta etiqueta para verlos todos juntos.'),
        ])

        <p class="inv-hashtag__tag" x-data="fitText()" x-init="fit()" @resize.window.debounce.150ms="fit()">
            <span class="inv-hashtag__text" x-ref="text">{{ $tag }}</span>
        </p>

        <div class="inv-actions inv-actions--split">
            <button type="button" class="inv-btn" @click="copy(@js($tag))">
                <span x-text="copied ? '¡Copiado!' : @js($invCopy['hashtag_copy'] ?? 'Copiar hashtag')">{{ $invCopy['hashtag_copy'] ?? 'Copiar hashtag' }}</span>
            </button>
            <a href="{{ $searchUrl }}" target="_blank" rel="noopener" class="inv-btn inv-btn--ghost">Ver en {{ $platformLabel }}</a>
        </div>
    </div>
</section>
<script>
// El hashtag va en una sola línea: si no cabe, la letra se reduce hasta caber (con un mínimo legible)
function fitText() {
    return {
        fit() {
            const run = () => {
                const box = this.$el;
                const text = this.$refs.text;

                box.style.fontSize = '';
                box.classList.remove('is-wrapped');

                const style = getComputedStyle(box);
                const available = box.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
                const needed = text.offsetWidth;

                if (!needed || needed <= available) return;

                const size = parseFloat(style.fontSize) * (available / needed) * 0.97;

                if (size < 18) {
                    box.style.fontSize = '18px';
                    box.classList.add('is-wrapped');
                    return;
                }

                box.style.fontSize = `${size}px`;
            };

            run();
            document.fonts?.ready.then(run);
        },
    };
}
</script>
