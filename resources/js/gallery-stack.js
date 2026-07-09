import { animate } from 'motion';

const DEPTH_PRESETS = [
    { rotate: 0, scale: 1, y: 0, x: 0 },
    { rotate: -7, scale: 0.94, y: 12, x: -10 },
    { rotate: 5.5, scale: 0.88, y: 22, x: 11 },
    { rotate: -4, scale: 0.82, y: 30, x: -6 },
];

const SWIPE_DISTANCE_THRESHOLD = 85;
const SWIPE_VELOCITY_THRESHOLD = 550;

export function galleryStack(initialPhotos = []) {
    return {
        photos: Array.isArray(initialPhotos) ? initialPhotos.filter(Boolean) : [],
        order: [],
        dragX: 0,
        topOpacity: 1,
        isDragging: false,
        isAnimating: false,
        resettingIndex: null,
        pointerId: null,
        dragStartX: 0,
        dragStartTime: 0,

        init() {
            this.resetOrder();
            this.$nextTick(() => window.initLottieIcons?.());
        },

        resetOrder() {
            this.order = this.photos.map((_, index) => index);
        },

        stackDepth(photoIndex) {
            return this.order.indexOf(photoIndex);
        },

        isTopCard(photoIndex) {
            return this.stackDepth(photoIndex) === 0;
        },

        currentTopIndex() {
            return this.order[0] ?? 0;
        },

        cardStyle(photoIndex) {
            const depth = this.stackDepth(photoIndex);
            const preset = DEPTH_PRESETS[Math.min(depth, DEPTH_PRESETS.length - 1)];
            const zIndex = this.order.length - depth;

            if (depth === 0) {
                const rotate = this.dragX * 0.045;
                const opacity = this.topOpacity;

                return `transform: translate3d(${this.dragX}px, ${preset.y}px, 0) rotate(${rotate}deg) scale(1); z-index: ${zIndex}; opacity: ${opacity};`;
            }

            return `transform: translate3d(${preset.x}px, ${preset.y}px, 0) rotate(${preset.rotate}deg) scale(${preset.scale}); z-index: ${zIndex}; opacity: ${depth > 3 ? 0 : 1};`;
        },

        onPointerDown(event, photoIndex) {
            if (!this.isTopCard(photoIndex) || this.isAnimating || this.photos.length <= 1) {
                return;
            }

            this.isDragging = true;
            this.pointerId = event.pointerId;
            this.dragStartX = event.clientX;
            this.dragStartTime = Date.now();
            event.currentTarget.setPointerCapture?.(event.pointerId);
        },

        onPointerMove(event) {
            if (!this.isDragging || event.pointerId !== this.pointerId) {
                return;
            }

            this.dragX = event.clientX - this.dragStartX;
        },

        async onPointerUp(event) {
            if (!this.isDragging || event.pointerId !== this.pointerId) {
                return;
            }

            this.isDragging = false;
            this.pointerId = null;

            const elapsed = Math.max(Date.now() - this.dragStartTime, 1);
            const velocity = Math.abs(this.dragX) / (elapsed / 1000);
            const shouldFling = Math.abs(this.dragX) > SWIPE_DISTANCE_THRESHOLD
                || velocity > SWIPE_VELOCITY_THRESHOLD;

            if (shouldFling) {
                await this.flingCard(this.dragX < 0 ? -1 : 1);
                return;
            }

            await this.snapBack();
        },

        async snapBack() {
            if (this.dragX === 0) {
                return;
            }

            this.isAnimating = true;
            const state = { x: this.dragX };

            await animate(state, { x: 0 }, {
                duration: 0.3,
                easing: [0.34, 1.2, 0.64, 1],
                onUpdate: () => {
                    this.dragX = state.x;
                },
            }).finished;

            this.dragX = 0;
            this.isAnimating = false;
        },

        async flingCard(direction) {
            if (this.isAnimating || this.photos.length <= 1) {
                return;
            }

            this.isAnimating = true;
            const topIndex = this.order[0];
            const startX = this.dragX;
            const targetX = startX + (direction * Math.max(window.innerWidth * 0.9, 320));
            const state = { x: startX, opacity: this.topOpacity };

            await animate(state, { x: targetX, opacity: 0.15 }, {
                duration: 0.4,
                easing: [0.32, 0.72, 0, 1],
                onUpdate: () => {
                    this.dragX = state.x;
                    this.topOpacity = state.opacity;
                },
            }).finished;

            this.resettingIndex = topIndex;
            const top = this.order.shift();
            this.order.push(top);
            this.dragX = 0;
            this.topOpacity = 1;

            await this.$nextTick();

            requestAnimationFrame(() => {
                this.resettingIndex = null;
                this.isAnimating = false;
            });
        },

        swipePrev() {
            return this.flingCard(-1);
        },

        swipeNext() {
            return this.flingCard(1);
        },
    };
}

window.galleryStack = galleryStack;
