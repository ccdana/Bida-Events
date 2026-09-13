{{-- Logo completo: isotipo + nombre de la marca (la primera palabra en negrita). --}}
@props(['animated' => false, 'markClass' => 'h-9 w-auto'])

@php([$first, $rest] = array_pad(explode(' ', (string) config('bida.brand'), 2), 2, ''))

<span {{ $attributes->class('brand-logo') }}>
    <x-brand.mark :animated="$animated" :class="$markClass" />
    <span class="brand-logo__word"><span class="brand-logo__strong">{{ $first }}</span>@if($rest) <span class="brand-logo__soft">{{ $rest }}</span>@endif</span>
</span>
