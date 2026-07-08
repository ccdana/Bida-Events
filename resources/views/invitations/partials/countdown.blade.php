<section class="invitation-section reveal invitation-countdown" id="cuenta-regresiva" x-data="countdown('{{ $eventDate }}')" x-init="start()">
    <div class="section-inner-wide">
        <div class="invitation-countdown__frame">
            @include('invitations.partials.lottie-framed-icon', ['name' => 'clock'])
            <p class="invitation-countdown__eyebrow">El gran día se acerca</p>
            <h2 class="invitation-countdown__title">Faltan</h2>
            <div class="invitation-countdown__rule" aria-hidden="true"></div>

            <div class="invitation-countdown__grid" aria-live="polite">
                <template x-for="unit in ['days','hours','minutes','seconds']" :key="unit">
                    <div class="invitation-countdown__unit">
                        <p class="invitation-countdown__value" x-text="String(time[unit]).padStart(2,'0')"></p>
                        <p class="invitation-countdown__label" x-text="labels[unit]"></p>
                    </div>
                </template>
            </div>

            @if($agendar ?? false)
                <button type="button" onclick="openCalendar('{{ $calendarUrl }}')"
                    class="invitation-countdown__button">
                    @include('invitations.partials.lottie-icon', ['name' => 'calendar', 'class' => 'invitation-countdown__button-lottie'])
                    Agendar en Google Calendar
                </button>
            @endif
        </div>
    </div>
</section>
<script>
function countdown(isoDate) {
    return {
        time: { days: 0, hours: 0, minutes: 0, seconds: 0 },
        labels: { days: 'Días', hours: 'Hrs', minutes: 'Min', seconds: 'Seg' },
        intervalId: null,
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
            };
            tick();
            this.intervalId = setInterval(tick, 1000);
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
