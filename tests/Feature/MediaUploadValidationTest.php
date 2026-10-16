<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MediaUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Qué archivos acepta el editor al subir una canción, un video o una foto. El detector de tipos del servidor
 * confunde con frecuencia los .m4a de iPhone y WhatsApp (los ve como video/mp4) y los .mp3 sin etiquetas (los ve como
 * application/octet-stream): rechazar eso impedía subir canciones que sí sirven.
 */
class MediaUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    private function accepts(string $type, UploadedFile $file): bool
    {
        try {
            app(MediaUploadService::class)->validateFile($file, $type);

            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    /** Un archivo de verdad en disco: el detector de tipos mira su contenido, no su nombre (los falsos de Laravel se guían por el nombre). */
    private function real(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'qa');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    /** Un .m4a real empieza con la caja «ftyp» de MP4: el detector lo reconoce como video/mp4. */
    private function m4a(string $name = 'nota-de-voz.m4a'): UploadedFile
    {
        return $this->real($name, "\x00\x00\x00\x20ftypM4A \x00\x00\x00\x00M4A mp42isom".str_repeat("\x00", 2048));
    }

    public function test_audio_formats_people_actually_have_are_accepted(): void
    {
        // Un mp3 sin etiquetas que el detector no reconoce
        $this->assertTrue($this->accepts('audio', $this->real('cancion.mp3', str_repeat("\x00", 4096))));
        // Notas de voz de iPhone y WhatsApp (m4a que el detector ve como video/mp4)
        $this->assertTrue($this->accepts('audio', $this->m4a()));
        // WAV real
        $wav = 'RIFF'.pack('V', 36).'WAVEfmt '.pack('VvvVVvv', 16, 1, 1, 44100, 88200, 2, 16).'data'.pack('V', 0);
        $this->assertTrue($this->accepts('audio', $this->real('tono.wav', $wav)));
        // Mayúsculas en la extensión
        $this->assertTrue($this->accepts('audio', $this->real('CANCION.MP3', str_repeat("\x00", 1024))));
    }

    public function test_audio_that_is_not_audio_is_rejected(): void
    {
        $this->assertFalse($this->accepts('audio', $this->real('truco.mp3', '<html><script>alert(1)</script></html>')));
        $this->assertFalse($this->accepts('audio', $this->real('programa.exe', str_repeat("\x00", 1024))));
        $this->assertFalse($this->accepts('audio', $this->real('foto.jpg', str_repeat("\x00", 1024))));
    }

    public function test_audio_over_twenty_megabytes_says_so(): void
    {
        $big = UploadedFile::fake()->create('larga.mp3', 21 * 1024);

        try {
            app(MediaUploadService::class)->validateFile($big, 'audio');
            $this->fail('Una canción de 21 MB no debería aceptarse');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('20 MB', $e->errors()['file'][0]);
        }
    }

    public function test_video_formats_are_accepted_and_others_are_not(): void
    {
        $mp4 = $this->real('clip.mp4', "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom".str_repeat("\x00", 2048));

        $this->assertTrue($this->accepts('video', $mp4));
        $this->assertTrue($this->accepts('video', $this->real('iphone.mov', "\x00\x00\x00\x14ftypqt  \x00\x00\x00\x00qt  ".str_repeat("\x00", 2048))));
        $this->assertFalse($this->accepts('video', $this->real('clip.php', 'x')));
        $this->assertFalse($this->accepts('video', UploadedFile::fake()->create('enorme.mp4', 101 * 1024)));
    }

    public function test_photos_keep_their_rules_with_clear_messages(): void
    {
        $this->assertTrue($this->accepts('image', UploadedFile::fake()->image('foto.jpg', 800, 600)));
        $this->assertFalse($this->accepts('image', $this->real('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')));

        try {
            app(MediaUploadService::class)->validateFile($this->real('logo.svg', '<svg/>'), 'image');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('JPG, PNG', implode(' ', $e->errors()['file']));
        }
    }

    public function test_the_upload_endpoint_refuses_a_bad_file_before_sending_it_anywhere(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->postJson(route('admin.media.upload'), ['file' => $this->real('truco.mp3', '<html></html>'), 'type' => 'audio'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file')
            ->assertJsonPath('errors.file.0', fn (string $message) => str_contains($message, 'MP3'));
    }
}
