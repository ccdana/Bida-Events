<?php

namespace App\Modules\Concerns;

/**
 * Lectura tolerante de la forma que llega del editor: la validación ya filtró lo peligroso,
 * aquí solo se evita guardar tipos inesperados.
 */
trait ReadsValues
{
    protected function text(mixed $value, ?int $limit = null): ?string
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return null;
        }

        $value = trim(is_bool($value) ? ($value ? '1' : '0') : (string) $value);

        if ($value === '') {
            return null;
        }

        return $limit !== null ? mb_substr($value, 0, $limit) : $value;
    }

    protected function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    /** @return array<int|string, mixed> */
    protected function items(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /** @return list<mixed> */
    protected function list(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }

    /** Forma de salida: quita las claves vacías (null), como la guardaba el JSON. */
    protected function compact(array $values): array
    {
        return array_filter($values, fn ($value) => $value !== null);
    }

    protected function filled(mixed $value): bool
    {
        return is_array($value) ? $value !== [] : trim((string) $value) !== '';
    }
}
