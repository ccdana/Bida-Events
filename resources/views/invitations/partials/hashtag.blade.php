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
            'eyebrow' => $invCopy['hashtag_eyebrow'] ?? __('Redes sociales'),
            'title' => $hashtag['texto_boton'] ?? __('Usa nuestro hashtag'),
            'intro' => str_replace(':red', $platformLabel, $invCopy['hashtag_intro'] ?? __('Publica tus fotos y videos en :red con esta etiqueta para verlos todos juntos.')),
        ])

        {{-- Un salto posible antes de cada mayúscula (#IsabellaXV → #Isabella·XV): si el hashtag es largo, se parte por palabras y no a la mitad de una --}}
        <p class="inv-hashtag__tag" x-data="fitText()" x-init="fit()" @resize.window.debounce.150ms="fit()">
            <span class="inv-hashtag__text" x-ref="text">{!! preg_replace('/(?<=[\p{Ll}\d])(?=\p{Lu})|(?<=\p{Lu})(?=\p{Lu}\p{Ll})/u', '<wbr>', e($tag)) !!}</span>
        </p>

        <div class="inv-actions inv-actions--split">
            <button type="button" class="inv-btn" @click="copy(@js($tag))">
                <span x-text="copied ? '¡Copiado!' : @js($invCopy['hashtag_copy'] ?? __('Copiar hashtag'))">{{ $invCopy['hashtag_copy'] ?? __('Copiar hashtag') }}</span>
            </button>
            <a href="{{ $searchUrl }}" target="_blank" rel="noopener" class="inv-btn inv-btn--ghost">{{ __('Ver en :platformLabel', ['platformLabel' => $platformLabel]) }}</a>
        </div>
    </div>
</section>
<script>
// El hashtag va en una sola línea: si no cabe, la letra se reduce hasta caber. Si para eso quedaría
// demasiado chica (hashtags muy largos), pasa a varias líneas, partido por palabras, con letra legible.
function fitText() {
    return {
        fit() {
            const text = this.$refs.text;
            // El navegador parte en cada <wbr> aunque la línea sea «nowrap»: se mide sin ellos y solo se
            // usan cuando el hashtag pasa a varias líneas
            const plain = text.textContent;
            const breakable = text.innerHTML;

            const run = () => {
                const box = this.$el;

                text.textContent = plain;
                box.style.fontSize = '';
                box.classList.remove('is-wrapped', 'is-broken');

                const style = getComputedStyle(box);
                const base = parseFloat(style.fontSize);
                const available = box.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
                const needed = text.offsetWidth;

                if (!needed || needed <= available) return;

                const single = base * (available / needed) * 0.97;
                const floor = Math.max(24, base * 0.62);

                if (single >= floor) {
                    box.style.fontSize = `${single}px`;
                    return;
                }

                // Varias líneas: se parte en las mayúsculas y, si una sola palabra aún no cabe, se reduce hasta que quepa
                box.classList.add('is-wrapped');
                text.innerHTML = breakable;
                box.style.fontSize = `${floor}px`;

                const longest = box.scrollWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);

                if (longest > available) {
                    const fitted = floor * (available / longest) * 0.97;

                    box.style.fontSize = `${Math.max(16, fitted)}px`;
                    box.classList.toggle('is-broken', fitted < 16);
                }
            };

            run();
            document.fonts?.ready.then(run);
        },
    };
}
</script>
