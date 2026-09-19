{{--
    «The Story We Write Together» (perfil App\EventProfiles\StoryCardProfile): la historia de una
    pareja en cuatro actos que se recorren con scroll, sobre un escenario fijo que cambia con ellos:
      I   El reflejo       la luna solo se ve en el agua; la primera foto, como a través del agua
      II  La marea         subimos desde la superficie; los momentos salen del agua uno a uno
      III De frente        la luna al frente, sin filtros: la anécdota, la dedicatoria, la pareja hoy
      IV  La constelación  cielo a lo Van Gogh: reflexión, promesa y la respuesta de quien la lee
    Los actos son fijos (partials/historia) y leen los módulos directamente; «order» solo arma el
    menú. Un dato vacío se cubre con el texto *_fallback de su acto (InvitationTemplates, «copy»).
    Estilos en resources/css/cards/historia.css; JS en resources/js/cards/historia.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::WE_STORY_TOGETHER);
    $invCopy = $page->copy;

    // Un módulo apagado cuenta como vacío: su acto usa el texto de respaldo
    $moduleData = fn (string $code) => $page->visible($code) ? (array) ($modulos[$code] ?? []) : [];
    $filled = fn ($value) => is_string($value) && trim($value) !== '' ? trim($value) : null;
    // Una línea en blanco separa párrafos, como en la dedicatoria
    $paragraphs = fn (string $text) => array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/u', $text) ?: [])));
    $photoList = fn ($photos) => collect(is_array($photos) ? $photos : [])
        ->map(fn ($photo) => is_array($photo)
            ? ['url' => $photo['url'] ?? null, 'alt' => trim((string) ($photo['alt'] ?? ''))]
            : ['url' => $photo, 'alt' => ''])
        ->filter(fn ($photo) => is_string($photo['url']) && $photo['url'] !== '')
        ->values();

    $story = $moduleData('relato');
    $dedication = $moduleData('dedicatoria');
    $milestone = $moduleData('juntos_desde');

    $names = $page->names();
    $coupleLabel = implode(' y ', $names);
    $cardTo = $filled($dedication['para'] ?? null) ?? ($guest?->name ?? '');
    $cardFrom = $filled($dedication['de'] ?? null) ?? '';

    $metOn = null;
    if ($filled($milestone['fecha'] ?? null)) {
        try {
            $metOn = \Illuminate\Support\Carbon::parse($milestone['fecha'])->locale('es');
        } catch (\Throwable) {
            $metOn = null;
        }
    }

    // «7 años y 3 meses»: el tiempo que llevan escribiendo la historia
    $together = null;
    if ($metOn && $metOn->isPast()) {
        $span = $metOn->diff(now());
        $plural = fn (int $n, string $one, string $many) => $n.' '.($n === 1 ? $one : $many);
        $together = match (true) {
            $span->y > 0 => $plural($span->y, 'año', 'años').($span->m > 0 ? ' y '.$plural($span->m, 'mes', 'meses') : ''),
            $span->m > 0 => $plural($span->m, 'mes', 'meses'),
            default => $plural(max(1, $span->d), 'día', 'días'),
        };
    }

    $moments = collect($story['momentos'] ?? [])
        ->filter(fn ($moment) => is_array($moment) && ($filled($moment['titulo'] ?? null) || $filled($moment['descripcion'] ?? null)))
        ->values();
    $quote = \App\Modules\Card\StoryActsModule::quote($story['cita'] ?? null);
    $couplePhotos = $photoList($moduleData('galeria')['fotos'] ?? [])->take(3);
    $song = $page->hasPlayer ? $page->music : [];

    $showIntro = empty($isPreview);
@endphp
<!DOCTYPE html>
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    {{-- La voz poética del tema (cursivas de nombres, actos y citas) va fija en Cormorant: el cliente elige títulos y cuerpo --}}
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;1,400;1,500&display=swap" rel="stylesheet">
    @vite(['resources/css/cards/historia.css'])
    @if($showIntro)
        @include('invitations.partials.shell.cover-script', ['coverSelector' => '.story-cover'])
    @endif
</head>
<body class="inv-page inv-historia overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" data-card="historia" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">Saltar al contenido</a>

    @if($showIntro)
        @include('invitations.partials.historia.cover')
    @endif

    @include('invitations.partials.shell.nav')

    @include('invitations.partials.historia.stage')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.historia.act-1')

    <main id="contenido">
        @include('invitations.partials.historia.act-2')
        @include('invitations.partials.historia.act-3')
        @include('invitations.partials.historia.act-4')
    </main>

    @include('invitations.partials.shell.footer', [
        'footerClass' => 'story-footer',
        'footerName' => $coupleLabel,
        'footerDate' => \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y')),
        'footerOrnament' => null,
    ])

    @include('invitations.partials.shell.scripts')

    @if($showIntro)
        @include('invitations.partials.shell.cover-component')
    @endif
</body>
</html>
