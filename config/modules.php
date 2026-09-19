<?php

use App\Modules\Card\AdventuresModule;
use App\Modules\Card\CollageModule;
use App\Modules\Card\DedicationModule;
use App\Modules\Card\FramesModule;
use App\Modules\Card\MemoriesModule;
use App\Modules\Card\MemoryGameModule;
use App\Modules\Card\MilestoneModule;
use App\Modules\Card\ReplyModule;
use App\Modules\Card\StoryActsModule;
use App\Modules\Card\StoryModule;
use App\Modules\Invitation\AudioModule;
use App\Modules\Invitation\ConfigModule;
use App\Modules\Invitation\DressCodeModule;
use App\Modules\Invitation\FeaturedPeopleModule;
use App\Modules\Invitation\GalleryModule;
use App\Modules\Invitation\GiftsModule;
use App\Modules\Invitation\HashtagModule;
use App\Modules\Invitation\HeroModule;
use App\Modules\Invitation\ItineraryModule;
use App\Modules\Invitation\LocationModule;
use App\Modules\Invitation\PlaylistModule;
use App\Modules\Invitation\PollsModule;
use App\Modules\Invitation\PostEventModule;
use App\Modules\Invitation\RsvpModule;
use App\Modules\Invitation\ToggleModule;
use App\Modules\Invitation\VideoModule;

/*
|--------------------------------------------------------------------------
| Módulos registrados
|--------------------------------------------------------------------------
|
| El orden de esta lista es el orden en que se guardan y en que el editor
| los envía. Los que solo se encienden o apagan usan ToggleModule con su
| código y nombre. Para sumar un módulo nuevo: su clase en app/Modules, su
| migración y su línea aquí (docs/temporadas.md).
|
*/

return [

    'modules' => [
        ConfigModule::class,
        HeroModule::class,
        LocationModule::class,
        ItineraryModule::class,
        DressCodeModule::class,
        FeaturedPeopleModule::class,
        GalleryModule::class,
        AudioModule::class,
        VideoModule::class,
        PlaylistModule::class,
        HashtagModule::class,
        PollsModule::class,
        GiftsModule::class,
        PostEventModule::class,
        RsvpModule::class,
        // Solo se encienden o apagan: su contenido sale de la fecha o de los invitados
        [ToggleModule::class, 'cuenta_regresiva', 'Cuenta regresiva'],
        [ToggleModule::class, 'agendar', 'Agendar'],
        [ToggleModule::class, 'fotomural', 'Fotomural'],
        // Tarjetas estacionales
        DedicationModule::class,
        MilestoneModule::class,
        ReplyModule::class,
        // Tarjeta tipo cuaderno («Libro de aventuras»)
        StoryModule::class,
        MemoriesModule::class,
        CollageModule::class,
        FramesModule::class,
        MemoryGameModule::class,
        AdventuresModule::class,
        // Tarjeta en cuatro actos («Bajo la misma luna»)
        StoryActsModule::class,
    ],

];
