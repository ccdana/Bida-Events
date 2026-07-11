const MOBILE_BREAKPOINT = 768;

export function scrollItinerary(totalSteps = 0) {

    // ═══════════════════════════════════════════════════════════════════════
    //  CLOSURE STATE  — completely outside Alpine's reactive system.
    //
    //  Alpine proxies every property on `this`, including DOM nodes, which
    //  breaks native DOM APIs (.style, .getBoundingClientRect, etc.).
    //  Storing everything here keeps DOM refs as real, unwrapped objects.
    //
    //  Additionally, $refs and other Alpine magic properties are ONLY valid
    //  inside Alpine's synchronous reactive context.  RAF / scroll callbacks
    //  run outside that context, so they must never call `this.$refs.*`.
    // ═══════════════════════════════════════════════════════════════════════
    let _trackEl         = null;   // .invitation-itinerary__track
    let _lightEl         = null;   // .invitation-itinerary__spine-light
    let _spineEl         = null;   // .invitation-itinerary__spine
    let _spineProgressEl = null;   // .invitation-itinerary__spine-progress
    let _rafId           = null;
    let _proxy           = null;   // Alpine reactive proxy — used ONLY to set scrollProgress
    const total          = Math.max(Number(totalSteps) || 0, 0);

    const computeNodeActivation = (index, progress) => {
        if (total <= 0) return 0;
        const start = index / total;
        const mid = (index + 0.5) / total;
        if (progress >= mid) return 1;
        if (progress <= start) return 0;
        return (progress - start) / (mid - start);
    };

    const updateHalos = (progress) => {
        if (!_trackEl || !_spineProgressEl || total <= 0) return;

        const halos = _trackEl.querySelectorAll('[data-halo-index]');
        halos.forEach((el) => {
            const index = Number(el.dataset.haloIndex);
            if (!Number.isFinite(index)) return;

            const activation = computeNodeActivation(index, progress);
            const activationValue = activation.toFixed(3);

            el.style.setProperty('--node-activation', activationValue);
            el.classList.toggle('is-looping', activation > 0.1);

            const node = el.closest('[data-itinerary-node]');
            if (!node) return;

            node.style.setProperty('--node-activation', activationValue);

            const panel = node.previousElementSibling;
            if (panel?.classList.contains('invitation-itinerary__panel')) {
                panel.style.setProperty('--node-activation', activationValue);
            }

            const icon = node.querySelector('.invitation-itinerary__node-icon');
            if (icon) {
                icon.style.transform = `scale(${0.88 + (activation * 0.12)})`;
            }

            const item = node.closest('.invitation-itinerary__item');
            if (item) {
                item.classList.toggle('is-active', activation >= 0.55);
                item.classList.toggle('is-done', activation >= 1);
                item.classList.toggle('is-pending', activation < 0.2);
            }
        });

        _spineProgressEl.style.transform = `scaleY(${progress})`;
    };

    // ── Pure scroll math — never references `this` ──────────────────────────
    const computeProgress = () => {
        if (!_trackEl) return 0;
        const vh      = window.innerHeight;
        const rect    = _trackEl.getBoundingClientRect();
        const start   = vh * 0.22;
        const end     = vh * 0.78;
        const range   = Math.max(rect.height - (end - start), 1);
        const traveled = Math.min(Math.max(start - rect.top, 0), range);
        return Math.min(Math.max(traveled / range, 0), 1);
    };

    // ── Direct DOM write — no Alpine involvement ─────────────────────────────
    const moveDot = (progress) => {
        if (!_lightEl || !_spineEl) return;
        const spineH = _spineEl.getBoundingClientRect().height;
        const half   = (_lightEl.offsetHeight || 12) / 2;
        _lightEl.style.top = `${Math.round(progress * spineH - half)}px`;
    };

    // ── RAF tick — runs outside Alpine context ────────────────────────────────
    const tick = () => {
        _rafId = null;
        const p = computeProgress();
        moveDot(p);
        updateHalos(p);
        // The ONLY Alpine touch: update the reactive scrollProgress so that
        // node activation, panel fade and class bindings keep working.
        if (_proxy) _proxy.scrollProgress = p;
    };

    // ── Throttle: at most 1 pending frame per scroll burst ───────────────────
    const schedule = () => {
        if (_rafId) return;
        _rafId = requestAnimationFrame(tick);
    };

    // ── Listeners ─────────────────────────────────────────────────────────────
    let _resizeCb = null;

    // Guaranteed polling loop — runs everyframe regardless of events.
    // This ensures the itinerary is updated even when scroll events are not
    // dispatched or when layout changes are driven by transforms.
    let _loopId = null;

    const loop = () => {
        tick();
        _loopId = requestAnimationFrame(loop);
    };

    const attachListeners = (alpineInstance) => {
        _resizeCb = () => {
            alpineInstance.updateLayout();
            tick();
        };
        // Events as secondary triggers (belt + suspenders)
        window.addEventListener('scroll',                tick,     { passive: true });
        document.documentElement.addEventListener('scroll', tick,     { passive: true });
        window.addEventListener('touchmove',            tick,     { passive: true });
        window.addEventListener('pointermove',          tick,     { passive: true });
        window.addEventListener('resize',               _resizeCb, { passive: true });
        window.addEventListener('orientationchange',    tick,     { passive: true });
        // Primary: polling loop — always fires, zero dependency on scroll events
        _loopId = requestAnimationFrame(loop);
    };

    const detachListeners = () => {
        window.removeEventListener('scroll',    schedule);
        document.documentElement.removeEventListener('scroll', schedule);
        window.removeEventListener('touchmove', schedule);
        if (_resizeCb) window.removeEventListener('resize', _resizeCb);
        if (_rafId)    cancelAnimationFrame(_rafId);
        if (_loopId)   cancelAnimationFrame(_loopId);
        _rafId = _loopId = null;
    };

    // ═══════════════════════════════════════════════════════════════════════
    //  ALPINE DATA OBJECT
    // ═══════════════════════════════════════════════════════════════════════
    return {
        total:          Math.max(Number(totalSteps) || 0, 0),
        scrollProgress: 0,
        isMobile:       true,
        visibleNodes:   {},
        _observers:     [],

        init() {
            // Capture the Alpine proxy once — safe here because init() is
            // called synchronously by Alpine inside its reactive context.
            _proxy = this;

            this.updateLayout();
            attachListeners(this);
            this.observeNodes();

            // $nextTick: Alpine has rendered the template, DOM is ready.
            // $root is valid here (we're still inside Alpine context).
            this.$nextTick(() => {
                const root = this.$root;
                _trackEl = root.querySelector('.invitation-itinerary__track');
                _lightEl = root.querySelector('.invitation-itinerary__spine-light');
                _spineEl = root.querySelector('.invitation-itinerary__spine');
                _spineProgressEl = root.querySelector('.invitation-itinerary__spine-progress');

                // Set initial dot position before the user scrolls
                tick();

                window.initLottieIcons?.();
            });

            // Alpine calls the returned function on component teardown
            return () => {
                detachListeners();
                this._observers.forEach((o) => o.disconnect());
                this._observers = [];
                _proxy = null;
            };
        },

        updateLayout() {
            this.isMobile = window.innerWidth < MOBILE_BREAKPOINT;
        },

        observeNodes() {
            const nodes = this.$root.querySelectorAll('[data-itinerary-node]');
            nodes.forEach((node) => {
                const index = Number(node.dataset.itineraryNode);
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((e) => {
                        if (e.isIntersecting) this.visibleNodes[index] = true;
                    });
                }, { threshold: 0.35, rootMargin: '-5% 0px -10% 0px' });

                observer.observe(node);
                this._observers.push(observer);
            });
        },

        // ── Node state helpers (all read this.scrollProgress reactively) ────

        nodeMid(index) {
            if (this.total <= 0) return 0;
            return (index + 0.5) / this.total;
        },

        nodeActivation(index) {
            if (this.total <= 0) return 0;
            const start    = index / this.total;
            const mid      = this.nodeMid(index);
            const progress = this.scrollProgress;
            if (progress >= mid)   return 1;
            if (progress <= start) return 0;
            return (progress - start) / (mid - start);
        },

        iconScale(index) {
            return 0.88 + (this.nodeActivation(index) * 0.12);
        },

        nodeInView(index) {
            return Boolean(this.visibleNodes[index]);
        },

        nodeHaloClass(index) {
            const activation = this.nodeActivation(index);
            if (!this.nodeInView(index) && activation < 0.1) return '';
            return 'is-looping';
        },

        nodeRingStyle(index) {
            return `--node-activation:${this.nodeActivation(index).toFixed(3)};`;
        },

        panelStyle(index)  { return this.nodeRingStyle(index); },

        nodeIconStyle(index) {
            return `transform: scale(${this.iconScale(index)});`;
        },

        panelClass(index) {
            if (this.isMobile) return 'is-mobile';
            return index % 2 === 0 ? 'is-left' : 'is-right';
        },

        itemClass(index) {
            const a = this.nodeActivation(index);
            return {
                'is-active':  a >= 0.55,
                'is-done':    a >= 1,
                'is-pending': a < 0.2,
                'is-left':    !this.isMobile && index % 2 === 0,
                'is-right':   !this.isMobile && index % 2 === 1,
            };
        },

        spineProgressStyle() {
            return `transform: scaleY(${this.scrollProgress});`;
        },

        // Kept so the :style binding in the template stays valid (no-op).
        lightStyle() { return ''; },

        formattedIndex(index) {
            return String(index + 1).padStart(2, '0');
        },
    };
}

window.scrollItinerary = scrollItinerary;
