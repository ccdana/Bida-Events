{{--
    Apertura por toque de XV y bautizo. Etapas: 0 esperando · 1 reacciona al toque · 2 se abre.
    timing (ms): part = empieza a abrirse, reveal = la portada se anima, close = se retira.
    timing.origin (opcional): selector del elemento desde el que se abre; su centro queda en --cover-x/--cover-y.
    El toque también desbloquea la música si tiene autoplay.
--}}
<script>
function invitationCover(timing) {
    return {
        stage: 0,
        closed: false,
        init() {
            const root = document.documentElement;

            if (root.classList.contains('inv-cover-skip')) {
                this.closed = true;
                return;
            }

            window.scrollTo(0, 0);
            root.classList.add('inv-lock');
        },
        open() {
            if (this.stage > 0 || this.closed) {
                return;
            }

            const root = document.documentElement;
            const finish = () => {
                this.closed = true;
                root.classList.remove('inv-lock');
            };

            if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
                root.classList.remove('inv-cover-waiting');
                finish();
                return;
            }

            if (timing.origin) {
                const rect = this.$el.querySelector(timing.origin)?.getBoundingClientRect();

                if (rect) {
                    this.$el.style.setProperty('--cover-x', `${rect.left + rect.width / 2}px`);
                    this.$el.style.setProperty('--cover-y', `${rect.top + rect.height / 2}px`);
                }
            }

            this.stage = 1;
            setTimeout(() => { this.stage = 2; }, timing.part);
            setTimeout(() => root.classList.remove('inv-cover-waiting'), timing.reveal);
            setTimeout(finish, timing.close);
        },
    };
}
</script>
