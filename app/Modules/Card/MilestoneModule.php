<?php

namespace App\Modules\Card;

use App\Models\Invitation;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\ReplacesOrderedRows;
use App\Modules\Module;
use Illuminate\Support\Carbon;
use Throwable;

/** «Juntos desde»: una fecha importante y un contador de cuánto tiempo pasó. */
class MilestoneModule extends Module
{
    use ReadsValues, ReplacesOrderedRows;

    public function code(): string
    {
        return 'juntos_desde';
    }

    public function label(): string
    {
        return 'Juntos desde';
    }

    public function kinds(): array
    {
        return [self::KIND_CARD];
    }

    public function defaults(): object
    {
        return (object) [];
    }

    public function relations(): array
    {
        return ['milestones'];
    }

    public function load(Invitation $invitation): array
    {
        $milestone = $invitation->milestones->first();

        return $milestone ? $this->compact([
            'titulo' => $milestone->label,
            'fecha' => $milestone->started_on?->toDateString(),
        ]) : [];
    }

    public function save(Invitation $invitation, array $data): void
    {
        $date = $this->date($data['fecha'] ?? null);
        $label = $this->text($data['titulo'] ?? null, 255);

        $this->replaceOrdered(
            $invitation->milestones(),
            $date === null ? [] : [['label' => $label, 'started_on' => $date]],
        );
    }

    public function hasContent(array $data): bool
    {
        return $this->date($data['fecha'] ?? null) !== null;
    }

    public function rules(string $prefix): array
    {
        return [
            "{$prefix}.juntos_desde.titulo" => ['nullable', 'string', 'max:255'],
            "{$prefix}.juntos_desde.fecha" => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function attributes(string $prefix): array
    {
        return [
            "{$prefix}.juntos_desde.titulo" => 'texto del contador',
            "{$prefix}.juntos_desde.fecha" => 'fecha desde la que están juntos',
        ];
    }

    public function partial(): string
    {
        return 'invitations.partials.modules.juntos-desde';
    }

    public function panel(): string
    {
        return 'admin.invitations.panels.modules.juntos-desde';
    }

    protected function date(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
