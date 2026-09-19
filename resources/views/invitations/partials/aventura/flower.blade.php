{{-- Un dibujo del sprite del libro. Parámetros: kind (girasol, margarita, ramita, globo, corazon) y class. --}}
<svg class="nb-draw nb-draw--{{ $kind }} {{ $class ?? '' }}" aria-hidden="true" focusable="false"
    viewBox="{{ ['ramita' => '0 0 120 60', 'globo' => '0 0 40 60', 'corazon' => '0 0 100 92'][$kind] ?? '0 0 100 100' }}"><use href="#nb-{{ $kind }}"/></svg>
