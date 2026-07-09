<section
    class="invitation-section reveal invitation-gallery"
    id="galeria"
    x-data="galleryStack(@js($galeria['fotos'] ?? []))"
    x-init="init()"
    @pointermove.window="onPointerMove($event)"
    @pointerup.window="onPointerUp($event)"
    @pointercancel.window="onPointerUp($event)"
>
    <div class="section-inner-wide">
        <div class="invitation-gallery__shell">
            @include('invitations.partials.lottie-framed-icon', ['name' => 'eye-image'])
            <p class="invitation-gallery__eyebrow">Momentos especiales</p>
            <h2 class="invitation-gallery__title">{{ $galeria['titulo'] ?? 'Galería' }}</h2>
            <div class="invitation-gallery__rule" aria-hidden="true"></div>
            <p class="invitation-gallery__hint">Desliza para descubrir</p>

            <template x-if="photos.length > 0">
                <div class="invitation-gallery__stage">
                    <div class="invitation-gallery__stack" x-ref="stack" aria-live="polite">
                        <template x-for="photoIndex in order" :key="`gallery-card-${photoIndex}`">
                            <article
                                class="invitation-gallery__card"
                                :class="{
                                    'is-top': isTopCard(photoIndex),
                                    'is-dragging': isTopCard(photoIndex) && isDragging,
                                    'is-resetting': resettingIndex === photoIndex,
                                }"
                                :data-photo-index="photoIndex"
                                :style="cardStyle(photoIndex)"
                                @pointerdown="onPointerDown($event, photoIndex)"
                            >
                                <img
                                    :src="photos[photoIndex]"
                                    :alt="'Foto ' + (photoIndex + 1)"
                                    class="invitation-gallery__image"
                                    loading="lazy"
                                    draggable="false"
                                >
                                <div class="invitation-gallery__image-shade" aria-hidden="true"></div>
                            </article>
                        </template>
                    </div>

                    <div class="invitation-gallery__nav">
                        <button
                            type="button"
                            class="invitation-gallery__nav-label"
                            @click="swipePrev()"
                            :disabled="photos.length <= 1 || isAnimating"
                        >
                            &lt; Desliza
                        </button>

                        <div class="invitation-gallery__dots" role="tablist" aria-label="Fotos de la galería">
                            <template x-for="(_, index) in photos" :key="`gallery-dot-${index}`">
                                <span
                                    class="invitation-gallery__dot"
                                    :class="{ 'is-active': currentTopIndex() === index }"
                                    role="tab"
                                    :aria-selected="currentTopIndex() === index"
                                ></span>
                            </template>
                        </div>

                        <button
                            type="button"
                            class="invitation-gallery__nav-label"
                            @click="swipeNext()"
                            :disabled="photos.length <= 1 || isAnimating"
                        >
                            Desliza &gt;
                        </button>
                    </div>
                </div>
            </template>

            <template x-if="photos.length === 0">
                <p class="invitation-gallery__empty">Aún no hay fotos en esta galería.</p>
            </template>
        </div>
    </div>
</section>
