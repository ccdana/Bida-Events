{{--
    Respuesta en la tarjeta de amor: quien la recibe elige una flor (cada una con su significado) y
    escribe unas palabras; al enviar, la flor sube y se va volando entre pétalos. La flor se guarda en
    guest_contributions.reaction y el mensaje en content_text (type = card_reply): lo ve el remitente
    en su panel, no se publica. Las flores las define la plantilla (InvitationTemplates, «reactions»).
    Reemplaza a modules/respuesta solo en esta plantilla; flowerReply en resources/js/cards/amor/reply.js.
    Recibe $data (titulo, descripcion, placeholder), $page e $invitation.
--}}
@php
    $replyTo = trim((string) ($modulos['dedicatoria']['de'] ?? ''));
    $flowers = $page->reactions;
@endphp

<section class="inv-section reveal inv-reply inv-bouquet-scene" id="respuesta"
    x-data="flowerReply(@js([
        'slug' => $invitation->slug,
        'guestToken' => $page->guestToken,
        'isPreview' => ! empty($isPreview),
        'to' => $replyTo,
        'flowers' => $flowers,
    ]))"
    :class="{ 'is-sent': sent }">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'compact' => true,
            'eyebrow' => $replyTo !== '' ? 'Para '.$replyTo : 'Tu respuesta',
            'title' => ($data['titulo'] ?? null) ?: 'Respóndele',
            'intro' => ($data['descripcion'] ?? null) ?: 'Elige una flor y, si quieres, escribe unas palabras: le llegan solo a quien te mandó la carta.',
        ])

        <form class="inv-bouquet" data-needs-js x-show="!sent" @submit.prevent="submit">
            @if($flowers)
                <fieldset class="inv-bouquet__field">
                    <legend class="inv-label">Elige una flor</legend>

                    <div class="inv-bouquet__options">
                        @foreach($flowers as $code => $flower)
                            <label class="inv-bouquet__option" :class="{ 'is-chosen': reaction === @js($code) }" style="--i: {{ $loop->index }}">
                                <input type="radio" name="reaction" value="{{ $code }}" x-model="reaction" class="sr-only">
                                @include('invitations.partials.amor.flower-art', ['flower' => $code])
                                <span class="inv-bouquet__name">{{ \Illuminate\Support\Str::after($flower['label'], ' ') }}</span>
                            </label>
                        @endforeach
                    </div>

                    {{-- Lo que dice la flor elegida; se vuelve a animar con cada cambio --}}
                    <div class="inv-bouquet__meaning" aria-live="polite">
                        <p class="inv-bouquet__meaning-empty" x-show="!reaction">Cada flor dice algo distinto: elige la tuya</p>
                        <template x-for="code in (reaction ? [reaction] : [])" :key="code">
                            <div class="inv-bouquet__card">
                                <p class="inv-bouquet__card-title" x-text="flowers[code].meaning"></p>
                                <p class="inv-bouquet__card-text" x-text="flowers[code].phrase"></p>
                            </div>
                        </template>
                    </div>
                </fieldset>
            @endif

            <label class="inv-label" for="reply-message">Tu mensaje <span class="inv-bouquet__optional" x-show="reaction">(opcional)</span></label>
            <textarea id="reply-message" class="inv-input inv-reply__input" rows="3" maxlength="500" x-model="message"
                placeholder="{{ ($data['placeholder'] ?? null) ?: 'Escribe tu respuesta…' }}"></textarea>
            <p class="inv-help"><span x-text="500 - message.length">500</span> caracteres disponibles</p>

            <div class="inv-actions">
                <button type="submit" class="inv-btn inv-btn--block" x-ref="submit" :disabled="sending || (!reaction && !message.trim())">
                    <span x-text="sending ? 'Enviando…' : (reaction ? `Enviar ${flowers[reaction].label.toLowerCase()}` : 'Enviar respuesta')">Enviar respuesta</span>
                </button>
            </div>
        </form>

        {{-- Enviada: la flor elegida se va volando y queda el aviso --}}
        <div class="inv-bouquet__sent" x-show="sent" x-cloak role="status">
            <div class="inv-bouquet__flight" aria-hidden="true">
                @foreach($flowers as $code => $flower)
                    <span x-show="reaction === @js($code)">@include('invitations.partials.amor.flower-art', ['flower' => $code])</span>
                @endforeach
                <svg x-show="!reaction" class="inv-reply__plane" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M6 30L58 8 44 56 30 38z"/><path d="M58 8L30 38v14l7-9"/></svg>
            </div>
            <p class="inv-reply__sent-title" x-text="sentTitle"></p>
            <p class="inv-reply__sent-text">¡Gracias por escribir!</p>
        </div>

        <p class="inv-status is-error" x-show="error" x-cloak x-text="error" aria-live="assertive"></p>

        <noscript>
            <p class="inv-noscript">Para responder desde aquí necesitas activar JavaScript en tu navegador.</p>
        </noscript>
    </div>
</section>
