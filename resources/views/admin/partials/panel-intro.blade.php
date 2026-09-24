{{--
    Encabezado de cada panel del editor: qué es el módulo, qué verá el invitado y su estado.
    Uso: @include('admin.partials.panel-intro', [
        'eyebrow' => 'Galería', 'title' => 'Fotos en pila',
        'description' => 'Qué ve el invitado…', 'tip' => 'Consejo opcional',
        'moduleKey' => 'galeria', 'countExpr' => '`${modules.galeria.fotos.length} fotos`',
    ])
--}}
<section class="ed-intro space-y-3">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="admin-eyebrow">{{ $eyebrow }}</p>
            <h2 class="text-base font-semibold tracking-tight">{{ $title }}</h2>
        </div>
        <div class="flex shrink-0 items-center gap-1.5">
            @if(!empty($countExpr))
                <span class="admin-status-badge" x-text="{{ $countExpr }}"></span>
            @endif
            @if(!empty($moduleKey))
                <span class="admin-status-badge" :class="modules.config.modulos.{{ $moduleKey }} ? 'is-active' : ''">
                    <x-phosphor-eye class="size-3.5" x-show="modules.config.modulos.{{ $moduleKey }}" aria-hidden="true" />
                    <x-phosphor-eye-slash class="size-3.5" x-show="!modules.config.modulos.{{ $moduleKey }}" aria-hidden="true" />
                    <span x-text="modules.config.modulos.{{ $moduleKey }} ? 'Visible' : 'Oculto'"></span>
                </span>
            @endif
        </div>
    </div>

    <p class="text-sm leading-relaxed text-site-muted">
        <span class="font-medium text-site-ink">Qué ve el invitado:</span> {{ $description }}
    </p>

    @if(!empty($tip))
        <p class="flex gap-2.5 rounded-[12px] bg-site-tint px-3 py-2.5 text-sm leading-relaxed">
            <x-phosphor-lightbulb class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
            <span>{{ $tip }}</span>
        </p>
    @endif
</section>
