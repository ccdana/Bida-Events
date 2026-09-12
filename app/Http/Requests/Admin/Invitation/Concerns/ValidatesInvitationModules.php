<?php

namespace App\Http\Requests\Admin\Invitation\Concerns;

use App\Support\InvitationModuleRules;

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
