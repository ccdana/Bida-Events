<?php

namespace Tests\Feature;

use App\Exports\GuestReportExport;
use App\Models\Invitation;
use App\Models\User;
use App\Support\Pdf\PdfAssets;
use App\ViewModels\Client\GuestReportData;
use App\ViewModels\Client\InvitationPrintData;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

class ClientExportsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private string $cacheRoot;

    protected function setUp(): void
    {
        parent::setUp();

        // Las fotos y fuentes remotas no se descargan, y la caché de las pruebas no toca la real
        Http::fake(['*' => Http::response('', 404)]);
        $this->cacheRoot = sys_get_temp_dir().'/bida-pdf-test-'.uniqid();
        $this->app->instance(PdfAssets::class, new PdfAssets($this->cacheRoot));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->cacheRoot);

        parent::tearDown();
    }

    public function test_owner_downloads_guest_and_invitation_reports(): void
    {
        Excel::fake();
        $owner = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $owner->id]);
        $this->createGuests($invitation);

        $this->actingAs($owner);

        $this->get(route('client.export.excel', $invitation))->assertOk();
        Excel::assertDownloaded("invitados-{$invitation->slug}.xlsx", function (GuestReportExport $export) {
            $titles = array_map(fn ($sheet) => $sheet->title(), $export->sheets());

            return $titles === ['Resumen', 'Invitados', 'Por contactar', 'Alimentación'];
        });

        $this->get(route('client.export.pdf', $invitation))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get(route('client.export.invitation-pdf', $invitation))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_guest_report_gives_the_numbers_to_plan_the_event(): void
    {
        $invitation = $this->createInvitation(['event_date' => now()->addDays(10)->setTime(18, 0)]);
        $this->createGuests($invitation);

        $report = app(GuestReportData::class)->make($invitation, $invitation->guests()->get());

        $this->assertSame(3, $report['stats']['confirmedPeople']);
        $this->assertSame(2, $report['stats']['pendingPeople']);
        $this->assertSame(5, $report['stats']['maxPeople']);
        $this->assertSame(6, $report['stats']['releasedPasses']);
        $this->assertSame(67, $report['stats']['responseRate']);
        $this->assertSame(10, $report['daysLeft']);
        $this->assertSame('https://wa.me/59171234567', $report['groups']['pending']->first()['whatsapp']);
        $this->assertSame([['table' => '4', 'guests' => 1, 'people' => 3]], $report['tables']->all());
        $this->assertStringContainsString('planifica entre 3 y 5 personas', implode(' ', $report['actions']));
    }

    public function test_invitation_print_uses_the_template_modules(): void
    {
        $invitation = $this->createInvitation();

        $print = app(InvitationPrintData::class)->make($invitation, XvSofiaModuleData::all());

        $this->assertSame('Sofía Valentina', $print['hero']['name']);
        $this->assertSame('Salón Imperial La Paz', $print['location']['name']);
        $this->assertCount(6, $print['itinerary']);
        $this->assertCount(3, $print['honor']['godparents']);
        $this->assertSame('#2c1810', $print['colors']['ink']);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $print['rsvp']['qr']);
        $this->assertNull($print['hero']['photo']);
    }

    private function createGuests(Invitation $invitation): void
    {
        $invitation->guests()->createMany([
            ['name' => 'Familia Rojas', 'phone' => '71234567', 'passes_allocated' => 2, 'passes_confirmed' => 0, 'status' => 'pending'],
            ['name' => 'Ana Quispe', 'passes_allocated' => 4, 'passes_confirmed' => 3, 'status' => 'confirmed', 'table_number' => '4', 'dietary_restrictions' => 'Vegetariana', 'confirmed_at' => now()],
            ['name' => 'Luis Mamani', 'passes_allocated' => 5, 'passes_confirmed' => 0, 'status' => 'declined'],
        ]);
    }
}
