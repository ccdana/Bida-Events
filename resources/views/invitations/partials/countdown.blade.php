<section class="inv-section reveal inv-countdown" id="cuenta-regresiva" x-data="countdown('{{ $eventDate }}')" x-init="start()">
    <div class="inv-wrap inv-wrap--wide">
        <header class="inv-head">
            @include('invitations.partials.lottie-framed-icon', ['name' => 'clock'])
            <p class="inv-head__eyebrow" x-text="finished ? 'Llegó el momento' : 'El gran día se acerca'">El gran día se acerca</p>
            <h2 class="inv-head__title" x-text="finished ? '¡Hoy es el gran día!' : 'Faltan'">Faltan</h2>
            <div class="inv-head__rule" aria-hidden="true"></div>
        </header>

        <div class="inv-countdown__grid" role="timer" x-show="!finished">
            @foreach(['days' => 'Días', 'hours' => 'Horas', 'minutes' => 'Minutos', 'seconds' => 'Segundos'] as $unit => $label)
                <div class="inv-countdown__unit">
                    <span class="inv-countdown__value" x-text="pad(time.{{ $unit }})">00</span>
                    <span class="inv-countdown__label">{{ $label }}</span>
                </div>
            @endforeach
        </div>

        @if(!empty($eventLabel))
            <p class="inv-countdown__date">{{ $eventLabel }}</p>
        @endif

        @if($agendar ?? false)
            <div class="inv-actions inv-countdown__actions">
                <button type="button" class="inv-btn inv-btn--ghost" data-url="{{ $calendarUrl }}" onclick="openCalendar(this.dataset.url)">
                    @include('invitations.partials.lottie-icon', ['name' => 'calendar', 'class' => 'inv-countdown__lottie'])
                    Agendar en mi calendario
                </button>
            </div>
        @endif
    </div>
</section>
<script>
function countdown(isoDate) {
    return {
        time: { days: 0, hours: 0, minutes: 0, seconds: 0 },
        finished: false,
        intervalId: null,
        pad(value) {
            return String(value).padStart(2, '0');
        },
        start() {
            const target = new Date(isoDate).getTime();
            const tick = () => {
                const diff = Math.max(0, target - Date.now());
                this.time = {
                    days: Math.floor(diff / 86400000),
                    hours: Math.floor((diff % 86400000) / 3600000),
                    minutes: Math.floor((diff % 3600000) / 60000),
                    seconds: Math.floor((diff % 60000) / 1000),
                };

                if (diff === 0) {
                    this.finished = true;
                    this.destroy();
                }
            };
            tick();
            if (!this.finished) {
                this.intervalId = setInterval(tick, 1000);
            }
        },
        destroy() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        }
    };
}
</script>
