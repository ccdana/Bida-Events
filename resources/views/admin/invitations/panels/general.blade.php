<div x-show="activeTab === 'general'" x-cloak class="admin-card space-y-4">
    <h2 class="font-serif text-lg text-stone-900">Datos generales</h2>
    <div>
        <label class="admin-label">Título del evento</label>
        <input type="text" name="title" x-model="meta.title" @input="onTitleInput()" class="admin-input" required>
    </div>
    <div>
        <label class="admin-label">Slug (URL pública)</label>
        <div class="flex items-center gap-2">
            <span class="text-xs text-stone-400 shrink-0">/p/</span>
            <input type="text" name="slug" x-model="meta.slug" @input="slugManual = true" class="admin-input" required>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="admin-label">Tipo de evento</label>
            <select name="event_type_id" x-model="meta.event_type_id" class="admin-input" required>
                @foreach($eventTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="admin-label">Plan</label>
            <select name="plan_id" x-model="meta.plan_id" class="admin-input" required>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="admin-label">Cliente asignado</label>
        <select name="user_id" x-model="meta.user_id" class="admin-input">
            <option value="">Sin cliente</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="admin-label">Plantilla</label>
        <select name="template" x-model="meta.template" class="admin-input" required>
            @foreach($templates as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="admin-label">Fecha del evento</label>
            <input type="datetime-local" name="event_date" x-model="meta.event_date" class="admin-input" required>
        </div>
        <div>
            <label class="admin-label">Expira el</label>
            <input type="date" name="expires_at" x-model="meta.expires_at" class="admin-input" required>
        </div>
    </div>
    <div>
        <label class="admin-label">Estado</label>
        <select name="status" x-model="meta.status" class="admin-input">
            @foreach(['draft' => 'Borrador', 'active' => 'Activa', 'suspended' => 'Suspendida', 'expired' => 'Expirada'] as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
</div>
