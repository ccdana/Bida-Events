{{--
    Confirmación por WhatsApp (paquete Estándar): las mismas preguntas que la confirmación con pase,
    pero la respuesta no se guarda aquí. Al final se abre WhatsApp con el mensaje listo para el
    organizador (número en el módulo rsvp_whatsapp; título y mensaje, los de rsvp). En el enlace personal ya van el nombre y los lugares del
    invitado; en el enlace general, el invitado escribe su nombre.
    Recibe $rsvp y $guest (o null); hereda $page, $invitation e $invCopy de la plantilla.
--}}
@php
    $maxPasses = $guest ? max(1, (int) $guest->passes_allocated) : 10;
    $eventText = $invitation->title.' ('.$page->eventDate->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F')).')';
@endphp
<section class="inv-section reveal inv-rsvp" id="rsvp" data-ed="rsvp_whatsapp"
    x-data="rsvpWhatsapp(@js($page->rsvpWhatsapp), @js($eventText), {{ $maxPasses }}, @js($guest?->name ?? ''))">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'lottie' => 'rsvp',
            'eyebrow' => $invCopy['rsvp_eyebrow'] ?? __('Confirma tu asistencia'),
            'title' => $rsvp['titulo_confirmacion'] ?? __('¿Nos acompañas?'),
            'intro' => $rsvp['mensaje_personalizado'] ?? null,
        ])

        @if($guest)
            <p class="inv-rsvp__greeting">
                {!! __('Hola :name, reservamos :places para ti.', [
                    'name' => '<strong>'.e($guest->name).'</strong>',
                    'places' => '<strong>'.e($maxPasses === 1 ? __('1 lugar') : __(':count lugares', ['count' => $maxPasses])).'</strong>',
                ]) !!}
            </p>
        @endif

        <div class="inv-rsvp__form">
            @unless($guest)
                <div class="inv-rsvp__step">
                    <label class="inv-label inv-rsvp__label" for="rsvp-wa-name">{{ $invCopy['rsvp_whatsapp_name'] ?? __('Tu nombre') }}</label>
                    <input id="rsvp-wa-name" type="text" class="inv-input" x-model="name" maxlength="80" autocomplete="name" placeholder="{{ __('Ej. Familia Rojas') }}">
                </div>
            @endunless

            <fieldset class="inv-rsvp__step">
                <legend class="inv-rsvp__legend">{{ $invCopy['rsvp_attend_question'] ?? __('¿Asistirás?') }}</legend>
                <div class="inv-rsvp__choices">
                    <button type="button" class="inv-choice" :class="{ 'is-selected': attending === true }"
                        :aria-pressed="(attending === true).toString()" @click="attending = true">{{ $invCopy['rsvp_yes'] ?? __('Sí, asistiré') }}</button>
                    <button type="button" class="inv-choice" :class="{ 'is-selected': attending === false }"
                        :aria-pressed="(attending === false).toString()" @click="attending = false">{{ $invCopy['rsvp_no'] ?? __('No podré ir') }}</button>
                </div>
            </fieldset>

            <fieldset class="inv-rsvp__step" x-show="attending === true" x-cloak x-transition.opacity>
                <legend class="inv-rsvp__legend">{{ $invCopy['rsvp_people_question'] ?? __('¿Cuántas personas vendrán?') }}</legend>
                @if($maxPasses > 1)
                    <div class="inv-stepper">
                        <button type="button" class="inv-stepper__btn" @click="passes = Math.max(1, passes - 1)" :disabled="passes <= 1" aria-label="{{ __('Una persona menos') }}">−</button>
                        <output class="inv-stepper__value" x-text="passes" aria-live="polite">1</output>
                        <button type="button" class="inv-stepper__btn" @click="passes = Math.min(maxPasses, passes + 1)" :disabled="passes >= maxPasses" aria-label="{{ __('Una persona más') }}">+</button>
                    </div>
                @endif

                <label class="inv-label inv-rsvp__label" for="rsvp-wa-dietary">{{ $invCopy['rsvp_dietary_label'] ?? __('Alergias o restricciones alimentarias (opcional)') }}</label>
                <textarea id="rsvp-wa-dietary" class="inv-input" x-model="dietary" rows="2" maxlength="300" placeholder="{{ __('Ej. vegetariano, sin gluten') }}"></textarea>
            </fieldset>

            <div class="inv-rsvp__submit">
                {{-- Enlace real (no window.open): el celular lo abre en la app de WhatsApp sin bloqueos --}}
                <a class="inv-btn inv-btn--block" :class="{ 'is-disabled': !ready }" :href="ready ? url : null"
                    target="_blank" rel="noopener" :aria-disabled="(!ready).toString()">
                    {{ $invCopy['rsvp_whatsapp_submit'] ?? __('Enviar por WhatsApp') }}
                </a>
                <p class="inv-help" x-show="!ready" x-text="hint"></p>
                <p class="inv-help" x-show="ready" x-cloak>{{ $invCopy['rsvp_whatsapp_help'] ?? __('Se abre WhatsApp con tu respuesta lista; solo tienes que enviarla.') }}</p>
            </div>
        </div>
    </div>
</section>

@once
<script>
// Arma el mensaje de confirmación para WhatsApp con lo que eligió el invitado
function rsvpWhatsapp(number, eventText, maxPasses, guestName) {
    // El mensaje sale en el idioma de la invitación; :name, :event, :count y :diet los llena el navegador
    const messages = @js([
        'yes' => __('Hola, soy :name. Confirmo mi asistencia a :event.'),
        'yesPeople' => __('Hola, soy :name. Confirmo mi asistencia a :event con :count personas.'),
        'no' => __('Hola, soy :name. Lamentablemente no podré asistir a :event.'),
        'diet' => __('Alergias o restricciones: :diet.'),
    ]);
    const fill = (text, values) => text.replace(/:(\w+)/g, (match, key) => values[key] ?? match);

    return {
        name: guestName,
        attending: null,
        passes: 1,
        dietary: '',
        maxPasses: maxPasses,
        get ready() {
            return this.attending !== null && this.name.trim().length > 1;
        },
        get hint() {
            return this.name.trim().length > 1 ? @js(__('Elige una opción para continuar.')) : @js(__('Escribe tu nombre y elige una opción.'));
        },
        get url() {
            const who = this.name.trim();
            const values = { name: who, event: eventText, count: this.passes, diet: this.dietary.trim() };
            let text = this.attending
                ? fill(this.passes > 1 ? messages.yesPeople : messages.yes, values)
                : fill(messages.no, values);
            if (this.attending && values.diet) {
                text += ' ' + fill(messages.diet, values);
            }
            return `https://wa.me/${number}?text=${encodeURIComponent(text)}`;
        },
    };
}
</script>
@endonce
