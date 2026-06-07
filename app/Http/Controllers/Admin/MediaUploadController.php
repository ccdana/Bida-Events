<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;

class MediaUploadController extends Controller
{
    public function __construct(
        protected MediaUploadService $mediaUpload
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file'],
            'type' => ['required', 'in:image,video,audio'],
            'slug' => ['nullable', 'string', 'max:100'],
            'context' => ['nullable', 'string', 'max:50'],
        ]);

        $file = $validated['file'];
        $type = $validated['type'];
        $slug = $validated['slug'] ?? 'draft';
        $context = $validated['context'] ?? 'general';

        try {
            $this->mediaUpload->validateFile($file, $type);
            $result = $this->mediaUpload->upload($file, $type, $slug, $context);

            return response()->json([
                'success' => true,
                'url' => $result['url'],
                'public_id' => $result['public_id'],
                'provider' => $result['provider'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo subir el archivo: '.$e->getMessage(),
            ], 422);
        }
    }
}
