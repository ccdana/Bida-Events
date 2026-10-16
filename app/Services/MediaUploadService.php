<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaUploadService
{
    // Transformaciones según contexto de imagen
    private const IMAGE_TRANSFORMS = [
        'bienvenida' => ['width' => 800, 'height' => 800, 'crop' => 'fill', 'quality' => 'auto'],
        // Misma proporción que el espacio de la invitación (16:10, App\Support\ImageFrames): el recorte del editor no se vuelve a cortar
        'ubicacion' => ['width' => 1600, 'height' => 1000, 'crop' => 'fill', 'quality' => 'auto'],
        'galeria' => ['width' => 600, 'height' => 600, 'crop' => 'fill', 'quality' => 'auto'],
        'fotomural' => ['width' => 500, 'height' => 500, 'crop' => 'fill', 'quality' => 'auto'],
        'post_evento' => ['width' => 600, 'height' => 600, 'crop' => 'fill', 'quality' => 'auto'],
        'destacados' => ['width' => 300, 'height' => 300, 'crop' => 'thumb', 'gravity' => 'face', 'quality' => 'auto'],
        'regalos_qr' => ['width' => 300, 'height' => 300, 'crop' => 'fill', 'quality' => 'auto'],
        // Tarjeta «Libro de aventuras»
        'historia' => ['width' => 900, 'height' => 900, 'crop' => 'limit', 'quality' => 'auto'],
        'recuerdos' => ['width' => 800, 'height' => 1000, 'crop' => 'fill', 'quality' => 'auto'],
        'collage' => ['width' => 800, 'height' => 800, 'crop' => 'limit', 'quality' => 'auto'],
        'marcos' => ['width' => 800, 'height' => 1000, 'crop' => 'fill', 'quality' => 'auto'],
        'memoria' => ['width' => 400, 'height' => 400, 'crop' => 'fill', 'quality' => 'auto'],
    ];

    private const VIDEO_DELIVERY = ['crop' => 'limit', 'width' => 1280, 'height' => 1280, 'quality' => 'auto', 'format' => 'mp4'];

    public function isCloudinaryConfigured(): bool
    {
        return ! empty(config('cloudinary.cloud_url'));
    }

    /**
     * @return array{url: string, public_id: string|null, provider: string}
     */
    public function upload(UploadedFile $file, string $type, string $slug, string $context = 'general'): array
    {
        // Carpeta segura: solo letras, números y guiones (el nombre y el contexto vienen del navegador)
        $slug = preg_replace('/[^a-z0-9-]/', '', strtolower($slug)) ?: 'draft';
        $context = preg_replace('/[^a-z0-9_-]/', '', strtolower($context)) ?: 'general';
        $folder = $this->buildFolder($slug, $context);

        if ($this->isCloudinaryConfigured()) {
            return $this->uploadToCloudinary($file, $type, $folder, $context);
        }

        Log::warning('Cloudinary no configurado — usando almacenamiento local.', ['folder' => $folder]);

        return $this->uploadToLocal($file, $type, $slug, $context);
    }

    protected function buildFolder(string $slug, string $context): string
    {
        $base = trim(config('cloudinary.folder', 'bida-events'), '/');
        $safeSlug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug)) ?: 'draft';

        return "{$base}/{$safeSlug}/{$context}";
    }

    protected function uploadToCloudinary(UploadedFile $file, string $type, string $folder, string $context): array
    {
        Configuration::instance(config('cloudinary.cloud_url'));

        $resourceType = match ($type) {
            'video' => 'video',
            'audio' => 'video', // Cloudinary trata audio como video resource
            default => 'image',
        };

        $options = [
            'folder' => $folder,
            'resource_type' => $resourceType,
            'use_filename' => true,
            'unique_filename' => true,
        ];

        if ($type === 'audio') {
            $options['resource_type'] = 'auto';
        }

        // La versión MP4 liviana se genera al subir: Cloudinary no transforma al vuelo videos pesados
        if ($type === 'video') {
            $options['eager'] = [self::VIDEO_DELIVERY];
            $options['eager_async'] = true;
        }

        // Agregar transformaciones para imágenes según contexto
        if ($type === 'image' && isset(self::IMAGE_TRANSFORMS[$context])) {
            $transform = self::IMAGE_TRANSFORMS[$context];
            $options['transformation'] = [
                [
                    'width' => $transform['width'],
                    'height' => $transform['height'],
                    'crop' => $transform['crop'],
                    'quality' => $transform['quality'],
                    'fetch_format' => 'auto',
                ],
            ];
            if (! empty($transform['gravity'])) {
                $options['transformation'][0]['gravity'] = $transform['gravity'];
            }
        }

        $result = (new UploadApi)->upload($file->getRealPath(), $options);

        // Para Cloudinary, aplicamos transformación en la URL
        $url = $result['secure_url'];
        if ($type === 'image' && isset(self::IMAGE_TRANSFORMS[$context])) {
            $url = $this->addCloudinaryTransform($url, $context);
        }

        if ($type === 'video') {
            $url = $this->videoDeliveryUrl($url);
        }

        return [
            'url' => $url,
            'public_id' => $result['public_id'] ?? null,
            'provider' => 'cloudinary',
        ];
    }

    /**
     * Los videos se entregan como MP4 (H.264) de hasta 1280px: los .mov de iPhone no se reproducen en Chrome
     * y un 4K pesa demasiado para verse desde el celular. Usa la misma transformación que se genera al subir.
     */
    protected function videoDeliveryUrl(string $url): string
    {
        $parts = explode('/video/upload/', $url, 2);
        if (count($parts) !== 2) {
            return $url;
        }

        $path = preg_replace('/\.[a-z0-9]+$/i', '', $parts[1]).'.mp4';

        return $parts[0].'/video/upload/c_limit,h_1280,q_auto,w_1280/'.$path;
    }

    protected function addCloudinaryTransform(string $url, string $context): string
    {
        if (! isset(self::IMAGE_TRANSFORMS[$context])) {
            return $url;
        }

        $transform = self::IMAGE_TRANSFORMS[$context];
        $parts = explode('/upload/', $url);
        if (count($parts) !== 2) {
            return $url;
        }

        $transformStr = sprintf(
            'w_%d,h_%d,c_%s,q_%s,f_auto',
            $transform['width'],
            $transform['height'],
            $transform['crop'],
            $transform['quality']
        );

        if (! empty($transform['gravity'])) {
            $transformStr .= ',g_'.$transform['gravity'];
        }

        return $parts[0].'/upload/'.$transformStr.'/'.$parts[1];
    }

    protected function uploadToLocal(UploadedFile $file, string $type, string $slug, string $context): array
    {
        $path = $file->store("uploads/{$slug}/{$context}", 'public');

        $url = Storage::disk('public')->url($path);

        return [
            'url' => $url,
            'public_id' => null,
            'provider' => 'local',
        ];
    }

    /**
     * Borra en Cloudinary el archivo de una URL nuestra. Devuelve false (sin romper nada) si la URL
     * no es de Cloudinary, si no hay credenciales o si el borrado falla: la fila local ya se fue y
     * no queremos que un error remoto detenga la operación.
     */
    public function delete(?string $url): bool
    {
        $publicId = $this->publicIdFromUrl($url);

        if ($publicId === null || ! $this->isCloudinaryConfigured()) {
            return false;
        }

        try {
            Configuration::instance(config('cloudinary.cloud_url'));
            (new UploadApi)->destroy($publicId, ['resource_type' => str_contains((string) $url, '/video/') ? 'video' : 'image']);

            return true;
        } catch (\Throwable $exception) {
            Log::warning('No se pudo borrar el archivo en Cloudinary.', [
                'public_id' => $publicId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * De "https://res.cloudinary.com/cuenta/image/upload/v123/carpeta/archivo.jpg" saca
     * "carpeta/archivo" (sin transformaciones, versión ni extensión).
     */
    public function publicIdFromUrl(?string $url): ?string
    {
        if (! is_string($url) || ! str_contains($url, 'res.cloudinary.com')) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! preg_match('#/upload/(.+)$#', $path, $match)) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $match[1])));

        // Se descartan las transformaciones (llevan coma o son el prefijo de versión "v123")
        while ($segments !== [] && (str_contains($segments[0], ',') || preg_match('/^v\d+$/', $segments[0]))) {
            array_shift($segments);
        }

        if ($segments === []) {
            return null;
        }

        $publicId = implode('/', $segments);

        return preg_replace('/\.[a-z0-9]+$/i', '', $publicId) ?: null;
    }

    /**
     * Qué se acepta de cada tipo (el editor revisa lo mismo al elegir el archivo: MEDIA_RULES en editor.js).
     * Audio y video se reconocen por su extensión y por un tipo «razonable»: el detector de tipos del servidor
     * confunde los .m4a de iPhone y WhatsApp (video/mp4) y algunos .mp3 sin etiquetas (application/octet-stream), y
     * rechazar eso era justo lo que impedía subir una canción.
     */
    private const AUDIO_EXTENSIONS = ['mp3', 'm4a', 'wav', 'ogg', 'aac', 'opus'];

    private const VIDEO_EXTENSIONS = ['mp4', 'mov', 'webm', 'avi'];

    public function validateFile(UploadedFile $file, string $type): void
    {
        if ($type === 'audio') {
            $this->validateAudioVideo($file, ['una canción', 'La canción'], self::AUDIO_EXTENSIONS, 'audio', 20, 'MP3, M4A, WAV, OGG, AAC u OPUS', ['video/mp4', 'video/mpeg', 'video/ogg', 'application/ogg', 'application/mp4']);

            return;
        }

        if ($type === 'video') {
            $this->validateAudioVideo($file, ['un video', 'El video'], self::VIDEO_EXTENSIONS, 'video', 100, 'MP4, MOV, WEBM o AVI', ['application/mp4', 'application/x-troff-msvideo']);

            return;
        }

        // Sin SVG: puede traer scripts dentro. Las dimensiones evitan imágenes que revientan la memoria al procesarlas.
        validator(['file' => $file], ['file' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'dimensions:max_width=8000,max_height=8000', 'max:10240']], [
            'file.image' => 'El archivo no es una foto que se pueda usar. Elige un JPG, PNG, WebP o GIF.',
            'file.mimes' => 'La foto tiene que ser JPG, PNG, WebP o GIF.',
            'file.max' => 'La foto pesa más de 10 MB. Elige una más liviana.',
            'file.dimensions' => 'La foto es demasiado grande (más de 8000 píxeles de lado). Elige una más chica.',
        ])->validate();
    }

    /**
     * @param  array{0: string, 1: string}  $name  cómo se dice («una canción», «La canción»)
     * @param  list<string>  $extensions
     * @param  list<string>  $otherTypes  tipos que no empiezan con «audio/» o «video/» pero son válidos
     */
    private function validateAudioVideo(UploadedFile $file, array $name, array $extensions, string $family, int $maxMb, string $formats, array $otherTypes): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mime = strtolower((string) $file->getMimeType());

        $typeOk = str_starts_with($mime, $family.'/') || $mime === 'application/octet-stream' || in_array($mime, $otherTypes, true);

        if (! in_array($extension, $extensions, true) || ! $typeOk) {
            throw ValidationException::withMessages(['file' => "El archivo no es {$name[0]} que se pueda usar. Elige uno en formato {$formats}."]);
        }

        if ($file->getSize() > $maxMb * 1048576) {
            throw ValidationException::withMessages(['file' => "{$name[1]} pesa más de {$maxMb} MB. Elige otro más liviano."]);
        }
    }
}
