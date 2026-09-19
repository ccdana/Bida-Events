<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationPoll;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\ReplacesOrderedRows;
use App\Modules\Module;
use Illuminate\Support\Str;

/**
 * Encuestas con sus opciones. Cada encuesta se actualiza por su clave (poll_key) para conservar
 * su fila y, con ella, los votos que ya recibió.
 */
class PollsModule extends Module
{
    use HasSectionTexts, ReadsValues, ReplacesOrderedRows;

    public function code(): string
    {
        return 'encuestas';
    }

    public function label(): string
    {
        return 'Encuestas';
    }

    public function defaults(): array
    {
        return ['preguntas' => []];
    }

    public function relations(): array
    {
        return ['polls.options', 'sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        return $this->compact([
            'titulo' => $this->section($invitation)?->title,
            'preguntas' => $invitation->polls
                ->map(fn (InvitationPoll $poll) => [
                    'id' => $poll->poll_key,
                    'tipo' => $poll->type,
                    'pregunta' => $poll->question,
                    'opciones' => $poll->options->pluck('label')->all(),
                ])
                ->all(),
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $keptIds = [];
        $seenKeys = [];

        foreach ($this->list($data['preguntas'] ?? null) as $index => $question) {
            if (! is_array($question)) {
                continue;
            }

            $key = $this->text($question['id'] ?? null, 100) ?? 'poll-'.Str::lower(Str::random(12));

            // Una clave repetida pisaría a la otra encuesta: la segunda se descarta
            if (isset($seenKeys[$key])) {
                continue;
            }

            $seenKeys[$key] = true;

            $poll = InvitationPoll::updateOrCreate(
                ['invitation_id' => $invitation->id, 'poll_key' => $key],
                [
                    'question' => $this->text($question['pregunta'] ?? null) ?? '',
                    'type' => $this->text($question['tipo'] ?? null, 20) ?? 'single',
                    'is_enabled' => true,
                    'sort_order' => $index,
                ],
            );

            $options = [];

            foreach ($this->list($question['opciones'] ?? null) as $option) {
                if (is_scalar($option)) {
                    $options[] = ['label' => (string) $option];
                }
            }

            $this->replaceOrdered($poll->options(), $options);
            $keptIds[] = $poll->id;
        }

        $invitation->polls()->whereNotIn('id', $keptIds)->delete();
        $this->saveSection($invitation, ['title' => $this->text($data['titulo'] ?? null, 255)]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['preguntas'] ?? []);
    }
}
