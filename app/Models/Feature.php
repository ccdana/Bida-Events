<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo de módulos (bienvenida, galería, dedicatoria…). Las filas se crean solas la primera
 * vez que un módulo registrado guarda algo, así un módulo nuevo no necesita migración de datos.
 */
class Feature extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'code'];

    public static function idFor(string $code, ?string $name = null): int
    {
        return (int) self::firstOrCreate(['code' => $code], ['name' => $name ?? $code])->id;
    }
}
