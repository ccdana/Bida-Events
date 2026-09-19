<?php

namespace App\Modules\Card;

use App\Models\CardStory;
use App\Models\Invitation;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\ReplacesOrderedRows;
use App\Modules\Module;
use Illuminate\Validation\Rule;

/**
 * «Relato en cuatro actos»: lo que la pareja cuenta en cada acto de la plantilla we-story-together
 * («Bajo la misma luna»). Código «relato»: «historia» es el de los capítulos del libro de aventuras.
 * Solo guarda lo que no existe en otro módulo: primeras impresiones, momentos clave, la cita
 * elegida, la anécdota, la reflexión y la promesa. Cada campo vacío se reemplaza en la vista por
 * un texto propio del acto (InvitationTemplates, «copy»), así ningún acto pierde su sentido.
 */
class StoryActsModule extends Module
{
    use ReadsValues, ReplacesOrderedRows;

    public const CODE = 'relato';

    public const MAX_MOMENTS = 6;

    public const DEFAULT_QUOTE = 'cortazar';

    /** Citas verificadas con su obra; la pareja elige una para el Acto II. */
    public const QUOTES = [
        'cortazar' => ['text' => 'Andábamos sin buscarnos, pero sabiendo que andábamos para encontrarnos.', 'author' => 'Julio Cortázar', 'work' => 'Rayuela'],
        'neruda' => ['text' => 'Te amo sin saber cómo, ni cuándo, ni de dónde, te amo directamente sin problemas ni orgullo.', 'author' => 'Pablo Neruda', 'work' => 'Soneto XVII'],
        'paz' => ['text' => 'El mundo cambia si dos se miran y se reconocen.', 'author' => 'Octavio Paz', 'work' => 'Piedra de sol'],
        'rilke' => ['text' => 'El amor consiste en esto: dos soledades que se protegen, se tocan y se saludan.', 'author' => 'Rainer Maria Rilke', 'work' => 'Cartas a un joven poeta'],
        'benedetti' => ['text' => 'Y en la calle, codo a codo, somos mucho más que dos.', 'author' => 'Mario Benedetti', 'work' => 'Te quiero'],
    ];

    public function code(): string
    {
        return self::CODE;
    }

    public function label(): string
    {
        return 'Relato en cuatro actos';
    }

    public function kinds(): array
    {
        return [self::KIND_CARD];
    }

    public function defaults(): object
    {
        return (object) [];
    }

    public function visibleByDefault(): bool
    {
        return true;
    }

    public function relations(): array
    {
        return ['story', 'storyMoments'];
    }

    public function load(Invitation $invitation): array
    {
        $story = $invitation->story;

        $data = $story ? $this->compact([
            'primeras_impresiones' => $story->first_impression,
            'cita' => $story->quote_key,
            'anecdota_titulo' => $story->anecdote_title,
            'anecdota' => $story->anecdote,
            'anecdota_foto' => $story->anecdote_photo,
            'anecdota_foto_alt' => $story->anecdote_photo_alt,
            'reflexion' => $story->reflection,
            'promesa' => $story->promise,
        ]) : [];

        $moments = $invitation->storyMoments->map(fn ($moment) => $this->compact([
            'cuando' => $moment->when_label,
            'titulo' => $moment->title,
            'descripcion' => $moment->description,
            'foto' => $moment->photo,
            'foto_alt' => $moment->photo_alt,
        ]))->all();

        if ($moments !== []) {
            $data['momentos'] = $moments;
        }

        return $data;
    }

