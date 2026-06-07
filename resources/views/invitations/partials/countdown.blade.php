<section class="invitation-section reveal" x-data="countdown('{{ $eventDate }}')" x-init="start()">
    <div class="section-inner text-center">
        <header class="section-header">
            <span class="section-eyebrow">El gran día se acerca</span>
            <h2 class="section-title">Faltan</h2>
            <div class="section-ornament"></div>
        </header>
        <div class="grid grid-cols-4 gap-2.5 mb-6">
            <template x-for="unit in ['days','hours','minutes','seconds']" :key="unit">
                <div class="rounded-2xl inv-card py-4">
                    <p class="font-title text-2xl sm:text-3xl tabular-nums" x-text="String(time[unit]).padStart(2,'0')"></p>
                    <p class="text-[10px] uppercase tracking-wider opacity-50 mt-1" x-text="labels[unit]"></p>
                </div>
            </template>
        </div>

        @if($agendar ?? false)
            <a href="{{ $calendarUrl }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-full inv-card-soft text-sm font-medium text-primary hover:scale-[1.02] active:scale-[0.98] transition-transform">
                @include('invitations.partials.icon', ['name' => 'calendar', 'class' => 'w-4 h-4', 'animated' => false])
                Agendar en Google Calendar
            </a>
        @endif
    </div>
</section>
<script>
function countdown(isoDate) {
    return {
        time: { days: 0, hours: 0, minutes: 0, seconds: 0 },
        labels: { days: 'Días', hours: 'Hrs', minutes: 'Min', seconds: 'Seg' },
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
            setInterval(tick, 1000);
        }
    };
}
</script>
