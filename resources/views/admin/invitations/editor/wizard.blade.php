{{--
    Asistente de creación: antes de ver la invitación se elige el evento, el diseño y se escriben los
    datos que la arman (los nombres, la fecha y la hora; en una tarjeta, para quién es). Al terminar se
    crea con las partes del evento ya llenas de ejemplo y se abre la vista previa para elegir las partes
    y cambiar lo que haga falta tocándolo. Lógica en resources/js/admin/editor.js (wizardChooseEvent, finishWizard).
--}}
<div class="ed-wizard" x-show="wizardOpen" x-cloak role="dialog" aria-modal="true" aria-labelledby="wizard-title">
    <div class="ed-wizard__inner">
        <ol class="ed-wizard__steps" aria-label="Pasos">
            <template x-for="(step, index) in ['El evento', 'El diseño', 'Los datos']" :key="index">
                <li class="ed-wizard__step" :class="{ 'is-current': wizardStep === index + 1, 'is-done': wizardStep > index + 1 }">
                    <button type="button" :disabled="wizardStep <= index + 1" @click="wizardStep = index + 1; wizardError = ''"
                        :aria-current="wizardStep === index + 1 ? 'step' : null">
                        <span class="ed-wizard__num" x-text="wizardStep > index + 1 ? '✓' : index + 1"></span>
                        <span x-text="step"></span>
                    </button>
                </li>
            </template>
        </ol>

        {{-- 1. El evento --}}
        <section class="ed-wizard__body" x-show="wizardStep === 1">
            <h1 id="wizard-title" class="ed-wizard__title">¿Qué vas a celebrar?</h1>
            <p class="ed-wizard__lead">Elige el tipo de evento. Después podrás cambiar todo.</p>
            <template x-for="group in wizardEventGroups" :key="group.name">
                <div class="ed-wizard__group">
                    <h2 class="ed-wizard__label" x-text="group.name"></h2>
                    <div class="ed-wizard__grid">
                        <template x-for="type in group.types" :key="type.id">
                            <button type="button" class="ed-wiz-tile" @click="wizardChooseEvent(type)"
                                :class="{ 'is-on': String(meta.event_type_id) === String(type.id) }"
                                :disabled="!!eventTypeDisabledReason(type)">
                                <span class="ed-wiz-tile__name" x-text="type.name"></span>
                                <span class="ed-wiz-tile__note" x-show="eventTypeDisabledReason(type)" x-text="eventTypeDisabledReason(type)"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </section>

        {{-- 2. El diseño --}}
        <section class="ed-wizard__body" x-show="wizardStep === 2" x-cloak>
            <h1 class="ed-wizard__title">Elige el diseño</h1>
            <p class="ed-wizard__lead">Toca el que más te guste. Los colores y las letras se pueden cambiar después.</p>
            <div class="ed-wizard__designs">
                <template x-for="option in templatesForEventType()" :key="option.value">
                    <button type="button" class="ed-design" @click="wizardChooseTemplate(option)"
                        :class="{ 'is-on': meta.template === option.value }" :disabled="!!option.disabledReason">
                        <span class="ed-design__shot">
                            <img x-show="option.capture" :src="option.capture || ''" alt="" loading="lazy">
                            <span class="ed-design__swatches" x-show="!option.capture" aria-hidden="true">
                                <i :style="'background:' + (option.palette?.background ?? '#fff')"></i>
                                <i :style="'background:' + (option.palette?.accent ?? '#eee')"></i>
                                <i :style="'background:' + (option.palette?.primary ?? '#999')"></i>
                                <i :style="'background:' + (option.palette?.text ?? '#222')"></i>
                            </span>
                        </span>
                        <span class="ed-design__name" x-text="option.label"></span>
                        <span class="ed-design__desc" x-text="option.disabledReason || option.description"></span>
                    </button>
                </template>
            </div>
        </section>

        {{-- 3. Los datos --}}
        <section class="ed-wizard__body" x-show="wizardStep === 3" x-cloak>
            <h1 class="ed-wizard__title" x-text="isCard ? '¿Para quién es la tarjeta?' : 'Los datos de tu evento'"></h1>
            <p class="ed-wizard__lead">Con esto armamos tu invitación. Todo lo demás lo cambias tocándolo en la vista previa.</p>

            <div class="ed-wizard__form">
                <template x-if="!isCard">
                    <div class="ed-wizard__fields">
                        <template x-for="field in heroFields" :key="field.key">
                            <label class="ed-wiz-field">
                                <span class="ed-wiz-field__label" x-text="field.label"></span>
                                <input class="ed-wiz-input" :type="field.type || 'text'" :inputmode="field.type === 'number' ? 'numeric' : null"
                                    :value="modules.bienvenida[field.key] ?? ''"
                                    @input="modules.bienvenida[field.key] = $event.target.value; syncHeroName()"
                                    :placeholder="field.placeholder">
                            </label>
                        </template>
                        <div class="ed-wizard__pair">
                            <label class="ed-wiz-field">
                                <span class="ed-wiz-field__label">¿Qué día es?</span>
                                <input class="ed-wiz-input" type="date" x-model="eventDatePart">
                            </label>
                            <label class="ed-wiz-field">
                                <span class="ed-wiz-field__label">¿A qué hora empieza?</span>
                                <input class="ed-wiz-input" type="time" x-model="eventTimePart">
                            </label>
                        </div>
                    </div>
                </template>
                <template x-if="isCard">
                    <div class="ed-wizard__fields">
                        <label class="ed-wiz-field">
                            <span class="ed-wiz-field__label">¿Para quién es?</span>
                            <input class="ed-wiz-input" type="text" x-model="wizardCard.para" placeholder="Ej. Ana">
                        </label>
                        <label class="ed-wiz-field">
                            <span class="ed-wiz-field__label">¿De parte de quién?</span>
                            <input class="ed-wiz-input" type="text" x-model="wizardCard.de" placeholder="Ej. Luis">
                        </label>
                    </div>
                </template>

                {{-- Idioma de los textos y botones de la invitación --}}
                <div class="ed-wizard__fields">
                    <div class="ed-wiz-field" role="radiogroup" aria-label="Idioma de la invitación">
                        <span class="ed-wiz-field__label">Idioma de la invitación</span>
                        <div class="ed-wizard__packages">
                            <template x-for="option in (config.localeOptions ?? [])" :key="option.value">
                                <button type="button" role="radio" class="ed-wiz-tile ed-wiz-tile--small" @click="meta.locale = option.value"
                                    :class="{ 'is-on': meta.locale === option.value }" :aria-checked="(meta.locale === option.value).toString()">
                                    <span class="ed-wiz-tile__name" x-text="option.label"></span>
                                </button>
                            </template>
                        </div>
                        <span class="ed-wiz-field__hint">Tus invitados también pueden traducirla con su navegador.</span>
                    </div>
                </div>

                @if(($editorMode ?? 'admin') === 'admin')
                    <div class="ed-wizard__fields ed-wizard__fields--admin">
                        <label class="ed-wiz-field">
                            <span class="ed-wiz-field__label">Cliente</span>
                            <select class="ed-wiz-input" x-model="meta.user_id">
                                <option value="">Sin cliente por ahora</option>
                                <template x-for="client in clients" :key="client.id">
                                    <option :value="client.id" x-text="client.name"></option>
                                </template>
                            </select>
                            <span class="ed-wiz-field__hint">Puedes asignarlo o crearlo después en «Datos del evento».</span>
                        </label>
                        <div class="ed-wiz-field" x-show="!isCard" role="radiogroup" aria-label="Paquete">
                            <span class="ed-wiz-field__label">Paquete</span>
                            <div class="ed-wizard__packages">
                                <template x-for="option in (config.packageOptions ?? [])" :key="option.value">
                                    <button type="button" role="radio" class="ed-wiz-tile ed-wiz-tile--small" @click="meta.package = option.value"
                                        :class="{ 'is-on': meta.package === option.value }" :aria-checked="(meta.package === option.value).toString()">
                                        <span class="ed-wiz-tile__name" x-text="option.label"></span>
                                        <span class="ed-wiz-tile__note" x-text="option.hint"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

                <p class="ed-wizard__error" x-show="wizardError" x-cloak x-text="wizardError" role="alert"></p>
            </div>
        </section>

        <footer class="ed-wizard__foot">
            <button type="button" class="ed-wiz-btn" x-show="wizardStep > 1" @click="wizardStep--; wizardError = ''">Atrás</button>
            <span class="flex-1"></span>
            <button type="button" class="ed-wiz-btn ed-wiz-btn--primary" x-show="wizardStep === 1"
                @click="selectedEventType && !eventTypeDisabledReason(selectedEventType) ? wizardStep = 2 : wizardError = 'Elige qué vas a celebrar.'">Seguir</button>
            <button type="button" class="ed-wiz-btn ed-wiz-btn--primary" x-show="wizardStep === 2" @click="wizardStep = 3">Seguir</button>
            <button type="button" class="ed-wiz-btn ed-wiz-btn--primary" x-show="wizardStep === 3" @click="finishWizard()" :disabled="wizardSaving">
                <span x-text="wizardSaving ? 'Creando tu invitación…' : 'Crear invitación'"></span>
            </button>
        </footer>
        <p class="ed-wizard__error ed-wizard__error--foot" x-show="wizardError && wizardStep === 1" x-cloak x-text="wizardError" role="alert"></p>
    </div>
</div>
