const MOBILE_BREAKPOINT = 768;

export function scrollItinerary(totalSteps = 0) {
    return {
        total: Math.max(Number(totalSteps) || 0, 0),
        scrollProgress: 0,
        isMobile: true,
        visibleNodes: {},
        _scrollHandler: null,
        _resizeHandler: null,
        _observers: [],

        init() {
            this.updateLayout();
            this.bindScroll();
            this.observeNodes();
            this.updateProgress();
            this.$nextTick(() => window.initLottieIcons?.());

            return () => this.destroy();
        },

        destroy() {
            if (this._scrollHandler) {
                window.removeEventListener('scroll', this._scrollHandler, { passive: true });
            }

            if (this._resizeHandler) {
                window.removeEventListener('resize', this._resizeHandler);
            }

            this._observers.forEach((observer) => observer.disconnect());
            this._observers = [];
        },

        bindScroll() {
            this._scrollHandler = () => this.updateProgress();
            this._resizeHandler = () => {
                this.updateLayout();
                this.updateProgress();
            };

            window.addEventListener('scroll', this._scrollHandler, { passive: true });
            window.addEventListener('resize', this._resizeHandler, { passive: true });
        },

        updateLayout() {
            this.isMobile = window.innerWidth < MOBILE_BREAKPOINT;
        },

        updateProgress() {
            const track = this.$refs.track;

            if (!track) {
                return;
            }

            const viewport = window.innerHeight;
            const rect = track.getBoundingClientRect();
            const startLine = viewport * 0.22;
            const endLine = viewport * 0.78;
            const scrollRange = Math.max(rect.height - (endLine - startLine), 1);
            const traveled = Math.min(Math.max(startLine - rect.top, 0), scrollRange);

            this.scrollProgress = Math.min(Math.max(traveled / scrollRange, 0), 1);
        },

        observeNodes() {
            const nodes = this.$root.querySelectorAll('[data-itinerary-node]');

            nodes.forEach((node) => {
                const index = Number(node.dataset.itineraryNode);
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            this.visibleNodes[index] = true;
                        }
                    });
                }, { threshold: 0.35, rootMargin: '-5% 0px -10% 0px' });

                observer.observe(node);
                this._observers.push(observer);
            });
        },

        nodeMid(index) {
            if (this.total <= 0) {
                return 0;
            }

            return (index + 0.5) / this.total;
        },

        nodeActivation(index) {
            if (this.total <= 0) {
                return 0;
            }

            const start = index / this.total;
            const mid = this.nodeMid(index);
            const progress = this.scrollProgress;

            if (progress >= mid) {
                return 1;
            }

            if (progress <= start) {
                return 0;
            }

            return (progress - start) / (mid - start);
        },

        iconScale(index) {
            return 0.82 + (this.nodeActivation(index) * 0.18);
        },

        nodeInView(index) {
            return Boolean(this.visibleNodes[index]);
        },

        nodeHaloClass(index) {
            return this.nodeInView(index) ? 'is-looping' : '';
        },

        nodeRingStyle(index) {
            const activation = this.nodeActivation(index);

            return `--node-activation:${activation};`;
        },

        panelStyle(index) {
            return this.nodeRingStyle(index);
        },

        nodeIconStyle(index) {
            const scale = this.iconScale(index);

            return `transform: scale(${scale});`;
        },

        panelClass(index) {
            if (this.isMobile) {
                return 'is-mobile';
            }

            return index % 2 === 0 ? 'is-left' : 'is-right';
        },

        itemClass(index) {
            const activation = this.nodeActivation(index);

            return {
                'is-active': activation >= 0.55,
                'is-done': activation >= 1,
                'is-pending': activation < 0.2,
                'is-left': !this.isMobile && index % 2 === 0,
                'is-right': !this.isMobile && index % 2 === 1,
            };
        },

        spineProgressStyle() {
            return `transform: scaleY(${this.scrollProgress});`;
        },

        lightStyle() {
            return `top: calc(${this.scrollProgress * 100}% - 0.35rem);`;
        },

        formattedIndex(index) {
            return String(index + 1).padStart(2, '0');
        },
    };
}

window.scrollItinerary = scrollItinerary;
