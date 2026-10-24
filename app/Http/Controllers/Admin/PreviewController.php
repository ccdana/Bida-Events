<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\InvitationModuleService;
use App\Services\InvitationPreviewSession;
use App\Support\InvitationDefaults;
use App\Support\InvitationTemplates;
use App\Support\Localization;
use App\Support\Packages;
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

    /** La vista previa desde lo que se guardó en la sesión con store() (la forma de antes; el editor ya usa render()). */
    public function frame(Request $request)
    {
        return $this->renderPayload($request, InvitationPreviewSession::get($request->query('key', 'draft')));
    }

    /**
     * La vista previa en una sola petición: el editor manda lo que se está armando y recibe la invitación
     * ya dibujada, directo en el marco. Antes eran dos (guardar en la sesión y pedir el marco), cada una con
     * su arranque del servidor, y la sesión crecía con cada cambio: la vista previa tardaba unos 2 segundos
     * en reflejar una letra.
     */
    public function render(Request $request)
    {
        $raw = json_decode((string) $request->input('payload', ''), true);

        return $this->renderPayload($request, is_array($raw) && $raw !== [] ? $this->payloadFromArray($raw) : null);
    }

    /** @param  array<string, mixed>|null  $payload */
    protected function renderPayload(Request $request, ?array $payload)
    {
        try {
            if (! $payload) {
                return response('<html><body style="font-family:sans-serif;padding:2rem;color:#888">Configura la invitación para ver la vista previa</body></html>')
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            }

            $modulos = $payload['modulos'] ?? [];
            $modulos = $this->moduleService->normalizeModules($modulos);
            $config = $modulos['config'] ?? [];
            $template = InvitationDefaults::resolveTemplate($payload['template'] ?? ($config['template'] ?? null));

            // La vista previa muestra los textos en el idioma elegido, aunque aún no se haya guardado
            $locale = Localization::resolve($payload['locale'] ?? null);
            Localization::apply($locale);

            $invitation = new Invitation([
                'locale' => $locale,
                'title' => $payload['title'] ?? 'Vista previa',
                'slug' => $payload['slug'] ?? 'preview',
                'template' => $template,
                // La vista previa respeta el paquete elegido: se ve lo que el paquete incluye
                'package' => Packages::isValid($payload['package'] ?? null) ? $payload['package'] : null,
                'event_date' => $this->parseDate($payload['event_date'] ?? null, now()->addMonths(3)),
                'status' => 'active',
                'expires_at' => $this->parseDate($payload['expires_at'] ?? null, now()->addYear(), asDate: true),
            ]);

            // El revendedor ve su vista previa con su propia marca en el pie, como la verán sus invitados
            // y con lo que su plan permite (la confirmación por WhatsApp o con pase)
            if ($request->user()?->isReseller()) {
                $invitation->reseller_id = $request->user()->id;
                $invitation->setRelation('reseller', $request->user());
            }

            $pollResults = [];
            foreach ($modulos['encuestas']['preguntas'] ?? [] as $poll) {
                $pollResults[$poll['id']] = array_fill(0, count($poll['opciones'] ?? []), 0);
            }

            $calendarUrl = $this->moduleService->googleCalendarUrl($invitation, $modulos['ubicacion'] ?? []);

            return response()
                ->view(InvitationTemplates::view($template), [
                    'templateKey' => $template,
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
        return $this->payloadFromArray($request->all());
    }

    /**
     * Lo que se está armando en el editor, con los módulos completos y normalizados.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function payloadFromArray(array $input): array
    {
        $modulos = $input['modulos'] ?? [];

        if (! is_array($modulos)) {
            $modulos = [];
        }

        $modulos = $this->moduleService->normalizeModules(
            array_replace_recursive(InvitationDefaults::emptyModules(), $modulos)
        );

        return [
            'title' => $input['title'] ?? null,
            'slug' => $input['slug'] ?? null,
            'template' => $input['template'] ?? null,
            'event_date' => $input['event_date'] ?? null,
            'expires_at' => $input['expires_at'] ?? null,
            'package' => $input['package'] ?? null,
            'locale' => $input['locale'] ?? null,
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
            'package' => $request->input('package'),
            'locale' => $request->input('locale'),
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
