<?php

namespace App\Modules\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ReplacesOrderedRows
{
    /**
     * Guarda una lista ordenada reutilizando las filas que ya existen: actualiza las primeras,
     * crea las que faltan y borra las que sobran. Así no cambian los ids (nada que dependa de
     * ellos se rompe) ni se tocan los timestamps de filas que no cambiaron.
     *
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, mixed>  $defaults  Valores que identifican al grupo (por ejemplo, la colección)
     * @return list<Model> Filas guardadas, en orden
     */
    protected function replaceOrdered(HasMany $relation, array $rows, array $defaults = []): array
    {
        $existing = (clone $relation)->reorder()->orderBy('sort_order')->orderBy('id')->get();
        $saved = [];

        foreach (array_values($rows) as $index => $row) {
            $attributes = $row + $defaults + ['sort_order' => $index];
            $current = $existing[$index] ?? null;

            if ($current) {
                $current->fill($attributes)->save();
                $saved[] = $current;

                continue;
            }

            $saved[] = $relation->create($attributes);
        }

        $extra = $existing->slice(count($rows))->pluck('id');

        if ($extra->isNotEmpty()) {
            $relation->getRelated()->newQuery()->whereKey($extra->all())->delete();
        }

        return $saved;
    }
}
