{{--
    Asistente de creación: antes de ver la invitación se elige el evento, el diseño y se escriben los
    datos que la arman (los nombres, la fecha y la hora; en una tarjeta, para quién es). Al terminar se
    crea con las partes del evento ya llenas de ejemplo y se abre la vista previa para elegir las partes
    y cambiar lo que haga falta tocándolo. Lógica en resources/js/admin/editor.js (wizardChooseEvent, finishWizard).

    Sin párrafos: una flecha y tres rayitas arriba, el título del paso y lo que se toca. Cada tipo de evento
    lleva su ícono suelto (por su código; uno nuevo usa el de «default»).
--}}
@php
    // Los íconos de los tipos de evento vienen de App\Support\EventIcons (los mismos de las listas del panel)
    $wizardIcons = collect(\App\Support\EventIcons::MAP + [
        'default' => \App\Support\EventIcons::FALLBACK,
        // Íconos de las etiquetas de los nombres (por la clave del campo de la portada)
        'campo-nombre' => 'user', 'campo-nombre_pareja' => 'heart', 'campo-edad' => 'hash', 'campo-default' => 'pencil-simple-line',
    ])->map(fn (string $name) => svg('phosphor-'.$name.'-light', 'ed-wiz-icon', ['aria-hidden' => 'true'])->toHtml());
