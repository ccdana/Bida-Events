<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

/** Tipos de evento que el editor necesita para crear invitaciones. */
class EventTypeSeeder extends Seeder
{
    public function run(): void
    {
        // slug => [nombre, perfil (app/EventProfiles), producto, temporada]
        foreach ([
            'xv-anos' => ['XV Años', 'xv', 'invitation', null],
            'bodas' => ['Bodas', 'boda', 'invitation', null],
            'bautizos' => ['Bautizos', 'bautizo', 'invitation', null],
            'cumpleanos' => ['Cumpleaños', 'cumple', 'invitation', null],
            'graduaciones' => ['Graduaciones', 'graduacion', 'invitation', null],
            'evento-libre' => ['Evento libre', 'lienzo', 'invitation', null],
            'halloween' => ['Halloween', 'halloween', 'invitation', 'halloween'],
            'dia-del-amor' => ['Día del Amor', 'amor', 'card', 'amor'],
            'libro-de-aventuras' => ['Libro de aventuras (Día del Amor)', 'aventura', 'card', 'amor'],
            'nuestra-historia' => ['Nuestra historia', 'historia', 'card', 'amor'],
        ] as $slug => [$name, $code, $kind, $season]) {
            EventType::updateOrCreate(['slug' => $slug], compact('name', 'code', 'kind', 'season'));
        }
    }
}
