{{--
    Página de las plantillas de la colección «nueva». Todas comparten el armado (cabecera, menú,
    modo historia, música, secciones y pie) y cada una aporta lo suyo desde su carpeta de parciales:
    intro (la apertura que se toca), hero (la portada) y, si la tiene, su propia vista de algún módulo
    (InvitationTemplates, clave «partials»). La página lleva la clase de su tema (inv-carta,
    inv-caminos…), así los estilos de la clásica de su evento no la alcanzan.

    Parámetros: template (clave del catálogo), parts (carpeta de parciales), footerDate (formato de la fecha del pie).
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, $template);
    $invCopy = $page->copy;
    // La apertura aparece en cada visita; solo se omite en la vista previa del editor
    $showIntro = empty($isPreview);
@endphp
<!DOCTYPE html>
{{-- La clase no-js la quita el primer script de la cabecera (ver shell/head) --}}
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    @if($showIntro)
        @include('invitations.partials.shell.cover-script', ['coverSelector' => '.inv-themed-intro'])
    @endif
</head>
<body class="inv-page inv-{{ $page->theme }} inv-themed overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">{{ $invCopy['skip_link'] ?? 'Saltar al contenido' }}</a>

    @if($showIntro)
        @include("invitations.partials.{$parts}.intro")
    @endif

    @include('invitations.partials.shell.themed-ambient')

    @include('invitations.partials.shell.nav')
    {{-- También se puede recorrer como historia, escena por escena (botón de la esquina o ?historias) --}}
    @include('invitations.partials.story.invitation')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include("invitations.partials.{$parts}.hero")

    <main id="contenido">
        @include('invitations.partials.shell.modules')
    </main>

    @include('invitations.partials.shell.footer', [
        'footerClass' => "inv-{$page->theme}-footer",
        'footerName' => $page->displayName,
        'footerDate' => \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat($footerDate ?? 'l j \d\e F \d\e Y')),
        'footerOrnament' => 'line',
    ])

    @include('invitations.partials.shell.scripts')

    @if($showIntro)
        @include('invitations.partials.shell.cover-component')
    @endif
</body>
</html>
