<?php

namespace Database\Seeders;

use App\Models\EventType;
use App\Models\Feature;
use App\Models\Guest;
use App\Models\GuestContribution;
use App\Models\Invitation;
use App\Models\InvitationData;
use App\Models\User;
use App\Services\InvitationModuleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrador Bida',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        $client = User::create([
            'name' => 'María Valenzuela',
            'email' => 'cliente@test.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        $eventType = EventType::create([
            'name' => 'XV Años',
            'slug' => 'xv-anos',
        ]);

        $features = [
            ['name' => 'Cuenta Regresiva', 'code' => 'cuenta_regresiva'],
            ['name' => 'Galería Premium', 'code' => 'galeria'],
            ['name' => 'RSVP Inteligente', 'code' => 'rsvp'],
            ['name' => 'Playlist Colaborativa', 'code' => 'playlist'],
            ['name' => 'Encuestas', 'code' => 'encuestas'],
            ['name' => 'Fotomural', 'code' => 'fotomural'],
        ];

        foreach ($features as $feature) {
            Feature::create($feature);
        }

        $invitation = Invitation::create([
            'user_id' => $client->id,
            'event_type_id' => $eventType->id,
            'slug' => 'xv-sofia',
            'template' => 'invitations.templates.xv-premium',
            'title' => 'XV Años de Sofía Valentina',
            'event_date' => now()->addMonths(3)->setTime(18, 0),
            'status' => 'active',
            'expires_at' => now()->addMonths(9),
        ]);

        foreach (XvSofiaModuleData::all() as $code => $jsonData) {
            InvitationData::create([
                'invitation_id' => $invitation->id,
                'feature_code' => $code,
                'json_data' => $jsonData,
            ]);
        }

        $guests = [
            ['name' => 'Familia Mamani', 'phone' => '70123456', 'passes_allocated' => 4],
            ['name' => 'Carlos Pereyra', 'phone' => '71234567', 'passes_allocated' => 1],
            ['name' => 'Familia Quispe', 'phone' => '72345678', 'passes_allocated' => 3],
        ];

        foreach ($guests as $guestData) {
            Guest::create([
                'invitation_id' => $invitation->id,
                ...$guestData,
                'qr_code_token' => InvitationModuleService::generateGuestToken(),
            ]);
        }

        $sampleSongs = [
            ['content_text' => 'Vivir mi vida — Marc Anthony', 'guest' => 'Familia Mamani'],
            ['content_text' => 'Bailando — Enrique Iglesias', 'guest' => 'Carlos Pereyra'],
            ['content_text' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'guest' => null],
        ];

        $guestRecords = Guest::where('invitation_id', $invitation->id)->get()->keyBy('name');

        foreach ($sampleSongs as $song) {
            GuestContribution::create([
                'invitation_id' => $invitation->id,
                'guest_id' => $song['guest'] ? $guestRecords[$song['guest']]?->id : null,
                'type' => 'song_request',
                'content_text' => $song['content_text'],
                'created_at' => now()->subMinutes(rand(10, 120)),
            ]);
        }
    }
}
