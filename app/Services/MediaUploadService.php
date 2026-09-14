<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Transformation\Transformation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaUploadService
{
    // Transformaciones según contexto de imagen
    private const IMAGE_TRANSFORMS = [
        'bienvenida' => ['width' => 800, 'height' => 800, 'crop' => 'fill', 'quality' => 'auto'],
        'ubicacion' => ['width' => 600, 'height' => 400, 'crop' => 'fill', 'quality' => 'auto'],
        'galeria' => ['width' => 600, 'height' => 600, 'crop' => 'fill', 'quality' => 'auto'],
        'fotomural' => ['width' => 500, 'height' => 500, 'crop' => 'fill', 'quality' => 'auto'],
        'post_evento' => ['width' => 600, 'height' => 600, 'crop' => 'fill', 'quality' => 'auto'],
        'destacados' => ['width' => 300, 'height' => 300, 'crop' => 'thumb', 'gravity' => 'face', 'quality' => 'auto'],
        'regalos_qr' => ['width' => 300, 'height' => 300, 'crop' => 'fill', 'quality' => 'auto'],
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
                ]
            ];
            if (!empty($transform['gravity'])) {
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
        if (!isset(self::IMAGE_TRANSFORMS[$context])) {
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

        if (!empty($transform['gravity'])) {
            $transformStr .= ',g_' . $transform['gravity'];
        }

        return $parts[0] . '/upload/' . $transformStr . '/' . $parts[1];
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

    public function validateFile(UploadedFile $file, string $type): void
    {
        $rules = match ($type) {
            'video' => ['mimes:mp4,mov,webm,avi', 'max:102400'], // 100MB
            'audio' => ['mimes:mp3,m4a,wav,ogg,aac', 'max:20480'], // 20MB
            default => ['mimes:jpg,jpeg,png,gif,webp,svg', 'max:10240'], // 10MB
        };

        validator(['file' => $file], ['file' => $rules])->validate();
    }
}
