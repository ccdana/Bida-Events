<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Invitation;
use App\Support\InvitationDefaults;

/**
 * Guardado de los módulos del editor, compartido por el editor del administrador y el del
 * revendedor: los dos mandan lo mismo y se guarda igual. Quien lo use necesita la propiedad
 * $moduleService (App\Services\InvitationModuleService).
 */
trait SavesInvitationModules
{
    protected function syncModules(Invitation $invitation, array $modulesData): void
    {
        $modules = InvitationDefaults::emptyModules();

        foreach (InvitationDefaults::moduleCodes() as $code) {
            $modules[$code] = is_array($modulesData[$code] ?? null) ? $modulesData[$code] : [];
        }

        $this->moduleService->syncAllModules($invitation, $modules);
        $invitation->touch();
    }

    /**
     * Si el último guardado no pasó la validación, el editor se reabre con lo que el usuario había enviado.
     */
    protected function modulesFromOldInput(): ?array
    {
        $old = session()->getOldInput('modulos');

        if (! is_array($old)) {
            return null;
        }

        $modules = InvitationDefaults::emptyModules();

        foreach (InvitationDefaults::moduleCodes() as $code) {
            $decoded = is_string($old[$code] ?? null) ? json_decode($old[$code], true) : null;

            if (is_array($decoded)) {
                $modules[$code] = $decoded;
            }
        }

        return $this->moduleService->normalizeModules($modules);
    }
}
