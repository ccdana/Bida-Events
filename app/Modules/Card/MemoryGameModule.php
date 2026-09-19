<?php

namespace App\Modules\Card;

use App\Models\Invitation;
use App\Models\InvitationGalleryImage;

/**
 * Juego de memoria con fotos de la pareja: cada foto aparece dos veces y hay que encontrar los
 * pares. Al terminar se muestra el mensaje final. Todo pasa en el navegador; no guarda nada.
 */
class MemoryGameModule extends PhotoCollectionModule
{
    public const MIN_PHOTOS = 3;

    public const MAX_PHOTOS = 8;

    public function code(): string
    {
        return 'memoria';
    }

    public function label(): string
    {
        return 'Juego de memoria';
    }

    protected function collection(): string
    {
        return InvitationGalleryImage::COLLECTION_MEMORY_GAME;
    }

    protected function maxPhotos(): int
    {
        return self::MAX_PHOTOS;
    }

    public function load(Invitation $invitation): array
    {
        return parent::load($invitation) + $this->compact([
            'mensaje_final' => $this->section($invitation)?->intro,
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $this->savePhotos($invitation, $this->collection(), $data['fotos'] ?? null);
        $this->saveSection($invitation, [
            'title' => $this->text($data['titulo'] ?? null, 255),
            'intro' => $this->text($data['mensaje_final'] ?? null, 300),
        ]);
    }

    /** Con menos de tres pares no hay juego. */
    public function hasContent(array $data): bool
    {
        return count($this->list($data['fotos'] ?? [])) >= self::MIN_PHOTOS;
    }

    public function rules(string $prefix): array
    {
        $rules = parent::rules($prefix);
        // Vacío se acepta (el juego queda apagado); con fotos, al menos tres pares
        $rules["{$prefix}.memoria.fotos"] = ['nullable', 'array', 'max:'.self::MAX_PHOTOS, function (string $attribute, mixed $value, \Closure $fail) {
            if (is_array($value) && $value !== [] && count($value) < self::MIN_PHOTOS) {
                $fail('El juego de memoria necesita al menos '.self::MIN_PHOTOS.' fotos.');
            }
        }];
        $rules["{$prefix}.memoria.mensaje_final"] = ['nullable', 'string', 'max:300'];

        return $rules;
    }

    public function attributes(string $prefix): array
    {
        return parent::attributes($prefix) + [
            "{$prefix}.memoria.mensaje_final" => 'mensaje al ganar',
        ];
    }
}
