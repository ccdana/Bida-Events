<section class="invitation-section relative z-10" x-data="countdown('{{ $eventDate }}')" x-init="start()">
    <div class="section-inner text-center">
        <h2 class="font-title text-2xl mb-8 text-primary">Faltan</h2>
        <div class="grid grid-cols-4 gap-3">
            <template x-for="unit in ['days','hours','minutes','seconds']" :key="unit">
                <div class="rounded-2xl border border-primary/20 bg-white/50 backdrop-blur py-4">
                    <p class="font-title text-2xl sm:text-3xl" x-text="String(time[unit]).padStart(2,'0')"></p>
                    <p class="text-[10px] uppercase tracking-wider opacity-60 mt-1" x-text="labels[unit]"></p>
                </div>
            </template>
        </div>
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
