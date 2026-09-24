<?php

namespace App\Http\Requests\Admin\Invitation\Concerns;

use App\Models\EventType;
use App\Support\InvitationModuleRules;
use App\Support\InvitationTemplates;
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
    }

    protected function moduleRules(): array
    {
        return InvitationModuleRules::rules('modulos_data');
    }

    /** La plantilla tiene que ser del tipo de evento elegido: una boda no se arma con la plantilla de XV. */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['template', 'event_type_id'])) {
                return;
            }

            $template = (string) $this->input('template');
            $template = str_starts_with($template, 'pages.') ? substr($template, strlen('pages.')) : $template;
            $event = InvitationTemplates::all()[$template]['event'] ?? null;
            $code = EventType::whereKey($this->input('event_type_id'))->value('code');

            if ($event && $code && $event !== $code) {
                $validator->errors()->add('template', 'La plantilla no corresponde al tipo de evento elegido.');
            }
        }];
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
