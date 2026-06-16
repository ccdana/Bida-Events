<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Support\InvitationDefaults;
use App\Services\InvitationModuleService;
use App\Services\InvitationPreviewSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

class PreviewController extends Controller
{
    public function __construct(
        protected InvitationModuleService $moduleService
    ) {}

    public function store(Request $request)
    {
        try {
            $key = $request->input('preview_key', 'draft');
            $payload = $request->isJson()
                ? $this->extractJsonPayload($request)
                : $this->extractPayload($request);

            InvitationPreviewSession::store($key, $payload);
            session()->save();

            return response()->json([
                'success' => true,
                'revision' => microtime(true),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar la vista previa.',
            ], 422);
        }
    }

    public function frame(Request $request)
    {
        try {
            $key = $request->query('key', 'draft');
            $payload = InvitationPreviewSession::get($key);

            if (! $payload) {
                return response('<html><body style="font-family:sans-serif;padding:2rem;color:#888">Configura la invitación para ver la vista previa</body></html>')
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            }

            $modulos = $payload['modulos'] ?? [];
            $modulos = $this->moduleService->normalizeModules($modulos);
            $config = $modulos['config'] ?? [];
            $template = $payload['template'] ?? ($config['template'] ?? 'invitations.templates.xv-premium');

            $invitation = new Invitation([
                'title' => $payload['title'] ?? 'Vista previa',
                'slug' => $payload['slug'] ?? 'preview',
                'template' => $template,
                'event_date' => $this->parseDate($payload['event_date'] ?? null, now()->addMonths(3)),
                'status' => 'active',
                'expires_at' => $this->parseDate($payload['expires_at'] ?? null, now()->addYear(), asDate: true),
            ]);

            $pollResults = [];
            foreach ($modulos['encuestas']['preguntas'] ?? [] as $poll) {
                $pollResults[$poll['id']] = array_fill(0, count($poll['opciones'] ?? []), 0);
            }

            $calendarUrl = $this->moduleService->googleCalendarUrl($invitation, $modulos['ubicacion'] ?? []);

            return response()
                ->view($template, [
                    'invitation' => $invitation,
                    'modulos' => $modulos,
                    'guest' => null,
                    'pollResults' => $pollResults,
                    'calendarUrl' => $calendarUrl,
                    'playlistSongs' => [],
                    'fotomuralPhotos' => [],
                    'isPreview' => true,
                ])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');
        } catch (Throwable $exception) {
            report($exception);

            return response(
                '<html><body style="font-family:sans-serif;padding:2rem;color:#b91c1c">Error al renderizar la vista previa. Revisa los datos del banner principal y las fechas del evento.</body></html>',
                500
            );
        }
    }

    protected function extractJsonPayload(Request $request): array
    {
        $modulos = $request->input('modulos', []);

        if (! is_array($modulos)) {
            $modulos = [];
        }

        $modulos = $this->moduleService->normalizeModules(
            array_replace_recursive(InvitationDefaults::emptyModules(), $modulos)
        );

        return [
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'template' => $request->input('template'),
            'event_date' => $request->input('event_date'),
            'expires_at' => $request->input('expires_at'),
            'modulos' => $modulos,
        ];
    }

    protected function extractPayload(Request $request): array
    {
        $moduleCodes = InvitationDefaults::moduleCodes();
        $modulos = [];

        foreach ($moduleCodes as $code) {
            $raw = $request->input("modulos.{$code}", '{}');
            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

            if (is_string($raw) && json_last_error() !== JSON_ERROR_NONE) {
                $decoded = [];
            }

            $modulos[$code] = is_array($decoded) ? $decoded : [];
        }

        $modulos = $this->moduleService->normalizeModules($modulos);

        return [
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'template' => $request->input('template'),
            'event_date' => $request->input('event_date'),
            'expires_at' => $request->input('expires_at'),
            'modulos' => $modulos,
        ];
    }

    protected function parseDate(?string $value, Carbon $fallback, bool $asDate = false): Carbon
    {
        if (blank($value)) {
            return $fallback;
        }

        try {
            $parsed = Carbon::parse($value);

            return $asDate ? $parsed->startOfDay() : $parsed;
        } catch (Throwable) {
            return $fallback;
        }
    }
}
