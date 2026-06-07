<section class="invitation-section py-16 px-6 z-10 relative">
    <div class="max-w-lg mx-auto space-y-10">
        @if(!empty($destacados['chambelanes']))
        <div class="text-center">
            <h3 class="font-title text-xl text-primary mb-4">Chambelanes</h3>
            <div class="flex flex-wrap justify-center gap-3">
                @foreach($destacados['chambelanes'] as $nombre)
                    <span class="px-4 py-2 rounded-full border border-primary/30 text-sm">{{ $nombre }}</span>
                @endforeach
            </div>
        </div>
        @endif
        @if(!empty($destacados['damitas']))
        <div class="text-center">
            <h3 class="font-title text-xl text-primary mb-4">Damitas</h3>
            <div class="flex flex-wrap justify-center gap-3">
                @foreach($destacados['damitas'] as $nombre)
                    <span class="px-4 py-2 rounded-full border border-primary/30 text-sm">{{ $nombre }}</span>
                @endforeach
            </div>
        </div>
        @endif
        @if(!empty($destacados['padrinos']))
        <div class="space-y-4">
            @foreach($destacados['padrinos'] as $padrino)
                <div class="text-center py-4 border-y border-primary/10">
                    <p class="text-xs uppercase tracking-wider text-primary">{{ $padrino['rol'] }}</p>
                    <p class="font-title text-lg mt-1">{{ $padrino['nombres'] }}</p>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
