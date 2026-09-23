<?php

namespace Tests\Feature;

use App\Exports\GuestReportExport;
use App\Models\Invitation;
use App\Models\User;
use App\Support\InvitationTemplates;
use App\Support\Pdf\PdfAssets;
use App\Support\Pdf\PdfTemplateStyle;
use App\ViewModels\Client\GuestReportData;
use App\ViewModels\Client\InvitationPrintData;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

    public function test_owner_requests_the_reports_and_downloads_them_when_they_are_ready(): void
    {
        Excel::fake();
        Storage::fake('local');
        $owner = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $owner->id]);
        $this->createGuests($invitation);

        $this->actingAs($owner);

        // El Excel se arma en segundo plano (en pruebas la cola corre al instante)
        $excel = $this->postJson(route('client.export.store', [$invitation, 'guests-excel']))
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->json();

        Excel::assertStored("exports/{$excel['id']}/invitados-{$invitation->slug}.xlsx", 'local', function (GuestReportExport $export) {
            $titles = array_map(fn ($sheet) => $sheet->title(), $export->sheets());

            return $titles === ['Resumen', 'Invitados', 'Por contactar', 'Alimentación'];
        });

        // Los PDF quedan guardados y se descargan desde su enlace
        foreach (['guests-pdf' => 'reporte-invitados', 'invitation-pdf' => 'invitacion'] as $type => $prefix) {
            $pdf = $this->postJson(route('client.export.store', [$invitation, $type]))
                ->assertOk()
                ->assertJsonPath('status', 'ready')
                ->json();

            Storage::disk('local')->assertExists("exports/{$pdf['id']}/{$prefix}-{$invitation->slug}.pdf");

            $this->get($pdf['download_url'])
                ->assertOk()
                ->assertDownload("{$prefix}-{$invitation->slug}.pdf");
        }

        // El estado se puede consultar y otro cliente no puede bajar el archivo
        $this->getJson(route('client.export.status', $excel['id']))->assertOk()->assertJsonPath('status', 'ready');

        $this->actingAs(User::factory()->create());
        $this->get(route('client.export.download', $excel['id']))->assertForbidden();
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
        // Las fotos no van al papel: el PDF se imprime y la galería vive en la invitación digital
        $this->assertArrayNotHasKey('photo', $print['hero']);
    }

    /**
     * Lo que el cliente imprime tiene que verse como su invitación, no como un documento
     * cualquiera: cada plantilla trae su portada y su adorno.
     */
    public function test_each_template_prints_its_own_cover(): void
    {
        $covers = [];

        foreach (array_keys(InvitationTemplates::all()) as $template) {
            $invitation = $this->createInvitation(['template' => $template]);
            $print = app(InvitationPrintData::class)->make($invitation, XvSofiaModuleData::all());

            $this->assertTrue(view()->exists($print['style']['view']), "Falta la portada de «{$template}»");
            $this->assertSame(InvitationTemplates::get($template)['label'], $print['style']['label']);
            $this->assertStringStartsWith('data:image/svg+xml;base64,', $print['motifs']['main']);
            $this->assertIsInt($print['cover']['fill']);

            $covers[$template] = $print['style']['cover'];
        }

        // Dos plantillas no pueden salir impresas igual: es lo que hace reconocible a cada una
        $this->assertSame(count($covers), count(array_unique($covers)), 'Hay plantillas que comparten portada: '.json_encode($covers));

        // Una plantilla nueva sin estilo propio hereda la portada de su tipo de evento
        $this->assertSame('gala', PdfTemplateStyle::for('invitations.templates.xv-lo-que-venga')['cover']);
    }

    /** La invitación impresa siempre sale en dos hojas: la invitación y sus detalles. */
    public function test_the_printed_invitation_always_fits_in_two_pages(): void
    {
        foreach ([XvSofiaModuleData::all(), $this->crowdedModules()] as $modules) {
            foreach (array_keys(InvitationTemplates::all()) as $template) {
                $invitation = $this->createInvitation(['template' => $template]);
                $pdf = Pdf::loadView(
                    'client.exports.invitation-pdf',
                    app(InvitationPrintData::class)->make($invitation, $modules)
                )->setPaper('a4')->output();

                $this->assertLessThanOrEqual(
                    2,
                    preg_match_all('#/Type\s*/Page[^s]#', $pdf),
                    "«{$template}» se pasó de dos hojas"
                );
            }
        }
    }

    /** Una invitación con todo lleno y textos largos: el caso que antes se iba a una tercera hoja. */
    private function crowdedModules(): array
    {
        $long = 'Un texto largo de los que se escriben cuando se quiere contar todo en la invitación, '
            .'con detalles, agradecimientos y la recomendación de llegar temprano.';

        $modules = XvSofiaModuleData::all();
        $modules['config']['modulos'] = array_fill_keys(array_keys($modules['config']['modulos'] ?? []), true);
        $modules['bienvenida']['mensaje'] = $long.' '.$long;
        $modules['ubicacion']['nota'] = $long;
        $modules['itinerario']['eventos'] = array_map(
            fn (int $i) => ['hora' => sprintf('%02d:00', 8 + $i), 'titulo' => "Momento número {$i}", 'descripcion' => $long],
            range(1, 12)
        );
        $modules['destacados']['padrinos'] = array_map(
            fn (int $i) => ['rol' => "Padrinos {$i}", 'nombres' => "Sr. Nombre Apellido y Sra. Nombre Apellido {$i}"],
            range(1, 8)
        );
        $modules['destacados']['chambelanes'] = array_map(fn (int $i) => ['nombre' => "Chambelán Nombre Apellido {$i}"], range(1, 20));
        $modules['destacados']['damitas'] = array_map(fn (int $i) => ['nombre' => "Damita Nombre Apellido {$i}"], range(1, 20));
        $modules['dress_code']['descripcion'] = $long;
        $modules['dress_code']['sugerencias'] = array_map(
            fn (int $i) => ['para' => "Para {$i}", 'titulo' => "Sugerencia {$i}", 'descripcion' => $long],
            range(1, 6)
        );
        $modules['regalos']['opciones'] = array_map(fn (int $i) => ['titulo' => "Regalo {$i}", 'descripcion' => $long], range(1, 6));
        $modules['regalos']['sobres'] = ['titulo' => 'Lluvia de sobres', 'direccion' => $long];

        return $modules;
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