@endphp
<div class="ed-wizard" x-show="wizardOpen" x-cloak role="dialog" aria-modal="true" aria-labelledby="wizard-title"
    x-data="{ wizardIcons: @js($wizardIcons) }">
    <div class="ed-wizard__inner">
        <div class="ed-wizard__bar">
            {{-- En el primer paso la flecha sale del asistente; en los demás vuelve un paso --}}
            <a href="{{ $cancelUrl ?? url()->previous() }}" class="ed-wizard__back" x-show="wizardStep === 1" aria-label="Cancelar" title="Cancelar">
                <x-phosphor-arrow-left-light aria-hidden="true" />
            </a>
            <button type="button" class="ed-wizard__back" x-show="wizardStep > 1" x-cloak @click="wizardStep--; wizardError = ''" aria-label="Atrás" title="Atrás">
                <x-phosphor-arrow-left-light aria-hidden="true" />
            </button>
            <ol class="ed-wizard__steps" aria-label="Pasos">
                <template x-for="(step, index) in ['El evento', 'El diseño', 'Los datos']" :key="index">
                    <li class="ed-wizard__step" :class="{ 'is-current': wizardStep === index + 1, 'is-done': wizardStep > index + 1 }">
                        <button type="button" :disabled="wizardStep <= index + 1" @click="wizardStep = index + 1; wizardError = ''"
                            :aria-current="wizardStep === index + 1 ? 'step' : null" :aria-label="'Paso ' + (index + 1) + ': ' + step" :title="step"></button>
                    </li>
                </template>
            </ol>
        </div>

        {{-- 1. El evento --}}
        <section class="ed-wizard__body" x-show="wizardStep === 1">
            <h1 id="wizard-title" class="ed-wizard__title site-display site-display--sm">¿Qué vas a celebrar?</h1>
            <template x-for="group in wizardEventGroups" :key="group.name">
                <div class="ed-wizard__group">
                    <h2 class="ed-wizard__label" x-show="wizardEventGroups.length > 1" x-text="group.name"></h2>
                    <div class="ed-wizard__grid">
                        <template x-for="type in group.types" :key="type.id">
                            <button type="button" class="ed-wiz-row" @click="wizardChooseEvent(type)"
                                :class="{ 'is-on': String(meta.event_type_id) === String(type.id) }"
                                :disabled="!!eventTypeDisabledReason(type)" :title="eventTypeDisabledReason(type)"
                                :aria-pressed="(String(meta.event_type_id) === String(type.id)).toString()">
                                <span class="ed-wiz-row__icon" aria-hidden="true" x-html="wizardIcons[type.code] ?? wizardIcons.default"></span>
                                <span class="ed-wiz-row__text">
                                    <span class="ed-wiz-row__name" x-text="type.name"></span>
                                    <span class="ed-wiz-row__note" x-show="eventTypeDisabledReason(type)" x-text="eventTypeDisabledReason(type)"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </section>

        {{-- 2. El diseño: la captura y su nombre; la descripción queda como título del botón --}}
        <section class="ed-wizard__body" x-show="wizardStep === 2" x-cloak>
            <h1 class="ed-wizard__title site-display site-display--sm">Elige el diseño</h1>
            <div class="ed-wizard__designs">
                <template x-for="option in templatesForEventType()" :key="option.value">
                    <button type="button" class="ed-design" @click="wizardChooseTemplate(option)"
                        :class="{ 'is-on': meta.template === option.value }" :disabled="!!option.disabledReason"
                        :title="option.disabledReason || option.description" :aria-pressed="(meta.template === option.value).toString()">
                        <span class="ed-design__shot">
                            <img x-show="option.capture" :src="option.capture || ''" alt="" loading="eager" decoding="async">
                            <span class="ed-design__swatches" x-show="!option.capture" aria-hidden="true">
                                <i :style="'background:' + (option.palette?.background ?? '#fff')"></i>
                                <i :style="'background:' + (option.palette?.accent ?? '#eee')"></i>
                                <i :style="'background:' + (option.palette?.primary ?? '#999')"></i>
                                <i :style="'background:' + (option.palette?.text ?? '#222')"></i>
                            </span>
                            <x-phosphor-check-circle-fill class="ed-design__check" aria-hidden="true" />
                        </span>
                        <span class="ed-design__name" x-text="option.label"></span>
                        <span class="ed-design__note" x-show="option.disabledReason" x-text="option.disabledReason"></span>
                    </button>
                </template>
            </div>
        </section>

        {{-- 3. Los datos --}}
        <section class="ed-wizard__body" x-show="wizardStep === 3" x-cloak>
            <h1 class="ed-wizard__title site-display site-display--sm" x-text="isCard ? '¿Para quién es?' : 'Los datos'"></h1>

            <div class="ed-wizard__form">
                <template x-if="!isCard">
                    <div class="ed-wizard__fields">
                        <template x-for="field in heroFields" :key="field.key">
                            <label class="ed-wiz-field">
                                <span class="site-label ed-wiz-label">
                                    <span aria-hidden="true" x-html="wizardIcons['campo-' + field.key] ?? wizardIcons['campo-default']"></span>
                                    <span x-text="field.label"></span>
                                </span>
                                <input class="site-input" maxlength="80" :type="field.type || 'text'" :inputmode="field.type === 'number' ? 'numeric' : null"
                                    :value="modules.bienvenida[field.key] ?? ''"
                                    @input="modules.bienvenida[field.key] = $event.target.value; syncHeroName()"
                                    :placeholder="field.placeholder">
                            </label>
                        </template>
                        <div class="ed-wizard__pair">
                            <label class="ed-wiz-field">
                                <span class="site-label ed-wiz-label"><x-phosphor-calendar-blank-light aria-hidden="true" /> Día</span>
                                <input class="site-input" type="date" x-model="eventDatePart">
                            </label>
                            <label class="ed-wiz-field">
                                <span class="site-label ed-wiz-label"><x-phosphor-clock-light aria-hidden="true" /> Hora</span>
                                <input class="site-input" type="time" x-model="eventTimePart">
                            </label>
                        </div>
                        <p class="ed-wiz-hint" x-show="eventInPast" x-cloak>
                            <x-phosphor-warning-circle-light aria-hidden="true" /> Esa fecha ya pasó.
                        </p>
                    </div>
                </template>
                <template x-if="isCard">
                    <div class="ed-wizard__fields">
                        <label class="ed-wiz-field">
                            <span class="site-label ed-wiz-label"><x-phosphor-heart-light aria-hidden="true" /> Para</span>
                            <input class="site-input" maxlength="80" type="text" x-model="wizardCard.para" placeholder="Ej. Ana">
                        </label>
                        <label class="ed-wiz-field">
                            <span class="site-label ed-wiz-label"><x-phosphor-pen-nib-light aria-hidden="true" /> De parte de</span>
                            <input class="site-input" maxlength="80" type="text" x-model="wizardCard.de" placeholder="Ej. Luis">
                        </label>
                    </div>
                </template>

                {{-- Idioma de los textos y botones de la invitación: dos pestañas con subrayado --}}
                <div class="ed-wiz-field" role="radiogroup" aria-label="Idioma de la invitación">
                    <span class="site-label ed-wiz-label"><x-phosphor-translate-light aria-hidden="true" /> Idioma</span>
                    <div class="ed-wiz-tabs">
                        <template x-for="option in (config.localeOptions ?? [])" :key="option.value">
                            <button type="button" role="radio" class="ed-wiz-tab" @click="meta.locale = option.value"
                                :class="{ 'is-on': meta.locale === option.value }" :aria-checked="(meta.locale === option.value).toString()"
                                x-text="option.label"></button>
                        </template>
                    </div>
                </div>

                {{-- Plan: el evento es de un cliente (se le crea su acceso después) o del propio revendedor (queda a su nombre) --}}
                @if(($editorMode ?? 'admin') === 'reseller')
                    <div class="ed-wiz-field" role="radiogroup" aria-label="De quién es el evento">
                        <span class="site-label ed-wiz-label"><x-phosphor-user-circle-light aria-hidden="true" /> ¿De quién es el evento?</span>
                        <div class="ed-wiz-tabs">
                            <button type="button" role="radio" class="ed-wiz-tab" @click="ownEvent = false"
                                :class="{ 'is-on': !ownEvent }" :aria-checked="(!ownEvent).toString()">De un cliente</button>
                            <button type="button" role="radio" class="ed-wiz-tab" @click="ownEvent = true"
                                :class="{ 'is-on': ownEvent }" :aria-checked="ownEvent.toString()">Mío</button>
                        </div>
                    </div>
                @endif

                @if(($editorMode ?? 'admin') === 'admin')
                    <div class="ed-wizard__fields ed-wizard__fields--admin">
                        <label class="ed-wiz-field">
                            <span class="site-label ed-wiz-label"><x-phosphor-user-light aria-hidden="true" /> Cliente</span>
                            <select class="site-input" x-model="meta.user_id">
                                <option value="">Sin cliente por ahora</option>
                                <template x-for="client in clients" :key="client.id">
                                    <option :value="client.id" x-text="client.name"></option>
                                </template>
                            </select>
                        </label>
                        <div class="ed-wiz-field" x-show="!isCard" role="radiogroup" aria-label="Paquete">
                            <span class="site-label ed-wiz-label"><x-phosphor-package-light aria-hidden="true" /> Paquete</span>
                            <div class="ed-wiz-tabs">
                                <template x-for="option in (config.packageOptions ?? [])" :key="option.value">
                                    <button type="button" role="radio" class="ed-wiz-tab" @click="meta.package = option.value" :title="option.hint"
                                        :class="{ 'is-on': meta.package === option.value }" :aria-checked="(meta.package === option.value).toString()"
                                        x-text="option.label"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- Seguir / Crear: pegado abajo, al alcance del pulgar; en el primer paso solo si ya hay un evento elegido (al volver) --}}
        <footer class="ed-wizard__foot" x-show="wizardStep > 1 || (selectedEventType && !eventTypeDisabledReason(selectedEventType))">
            <p class="ed-wizard__error" x-show="wizardError" x-cloak role="alert">
                <x-phosphor-warning-circle-light aria-hidden="true" />
                <span x-text="wizardError"></span>
            </p>
            <button type="button" class="site-btn site-btn--lg ed-wizard__next" x-show="wizardStep < 3" @click="wizardStep++; wizardError = ''">
                Seguir <x-phosphor-arrow-right-light aria-hidden="true" />
            </button>
            <button type="button" class="site-btn site-btn--lg ed-wizard__next" x-show="wizardStep === 3" x-cloak @click="finishWizard()"
                :disabled="wizardSaving" :class="{ 'is-loading': wizardSaving }">
                <span x-text="wizardSaving ? 'Creando…' : 'Crear invitación'"></span>
                <x-phosphor-arrow-right-light aria-hidden="true" />
            </button>
        </footer>
    </div>
</div>
