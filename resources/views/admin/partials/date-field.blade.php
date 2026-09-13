{{--
    Fecha en formato DD-MM-AAAA enlazada a un valor ISO del editor.
    Uso: @include('admin.partials.date-field', ['id' => 'event-date', 'label' => 'Día del evento', 'model' => 'eventDatePart'])
--}}
<div class="admin-date-field" x-data="dateField()" x-modelable="value" x-model="{{ $model }}">
    <label for="{{ $id }}" class="admin-label">{{ $label }}</label>
    <div class="relative">
        <input id="{{ $id }}" type="text" inputmode="numeric" placeholder="DD-MM-AAAA" maxlength="10" autocomplete="off" required
            class="admin-input pr-12 font-mono tracking-wide"
            :style="error ? 'border-color: var(--site-danger)' : ''"
            :aria-invalid="error ? 'true' : 'false'" aria-describedby="{{ $id }}-help"
            :value="text" @input="onInput($event)" @blur="onBlur()">
        <button type="button" class="admin-icon-button absolute right-1 top-1/2 -translate-y-1/2" @click="openPicker()"
            aria-label="Elegir la fecha en el calendario" title="Abrir calendario">
            <x-phosphor-calendar-blank aria-hidden="true" />
        </button>
        <input type="date" x-ref="picker" tabindex="-1" aria-hidden="true" @change="onPick($event)"
            class="pointer-events-none absolute bottom-0 right-4 size-px opacity-0">
    </div>
    <p id="{{ $id }}-help" class="text-xs first-letter:uppercase" :class="error ? 'text-site-danger' : 'text-site-muted'" x-text="error || readable">Formato DD-MM-AAAA</p>
</div>
