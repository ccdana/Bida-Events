{{--
    Vista de la colección «tendencias» (App\Support\TrendTemplates): la misma para todos los diseños y
    todos los eventos. La clave del catálogo (boda-mosaico, xv-cristal…) dice qué diseño dibujar; el
    armado es el común de partials/shell/themed y lo propio de cada diseño vive en
    partials/tendencias/{diseño} (intro, hero y sus módulos) y en css/invitation/tendencias/{diseño}.css.
--}}
@php
    $trendTemplate = $templateKey ?? $invitation->template;
    $trendDesign = \App\Support\InvitationTemplates::theme($trendTemplate);
@endphp
@include('invitations.partials.shell.themed', [
    'template' => $trendTemplate,
    'parts' => "tendencias.{$trendDesign}",
    'styles' => ["resources/css/invitation/tendencias/{$trendDesign}.css"],
    'headExtra' => 'invitations.partials.tendencias.head',
    'bodyClass' => 'inv-trend',
    'footerDate' => 'l j \d\e F \d\e Y',
])
