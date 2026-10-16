<?php

namespace App\Http\Requests\Admin\Invitation\Concerns;

use App\Models\EventType;
use App\Models\Invitation;
use App\Support\InvitationModuleRules;
use App\Support\InvitationTemplates;
use App\Support\TemplateAvailability;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

/**
 * Decodifica y valida los módulos JSON del editor antes de guardar nada.
 * Las clases que lo usan deben añadir 'modulos_data' a $dontFlash.
 */
trait ValidatesInvitationModules
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'modulos_data' => InvitationModuleRules::decode($this->input('modulos', [])),
        ]);

        $slug = (string) $this->input('slug', '');

        if ($this->boolean('slug_auto') && $this->isMethod('post')) {
            // El asistente de creación arma la dirección con el nombre: si ya existe, le suma un número
            // en lugar de devolver un error que quien crea la invitación no sabría resolver
            $slug = $this->uniqueSlug($slug);
        } elseif ($slug !== '') {
            // «Mi Boda» se guarda como «mi-boda»: la unicidad se revisa sobre lo que de verdad se guarda,
            // no sobre lo escrito (antes «Mi Boda» pasaba la revisión y chocaba con «mi-boda» al guardar: error 500)
            $slug = Str::slug($slug);
        }

        $this->merge(['slug' => $slug]);
    }

    private function uniqueSlug(string $slug): string
    {
        $base = Str::slug($slug) ?: 'invitacion';
        $candidate = $base;

        for ($n = 2; Invitation::where('slug', $candidate)->exists(); $n++) {
            $candidate = $base.'-'.$n;
        }

        return $candidate;
    }

    protected function moduleRules(): array
    {
        return InvitationModuleRules::rules('modulos_data');
    }

    /**
     * La plantilla tiene que ser del tipo de evento elegido (una boda no se arma con la plantilla de
     * XV) y tiene que ofrecerse hoy: no una apagada en Ajustes ni de una temporada cerrada. La que la
     * invitación ya tiene se conserva aunque se haya apagado.
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['template', 'event_type_id'])) {
                return;
            }

            $template = (string) $this->input('template');
            $current = $this->route('invitation') instanceof Invitation ? $this->route('invitation')->template : null;

            // Una plantilla que no existe dejaba una invitación que no se puede ver (la que ya tiene se conserva)
            if (! isset(InvitationTemplates::all()[$template]) && $template !== $current) {
                $validator->errors()->add('template', 'La plantilla elegida no existe.');

                return;
            }

            // El enlace tiene que seguir activo hasta el día del evento: si no, los invitados ven «vencida» su invitación
            $this->checkLinkOutlivesEvent($validator);

            $event = InvitationTemplates::all()[$template]['event'] ?? null;
            $code = EventType::whereKey($this->input('event_type_id'))->value('code');

            if ($event && $code && $event !== $code) {
                $validator->errors()->add('template', 'La plantilla no corresponde al tipo de evento elegido.');

                return;
            }

            // La confirmación es una sola: por WhatsApp o con pase QR
            $flags = (array) data_get($this->input('modulos_data', []), 'config.modulos', []);
            if (! empty($flags['rsvp']) && ! empty($flags['rsvp_whatsapp'])) {
                $validator->errors()->add('modulos', 'La invitación confirma por WhatsApp o con pase QR: deja encendida solo una de las dos.');

                return;
            }

            if ($template !== $current && ($reason = TemplateAvailability::templateReason($template))) {
                $validator->errors()->add('template', "Esa plantilla no se puede usar ahora: {$reason}.");
            }
        }];
    }

    private function checkLinkOutlivesEvent(Validator $validator): void
    {
        // «Hazlo tú» no manda vencimiento (lo calcula el servidor con la fecha del evento): ahí no hay nada que revisar
        if (! array_key_exists('expires_at', $this->rules()) || $validator->errors()->hasAny(['expires_at', 'event_date']) || ! $this->filled('expires_at') || ! $this->filled('event_date')) {
            return;
        }

        try {
            $outlives = Carbon::parse((string) $this->input('expires_at'))->endOfDay()->gte(Carbon::parse((string) $this->input('event_date'))->startOfDay());
        } catch (Throwable) {
            return;
        }

        if (! $outlives) {
            $validator->errors()->add('expires_at', 'El enlace deja de funcionar antes del evento: elige una fecha igual o posterior al día del evento.');
        }
    }

    public function attributes(): array
    {
        return InvitationModuleRules::attributes('modulos_data');
    }

    /**
     * Módulos decodificados completos (validated() descartaría las claves sin regla).
     */
    public function modulesData(): array
    {
        $modules = $this->input('modulos_data', []);

        return is_array($modules) ? $modules : [];
    }
}
