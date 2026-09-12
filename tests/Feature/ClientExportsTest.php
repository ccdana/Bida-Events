<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

class ClientExportsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_owner_downloads_guest_and_invitation_reports(): void
    {
        Excel::fake();
        $owner = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $owner->id]);
        $invitation->guests()->create(['name' => 'Familia Rojas', 'passes_allocated' => 2]);

        $this->actingAs($owner);

        $this->get(route('client.export.excel', $invitation))->assertOk();
        Excel::assertDownloaded("confirmados-{$invitation->slug}.xlsx");

        $this->get(route('client.export.pdf', $invitation))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get(route('client.export.invitation-pdf', $invitation))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
