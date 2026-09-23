<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Credenciales de los clientes creados desde el editor: usuario a partir del
 * nombre y una contraseña que se pueda dictar por teléfono y recordar sin anotarla.
 */
class ClientCredentials
{
    /**
     * Sustantivos comunes, sin tildes ni letras que se confundan al dictarlas.
     * Todos son palabras neutrales y fáciles de escribir en un teclado de celular.
     */
    private const NOUNS = [
        'luna', 'sol', 'estrella', 'nube', 'lluvia', 'viento', 'rio', 'lago', 'mar', 'monte',
        'cerro', 'valle', 'bosque', 'jardin', 'flor', 'rosa', 'girasol', 'tulipan', 'jazmin', 'cedro',
        'pino', 'sauce', 'quinua', 'cafe', 'canela', 'miel', 'pan', 'trigo', 'arroz', 'limon',
        'naranja', 'mango', 'uva', 'pera', 'durazno', 'cereza', 'fresa', 'manzana', 'mandarina', 'almendra',
        'avena', 'cacao', 'vainilla', 'menta', 'trebol', 'helecho', 'cactus', 'palmera', 'colibri', 'paloma',
        'golondrina', 'gorrion', 'jilguero', 'aguila', 'condor', 'lechuza', 'cisne', 'garza', 'flamenco', 'tucan',
        'llama', 'alpaca', 'zorro', 'venado', 'ciervo', 'ardilla', 'caballo', 'potro', 'cordero', 'abeja',
        'mariposa', 'libelula', 'luciernaga', 'grillo', 'delfin', 'ballena', 'tortuga', 'faro', 'puente', 'torre',
        'castillo', 'plaza', 'camino', 'sendero', 'puerta', 'ventana', 'balcon', 'farol', 'vela', 'lampara',
        'linterna', 'brujula', 'mapa', 'barco', 'velero', 'canoa', 'tren', 'globo', 'cometa', 'guitarra',
        'violin', 'piano', 'tambor', 'flauta', 'campana', 'reloj', 'libro', 'carta', 'poema', 'cuento',
        'verso', 'acuarela', 'pincel', 'lienzo', 'piedra', 'arena', 'cristal', 'perla', 'ambar', 'jade',
        'topacio', 'zafiro', 'esmeralda', 'plata', 'bronce', 'seda', 'lino', 'lana', 'encaje', 'cinta',
        'anillo', 'corona', 'abanico', 'sombrero', 'bufanda', 'manta', 'taza', 'tetera', 'plato', 'fogon',
        'maceta', 'semilla', 'brote', 'hoja', 'rama', 'nido', 'pluma', 'huella', 'brisa', 'niebla',
        'rocio', 'escarcha', 'nieve', 'fuego', 'chispa', 'aroma', 'perfume', 'canto', 'melodia', 'danza',
    ];

    /**
     * Adjetivos que valen igual en masculino y femenino: así cualquier pareja de palabras
     * suena natural ("luna-brillante", "faro-brillante") sin tener que cuidar el género.
     */
    private const ADJECTIVES = [
        'alegre', 'amable', 'audaz', 'azul', 'brillante', 'celeste', 'cordial', 'dulce', 'elegante', 'estelar',
        'feliz', 'fiel', 'firme', 'fuerte', 'genial', 'gentil', 'grande', 'humilde', 'ideal', 'joven',
        'leal', 'libre', 'lunar', 'musical', 'natural', 'noble', 'radiante', 'simple', 'solar', 'suave',
        'sutil', 'tenaz', 'valiente', 'veloz', 'verde', 'vital',
    ];

    /** "María José Valenzuela" -> "maria.valenzuela"; si ya existe, "maria.valenzuela2". */
    public function username(string $name): string
    {
        $words = collect(preg_split('/\s+/', Str::lower(Str::ascii(trim($name)))) ?: [])
            ->map(fn (string $word) => preg_replace('/[^a-z0-9]/', '', $word))
            ->filter()
            ->values();

        $base = match ($words->count()) {
            0 => 'cliente',
            1 => $words->first(),
            default => $words->first().'.'.$words->last(),
        };
        $base = substr($base, 0, 50);

        $username = $base;
        $suffix = 2;

        while (User::where('username', $username)->exists()) {
            $username = $base.$suffix++;
        }

        return $username;
    }

    /**
     * Contraseña de dos palabras y tres cifras, por ejemplo "luna-brillante-473".
     *
     * Se recuerda de una sola lectura y se dicta sin deletrear, que es como llega al cliente
     * (por WhatsApp o por teléfono). Son más de cuatro millones de combinaciones y el login
     * está limitado por minuto, así que adivinarla no es un camino realista.
     */
    public function password(): string
    {
        $pick = fn (array $words) => $words[random_int(0, count($words) - 1)];

        return $pick(self::NOUNS).'-'.$pick(self::ADJECTIVES).'-'.random_int(100, 999);
    }
}
