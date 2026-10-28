{{-- Botón «copiar» con confirmación; el texto a copiar viaja en el propio botón. Con «plain» no lleva el estilo de botón (el que lo usa le pone el suyo). --}}
@props([
    'text',
    'label' => 'Copiar enlace',
    'done' => 'Enlace copiado',
    'icon' => 'copy',
    'plain' => false,
])

<button type="button" {{ $attributes->class(['admin-link-button' => ! $plain]) }}
    x-data="{ copied: false }"
    @click="bidaCopy(@js($text)).then((ok) => { if (ok) { copied = true; setTimeout(() => copied = false, 1800); } })">
    <x-phosphor-check x-show="copied" x-cloak aria-hidden="true" />
    <x-dynamic-component :component="'phosphor-'.$icon" x-show="!copied" aria-hidden="true" />
    <span x-text="copied ? @js($done) : @js($label)">{{ $label }}</span>
</button>