    public function save(Invitation $invitation, array $data): void
    {
        $quote = $this->text($data['cita'] ?? null, 50);

        $attributes = [
            'first_impression' => $this->text($data['primeras_impresiones'] ?? null, 1500),
            'quote_key' => isset(self::QUOTES[$quote]) ? $quote : null,
            'anecdote_title' => $this->text($data['anecdota_titulo'] ?? null, 255),
            'anecdote' => $this->text($data['anecdota'] ?? null, 3000),
            'anecdote_photo' => $this->text($data['anecdota_foto'] ?? null, 2048),
            'anecdote_photo_alt' => $this->text($data['anecdota_foto_alt'] ?? null, 255),
            'reflection' => $this->text($data['reflexion'] ?? null, 2000),
            'promise' => $this->text($data['promesa'] ?? null, 1000),
        ];

        if (array_filter($attributes) === []) {
            CardStory::where('invitation_id', $invitation->id)->delete();
        } else {
            CardStory::updateOrCreate(['invitation_id' => $invitation->id], $attributes);
        }

        $moments = [];

        foreach (array_slice($this->list($data['momentos'] ?? null), 0, self::MAX_MOMENTS) as $moment) {
            $row = [
                'when_label' => $this->text($moment['cuando'] ?? null, 100),
                'title' => $this->text($moment['titulo'] ?? null, 255),
                'description' => $this->text($moment['descripcion'] ?? null, 1000),
                'photo' => $this->text($moment['foto'] ?? null, 2048),
                'photo_alt' => $this->text($moment['foto_alt'] ?? null, 255),
            ];

            // Un momento sin título ni texto no cuenta nada: no se guarda
            if ($row['title'] !== null || $row['description'] !== null) {
                $moments[] = $row;
            }
        }

        $this->replaceOrdered($invitation->storyMoments(), $moments);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['anecdota'] ?? null)
            || $this->filled($data['reflexion'] ?? null)
            || $this->filled($data['momentos'] ?? []);
    }

    public function rules(string $prefix): array
    {
        $p = "{$prefix}.".self::CODE;
        $url = ['nullable', 'string', 'max:2048', 'not_regex:/^\s*(blob|data):/i'];

        return [
            "{$p}.primeras_impresiones" => ['nullable', 'string', 'max:1500'],
            "{$p}.cita" => ['nullable', 'string', Rule::in(array_keys(self::QUOTES))],
            "{$p}.anecdota_titulo" => ['nullable', 'string', 'max:255'],
            "{$p}.anecdota" => ['nullable', 'string', 'max:3000'],
            "{$p}.anecdota_foto" => $url,
            "{$p}.anecdota_foto_alt" => ['nullable', 'string', 'max:255'],
            "{$p}.reflexion" => ['nullable', 'string', 'max:2000'],
            "{$p}.promesa" => ['nullable', 'string', 'max:1000'],
            "{$p}.momentos" => ['nullable', 'array', 'max:'.self::MAX_MOMENTS],
            "{$p}.momentos.*" => ['array'],
            "{$p}.momentos.*.cuando" => ['nullable', 'string', 'max:100'],
            "{$p}.momentos.*.titulo" => ['nullable', 'string', 'max:255'],
            "{$p}.momentos.*.descripcion" => ['nullable', 'string', 'max:1000'],
            "{$p}.momentos.*.foto" => $url,
            "{$p}.momentos.*.foto_alt" => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(string $prefix): array
    {
        $p = "{$prefix}.".self::CODE;

        return [
            "{$p}.primeras_impresiones" => 'primeras impresiones',
            "{$p}.cita" => 'cita',
            "{$p}.anecdota_titulo" => 'título de la anécdota',
            "{$p}.anecdota" => 'anécdota',
            "{$p}.anecdota_foto" => 'foto de la anécdota',
            "{$p}.reflexion" => 'reflexión final',
            "{$p}.promesa" => 'promesa',
            "{$p}.momentos" => 'momentos',
            "{$p}.momentos.*.cuando" => 'cuándo fue el momento',
            "{$p}.momentos.*.titulo" => 'título del momento',
            "{$p}.momentos.*.descripcion" => 'texto del momento',
            "{$p}.momentos.*.foto" => 'foto del momento',
        ];
    }

    public function panel(): string
    {
        return 'admin.invitations.panels.modules.relato';
    }

    /** La cita elegida, o la de siempre si no eligieron ninguna. */
    public static function quote(?string $key): array
    {
        return self::QUOTES[$key] ?? self::QUOTES[self::DEFAULT_QUOTE];
    }
}
