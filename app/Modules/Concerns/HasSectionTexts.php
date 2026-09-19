<?php

namespace App\Modules\Concerns;

use App\Models\Feature;
use App\Models\Invitation;
use App\Models\InvitationSection;

/**
 * Textos del encabezado de la sección (invitation_sections), para los módulos que los tienen:
 * título, subtítulo, introducción, marcador del campo y botón.
 */
trait HasSectionTexts
{
    protected function section(Invitation $invitation): ?InvitationSection
    {
        return $invitation->sections->first(fn (InvitationSection $section) => $section->feature?->code === $this->code());
    }

    /**
     * @param  array{title?: ?string, subtitle?: ?string, intro?: ?string, placeholder?: ?string, cta_text?: ?string, cta_url?: ?string}  $texts
     */
    protected function saveSection(Invitation $invitation, array $texts): void
    {
        $featureId = Feature::idFor($this->code(), $this->label());

        // Sin ningún texto no se guarda la fila (y se borra la que hubiera)
        if (array_filter($texts, fn ($value) => $value !== null && $value !== '') === []) {
            InvitationSection::where('invitation_id', $invitation->id)->where('feature_id', $featureId)->delete();

            return;
        }

        InvitationSection::updateOrCreate(
            ['invitation_id' => $invitation->id, 'feature_id' => $featureId],
            array_merge(['title' => null, 'subtitle' => null, 'intro' => null, 'placeholder' => null, 'cta_text' => null, 'cta_url' => null], $texts),
        );
    }
}
