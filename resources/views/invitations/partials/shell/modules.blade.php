{{--
    Secciones de la invitación en el orden que define cada plantilla (App\Support\InvitationTemplates).
    Hereda de la plantilla: $page, $invitation, $modulos, $guest, $pollResults, $calendarUrl, $playlistSongs y $fotomuralPhotos.
--}}
@php($moduleRegistry = app(\App\Modules\ModuleRegistry::class))

@if($page->showsGuestBanner())
    @include('invitations.partials.guest-banner', ['guest' => $guest, 'showStatus' => $page->rsvpMode === \App\Support\Packages::RSVP_PASS])
@endif

@if($page->isPostEvent && $page->visible('post_evento') && !empty($page->welcome['mensaje_post_evento']))
    <section class="inv-section reveal">
        <div class="inv-wrap">
            <p class="inv-thanks__text">{{ $page->welcome['mensaje_post_evento'] }}</p>
        </div>
    </section>
@endif

@foreach($page->order as $module)
    @if($page->partialFor($module))
        {{-- La plantilla trae su propia vista para este módulo (InvitationTemplates, clave «partials») --}}
        @if($page->visible($module))
            @include($page->partialFor($module), ['data' => $modulos[$module] ?? []])
        @endif
    @elseif($module === 'cuenta_regresiva' && $page->visible('cuenta_regresiva'))
        @include('invitations.partials.countdown', [
            'eventDate' => $page->eventDate->toIso8601String(),
            'eventLabel' => $page->eventLabel,
            'calendarUrl' => $calendarUrl,
            'agendar' => $page->visible('agendar'),
        ])
    @elseif($module === 'cuenta_regresiva' && $page->visible('agendar'))
        <section class="inv-section reveal" id="agendar">
            <div class="inv-wrap">
                @include('invitations.partials.section-header', [
                    'lottie' => 'calendar',
                    'eyebrow' => 'Guarda la fecha',
                    'title' => 'Agéndalo',
                    'intro' => $page->eventLabel,
                ])
                <div class="inv-actions">
                    <button type="button" class="inv-btn inv-btn--block" data-url="{{ $calendarUrl }}" onclick="openCalendar(this.dataset.url)">
                        {{ $invCopy['calendar_add'] ?? 'Agregar a Google Calendar' }}
                    </button>
                </div>
            </div>
        </section>
    @elseif($module === 'video' && $page->visible('video'))
        @include('invitations.partials.video', ['video' => $modulos['video']])
    @elseif($module === 'galeria' && $page->visible('galeria'))
        @include('invitations.partials.gallery-stack', ['galeria' => $modulos['galeria']])
    @elseif($module === 'itinerario' && $page->visible('itinerario'))
        @include('invitations.partials.itinerary', ['itinerario' => $modulos['itinerario'] ?? []])
    @elseif($module === 'dress_code' && $page->visible('dress_code'))
        @include('invitations.partials.dress-code', ['dressCode' => $modulos['dress_code'] ?? []])
    @elseif($module === 'destacados' && $page->visible('destacados'))
        @include('invitations.partials.destacados', ['destacados' => $modulos['destacados'] ?? []])
    @elseif($module === 'ubicacion' && $page->visible('ubicacion'))
        @include('invitations.partials.location', ['ubicacion' => $modulos['ubicacion'] ?? []])
    @elseif($module === 'hashtag' && $page->visible('hashtag'))
        @include('invitations.partials.hashtag', ['hashtag' => $modulos['hashtag'] ?? []])
    @elseif($module === 'encuestas' && $page->visible('encuestas'))
        @include('invitations.partials.polls', [
            'encuestas' => $modulos['encuestas'] ?? [],
            'pollResults' => $pollResults,
            'slug' => $invitation->slug,
            'guestToken' => $page->guestToken,
        ])
    @elseif($module === 'playlist' && $page->visible('playlist'))
        @include('invitations.partials.playlist', [
            'playlist' => $modulos['playlist'] ?? [],
            'slug' => $invitation->slug,
            'guestToken' => $page->guestToken,
            'songs' => $playlistSongs ?? [],
        ])
    @elseif($module === 'regalos' && $page->visible('regalos'))
        @include('invitations.partials.regalos', ['regalos' => $modulos['regalos'] ?? []])
    @elseif($module === 'rsvp' && $page->showsRsvp() && $page->rsvpMode === \App\Support\Packages::RSVP_WHATSAPP)
        {{-- Paquete Estándar: el invitado arma su respuesta y se la envía por WhatsApp al organizador --}}
        @include('invitations.partials.rsvp-whatsapp', [
            'rsvp' => $modulos['rsvp'] ?? [],
            'guest' => $guest,
        ])
    @elseif($module === 'rsvp' && $page->showsRsvp())
        @include('invitations.partials.rsvp', [
            'rsvp' => $modulos['rsvp'] ?? [],
            'guest' => $guest,
            'slug' => $invitation->slug,
        ])
    @elseif($module === 'fotomural' && $page->visible('fotomural'))
        @include('invitations.partials.fotomural', [
            'slug' => $invitation->slug,
            'guestToken' => $page->guestToken,
            'photos' => $fotomuralPhotos ?? [],
            'readOnly' => $page->isPostEvent,
        ])
    @elseif($module === 'post_evento' && $page->isPostEvent && $page->visible('post_evento'))
        @include('invitations.partials.post-event', ['postEvento' => $modulos['post_evento'] ?? []])
    @elseif($page->visible($module) && $moduleRegistry->has($module) && $moduleRegistry->get($module)->partial())
        {{-- Módulos con vista propia (tarjetas y los que se sumen): app/Modules --}}
        @include($moduleRegistry->get($module)->partial(), ['data' => $modulos[$module] ?? []])
    @endif
@endforeach
