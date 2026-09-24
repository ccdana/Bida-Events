<?php

use App\EventProfiles\AdventureBookProfile;
use App\EventProfiles\BaptismProfile;
use App\EventProfiles\BirthdayProfile;
use App\EventProfiles\CanvasProfile;
use App\EventProfiles\GraduationProfile;
use App\EventProfiles\HalloweenProfile;
use App\EventProfiles\LoveCardProfile;
use App\EventProfiles\StoryCardProfile;
use App\EventProfiles\WeddingProfile;
use App\EventProfiles\XvProfile;

/*
|--------------------------------------------------------------------------
| Perfiles de evento
|--------------------------------------------------------------------------
|
| Cada tipo de evento o temporada, con sus módulos, su vocabulario y sus
| datos obligatorios. Las plantillas de App\Support\InvitationTemplates
| apuntan a uno por su código ("event"). Ver docs/temporadas.md.
|
*/

return [
    XvProfile::class,
    WeddingProfile::class,
    BaptismProfile::class,
    BirthdayProfile::class,
    GraduationProfile::class,
    // Plantilla en blanco para cualquier evento
    CanvasProfile::class,
    // Invitaciones de temporada
    HalloweenProfile::class,
    // Tarjetas estacionales
    LoveCardProfile::class,
    AdventureBookProfile::class,
    StoryCardProfile::class,
];
