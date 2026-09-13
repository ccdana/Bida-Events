{{--
    Encabezado de cada panel del editor: qué es el módulo, qué verá el invitado y su estado.
    Uso: @include('admin.partials.panel-intro', [
        'eyebrow' => 'Galería', 'title' => 'Fotos en pila',
        'description' => 'Qué ve el invitado…', 'tip' => 'Consejo opcional',
        'moduleKey' => 'galeria', 'countExpr' => '`${modules.galeria.fotos.length} fotos`',
    ])
--}}
<section class="admin-card p-3 space-y-2">
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="admin-eyebrow mb-0.5">{{ $eyebrow }}</p>
            <p class="text-sm font-semibold text-stone-900">{{ $title }}</p>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            @if(!empty($countExpr))
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-stone-200 text-xs font-semibold text-stone-600"
                    x-text="{{ $countExpr }}"></span>
            @endif
            @if(!empty($moduleKey))
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold"
                    :class="modules.config.modulos.{{ $moduleKey }}
                        ? 'text-green-700 bg-green-50 border-green-200'
                        : 'text-stone-500 bg-stone-50 border-stone-200'">
                    <span class="inline-block w-1.5 h-1.5 rounded-full"
                        :class="modules.config.modulos.{{ $moduleKey }} ? 'bg-green-500' : 'bg-stone-400'"></span>
                    <span x-text="modules.config.modulos.{{ $moduleKey }} ? 'Visible' : 'Oculto'"></span>
                </span>
            @endif
        </div>
    </div>

    <p class="text-xs text-stone-500 leading-relaxed">
        <span class="font-semibold text-stone-600">Qué ve el invitado:</span> {{ $description }}
    </p>

    @if(!empty($tip))
        <p class="flex gap-2 rounded-lg border border-amber-100 bg-amber-50 px-2.5 py-2 text-xs leading-relaxed text-amber-800">
            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5m0-4h.01M12 3a9 9 0 110 18 9 9 0 010-18z"/>
            </svg>
            <span>{{ $tip }}</span>
        </p>
    @endif
</section>
