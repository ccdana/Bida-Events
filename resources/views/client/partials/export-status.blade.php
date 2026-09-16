{{-- Estado del último archivo pedido: pregunta cada dos segundos hasta que esté listo. --}}
@if(session('export'))
    <div class="site-enter admin-card mt-6 flex flex-wrap items-center gap-3 p-4"
        x-data="exportWatcher(@js(session('export')))" x-init="watch()">
        <template x-if="state.status === 'pending'">
            <span class="inline-flex items-center gap-2 text-site-muted">
                <x-phosphor-spinner class="size-5 animate-spin" aria-hidden="true" />
                Preparando tu <span x-text="state.label"></span>…
            </span>
        </template>

        <template x-if="state.status === 'ready'">
            <span class="inline-flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2">
                    <x-phosphor-check-circle class="size-5 text-site-accent" aria-hidden="true" />
                    Tu <span x-text="state.label"></span> está listo.
                </span>
                <a :href="state.download_url" class="admin-primary-button">
                    <x-phosphor-download-simple aria-hidden="true" />
                    Descargar
                </a>
            </span>
        </template>

        <template x-if="state.status === 'failed'">
            <span class="inline-flex items-center gap-2 text-site-danger">
                <x-phosphor-warning-circle class="size-5" aria-hidden="true" />
                No pudimos generar el archivo. Intenta de nuevo o escríbenos.
            </span>
        </template>
    </div>

    <script>
        function exportWatcher(initial) {
            return {
                state: initial,
                tries: 0,
                watch() {
                    if (this.state.status !== 'pending' || this.tries > 60) {
                        return;
                    }

                    this.tries++;

                    setTimeout(async () => {
                        try {
                            const res = await fetch(this.state.status_url, { headers: { Accept: 'application/json' } });
                            this.state = await res.json();
                        } catch (error) {
                            // Si falla la consulta se reintenta en el siguiente ciclo
                        }

                        this.watch();
                    }, 2000);
                },
            };
        }
    </script>
@endif
