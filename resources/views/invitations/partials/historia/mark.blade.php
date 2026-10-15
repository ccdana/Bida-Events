{{-- Marca de acto: el número romano en luz de luna y su nombre. Recibe $number y $name. --}}
<p class="story-mark reveal">
    <span class="story-mark__number">{{ __('Acto :number', ['number' => $number]) }}</span>
    <span class="story-mark__name">{{ $name }}</span>
</p>
