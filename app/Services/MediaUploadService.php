<?php

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaUploadService
{
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
            return $this->uploadToCloudinary($file, $type, $folder);
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

    protected function uploadToCloudinary(UploadedFile $file, string $type, string $folder): array
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

        $result = (new UploadApi)->upload($file->getRealPath(), $options);

        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'] ?? null,
            'provider' => 'cloudinary',
        ];
    }

    protected function uploadToLocal(UploadedFile $file, string $type, string $slug, string $context): array
    {
        $path = $file->store("uploads/{$slug}/{$context}", 'public');

        return [
            'url' => Storage::disk('public')->url($path),
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
