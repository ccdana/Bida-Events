<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

/** Tipos de evento que el editor necesita para crear invitaciones. */
class EventTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'xv-anos' => 'XV Años',
            'bodas' => 'Bodas',
            'bautizos' => 'Bautizos',
            'cumpleanos' => 'Cumpleaños',
        ] as $slug => $name) {
            EventType::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }
}
