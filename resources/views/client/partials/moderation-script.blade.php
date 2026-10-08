@once
    <script>
        /**
         * Ocultar / mostrar una foto, canción o respuesta sin recargar la página. El formulario sigue
         * ahí: si el JavaScript no llega a cargar, se envía como siempre y la página se recarga.
         */
        window.moderation = function (url, hidden) {
            return {
                hidden: !!hidden,
                busy: false,
                message: '',
                failed: false,
                async toggle() {
                    if (this.busy) return;

                    this.busy = true;
                    this.failed = false;

                    try {
                        const response = await fetch(url, {
                            method: 'PATCH',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ moderation_status: this.hidden ? 'visible' : 'hidden' }),
                        });
                        const data = await response.json().catch(() => ({}));

                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'No se pudo actualizar. Intenta de nuevo.');
                        }

                        this.hidden = !!data.hidden;
                        this.message = data.message;
                        this.$dispatch('moderated', { hidden: this.hidden });
                    } catch (error) {
                        this.failed = true;
                        this.message = error.message || 'Revisa tu conexión e intenta de nuevo.';
                    } finally {
                        this.busy = false;
                    }
                },
            };
        };
    </script>
@endonce
