{{-- Panel de App\Modules\Card\MilestoneModule: fecha y texto del contador --}}
<div x-show="activeTab === 'juntos_desde'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Juntos desde',
        'title' => 'Tiempo que llevan juntos',
        'description' => 'los años, meses y días que pasaron desde esta fecha, y el total de días compartidos. Se calcula solo cada vez que se abre la carta.',
        'moduleKey' => 'juntos_desde',
    ])

    <section class="admin-card p-3 space-y-3">
        <div>
            <label class="admin-label" for="juntos-fecha">Fecha</label>
            <input id="juntos-fecha" type="date" x-model="modules.juntos_desde.fecha" @input="schedulePreview()" class="admin-input" :max="new Date().toISOString().slice(0, 10)">
            <p class="mt-1 text-[11px] text-site-muted">El día que se conocieron, empezaron o se casaron.</p>
        </div>
        <div>
            <label class="admin-label" for="juntos-titulo">Texto sobre el contador</label>
            <input id="juntos-titulo" type="text" x-model="modules.juntos_desde.titulo" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Llevamos juntos">
        </div>
    </section>
</div>
