import './bootstrap';
import Alpine from 'alpinejs';
import 'video.js/dist/video-js.css';
import videojs from 'video.js';
import { initLottieIcons } from './lottie-icons';
import './gallery-stack';
import './itinerary-scroll';

window.Alpine = Alpine;
window.videojs = videojs;
Alpine.start();

const initVideoPlayers = () => {
    document.querySelectorAll('[data-video-player]').forEach((element) => {
        if (!element.id || element.dataset.videoInitialized === 'true') {
            return;
        }

        try {
            const frame = element.closest('[data-video-frame]') ?? element.parentElement;
            const toggleButton = frame?.querySelector('[data-video-toggle]');
            let isIdle = true;
            let hasPlayed = false;

            const setIdleState = (idle) => {
                isIdle = idle;
                frame?.classList.toggle('is-idle', idle);
                frame?.classList.toggle('is-playing', !idle && !element.paused);
                toggleButton?.classList.toggle('is-pinned', idle);

                if (idle) {
                    clearTimeout(toggleButton?._hideTimer);
                    toggleButton?.classList.add('is-visible');
                }
            };

            const setToggleState = (state) => {
                if (!toggleButton) {
                    return;
                }

                const isPaused = state === 'paused';
                toggleButton.classList.toggle('is-paused', isPaused);
                toggleButton.setAttribute('aria-label', isPaused ? 'Reproducir video' : 'Pausar video');
                toggleButton.setAttribute('title', isPaused ? 'Reproducir video' : 'Pausar video');
            };

            const revealToggle = () => {
                if (!toggleButton || isIdle) {
                    return;
                }

                toggleButton.classList.add('is-visible');
                clearTimeout(toggleButton._hideTimer);
                toggleButton._hideTimer = setTimeout(() => {
                    if (!toggleButton.classList.contains('is-pinned')) {
                        toggleButton.classList.remove('is-visible');
                    }
                }, 2000);
            };

            const bindInactivityFade = () => {
                if (!frame || !toggleButton) {
                    return;
                }

                frame.addEventListener('pointerenter', () => {
                    if (!isIdle) {
                        revealToggle();
                    }
                });
                frame.addEventListener('pointermove', () => {
                    if (!isIdle) {
                        revealToggle();
                    }
                });
                frame.addEventListener('pointerleave', () => {
                    if (isIdle || !toggleButton.classList.contains('is-visible')) {
                        return;
                    }

                    clearTimeout(toggleButton._hideTimer);
                    toggleButton._hideTimer = setTimeout(() => {
                        if (!toggleButton.classList.contains('is-pinned')) {
                            toggleButton.classList.remove('is-visible');
                        }
                    }, 2000);
                });
            };

            const togglePlayback = async () => {
                try {
                    if (element.paused) {
                        await element.play();
                    } else {
                        element.pause();
                    }
                } catch (error) {
                    console.error('Unable to toggle invitation video playback', error);
                }

                revealToggle();
            };

            toggleButton?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                togglePlayback();
            });

            element.addEventListener('play', () => {
                hasPlayed = true;
                setIdleState(false);
                setToggleState('playing');
                revealToggle();
            });

            element.addEventListener('pause', () => {
                setToggleState('paused');

                if (element.currentTime > 0 && element.currentTime < element.duration) {
                    setIdleState(false);
                    revealToggle();
                }
            });

            element.addEventListener('ended', () => {
                element.currentTime = 0;
                setToggleState('paused');
                setIdleState(true);
            });

            element.addEventListener('click', () => {
                togglePlayback();
            });

            if (!element.paused && !element.ended) {
                hasPlayed = true;
                setIdleState(false);
            } else {
                setIdleState(true);
            }

            setToggleState(element.paused ? 'paused' : 'playing');
            bindInactivityFade();
            element.dataset.videoInitialized = 'true';
        } catch (error) {
            console.error('Unable to initialize invitation video player', error);
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initVideoPlayers();
    initLottieIcons();
});
window.addEventListener('load', () => {
    initVideoPlayers();
    initLottieIcons();
});
