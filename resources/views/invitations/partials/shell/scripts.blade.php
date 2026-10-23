<script>
// Textos de los módulos de JavaScript (resources/js/i18n.js) en el idioma de la invitación
window.bidaStrings = @js([
    'mic_blocked' => __('El micrófono está bloqueado para esta página. Actívalo en los permisos del navegador (el candado junto a la dirección) o desliza hacia arriba para soplar.'),
    'mic_dismissed' => __('No se dio permiso para usar el micrófono. Tócalo otra vez y elige «Permitir», o desliza hacia arriba para soplar.'),
    'mic_missing' => __('No encontramos un micrófono en este dispositivo. Desliza hacia arriba para soplar.'),
    'mic_busy' => __('Otra aplicación está usando el micrófono. Ciérrala e inténtalo de nuevo, o desliza hacia arriba.'),
    'mic_unsupported' => __('Este navegador no deja usar el micrófono aquí. Desliza hacia arriba para soplar.'),
    'reply_preview' => __('Las respuestas no se envían desde la vista previa.'),
    'reply_failed' => __('No se pudo enviar. Intenta de nuevo.'),
    'reply_offline' => __('No se pudo enviar. Revisa tu conexión e intenta de nuevo.'),
    'reply_on_the_way' => __('Tu respuesta va en camino'),
    'reply_on_the_way_to' => __('Tu respuesta va en camino a :to'),
    'flower_on_the_way' => __(':flower va en camino'),
    'flower_on_the_way_to' => __(':flower va en camino a :to'),
    'scratch_here' => __('Raspa aquí'),
]);

/**
 * Abre Google Calendar priorizando la app nativa en Android;
 * en iOS y escritorio el enlace universal resuelve solo.
 */
function openCalendar(webUrl) {
    if (/android/i.test(navigator.userAgent || '')) {
        window.location.href = webUrl.replace('https://calendar.google.com/calendar/render', 'intent://calendar.google.com/calendar/render')
            + '#Intent;scheme=https;package=com.google.android.calendar;S.browser_fallback_url=' + encodeURIComponent(webUrl) + ';end';
        return;
    }

    window.open(webUrl, '_blank', 'noopener');
}

async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);

        return true;
    } catch (error) {
        // Respaldo para navegadores sin Clipboard API o contextos no seguros
        const field = document.createElement('textarea');
        field.value = text;
        field.setAttribute('readonly', '');
        field.style.position = 'fixed';
        field.style.opacity = '0';
        document.body.appendChild(field);
        field.select();
        const copied = document.execCommand('copy');
        field.remove();

        return copied;
    }
}

// Botón "Copiar" con confirmación temporal; `key` distingue varios botones en un mismo bloque
function copyButton() {
    return {
        copied: null,
        timer: null,
        async copy(text, key = 'default') {
            if (!(await copyToClipboard(text))) {
                return;
            }

            this.copied = key;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => { this.copied = null; }, 1800);
        },
    };
}

// Portada: el desvanecido inferior toma el fondo real de lo que viene después, sea la sección que sea
function blendHeroWithNextSection() {
    const hero = document.getElementById('inicio');

    if (!hero) {
        return;
    }

    const next = hero.nextElementSibling?.firstElementChild ?? hero.nextElementSibling;
    const isTransparent = (color) => !color || color === 'transparent' || /rgba\(.*,\s*0\)$/.test(color);
    let color = next ? getComputedStyle(next).backgroundColor : '';

    if (isTransparent(color)) {
        color = getComputedStyle(document.body).backgroundColor;
    }

    hero.style.setProperty('--inv-hero-fade', color);
}

function invitationApp() {
    return {
        init() {
            blendHeroWithNextSection();

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

            document.querySelectorAll('.reveal').forEach((element) => observer.observe(element));
        }
    };
}

// Menú: resalta la sección que ocupa el centro de la pantalla
function invitationNav(sectionIds) {
    return {
        open: false,
        // El botón se esconde al bajar (para no tapar títulos ni botones) y vuelve al subir
        tucked: false,
        active: sectionIds[0] ?? 'inicio',
        // Las invitaciones se pueden recorrer como historia (partials/story/invitation)
        hasStories: false,

        toggle() {
            this.open ? this.close() : this.show();
        },

        show() {
            this.open = true;
            // El primer enlace recibe el foco: con teclado se navega el menú, no lo que quedó detrás
            this.$nextTick(() => this.focusables()[0]?.focus());
        },

        // Al cerrar, el foco vuelve al botón que abrió el menú (salvo al seguir un enlace)
        close(restoreFocus = true) {
            if (!this.open) return;
            this.open = false;
            if (restoreFocus) this.$refs.toggle?.focus();
        },

        focusables() {
            return [...(this.$refs.panel?.querySelectorAll('a[href], button:not([disabled])') ?? [])];
        },

        // Mientras el panel tapa la página, el tabulador da la vuelta dentro de él
        trapFocus(event) {
            if (!this.open) return;

            const items = this.focusables();
            if (!items.length) return;

            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },

        // Baja: se esconde; sube un poco, está cerca del inicio o del final, o hay un panel abierto: se muestra
        trackScroll() {
            let last = window.scrollY;
            let waiting = false;

            window.addEventListener('scroll', () => {
                if (waiting) return;
                waiting = true;

                requestAnimationFrame(() => {
                    const y = window.scrollY;
                    const delta = y - last;
                    const nearEnd = window.innerHeight + y >= document.documentElement.scrollHeight - 240;

                    if (this.open || y < 160 || nearEnd || delta < -8) {
                        this.tucked = false;
                    } else if (delta > 8) {
                        this.tucked = true;
                    }

                    last = y;
                    waiting = false;
                });
            }, { passive: true });
        },

        init() {
            this.hasStories = Boolean(document.querySelector('[data-inv-story]'));
            this.trackScroll();

            if (!('IntersectionObserver' in window)) {
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                // En modo historia las escenas están apiladas: la activa la avisa «inv-story-change»
                if (document.documentElement.classList.contains('inv-story-on')) {
                    return;
                }

                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        this.active = entry.target.id;
                    }
                });
            }, { rootMargin: '-45% 0px -50% 0px' });

            sectionIds.forEach((id) => {
                const section = document.getElementById(id);

                if (section) {
                    observer.observe(section);
                }
            });
        },
    };
}
</script>
