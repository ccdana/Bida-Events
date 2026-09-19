<?php

namespace App\Support;

/**
 * Reparte un texto largo en páginas del cuaderno («Libro de aventuras»): la carta y cada capítulo
 * de la historia ocupan las páginas que necesiten. Corta entre párrafos; si un párrafo no entra,
 * lo corta en el último final de oración o espacio que quepa. No se pierde ni se agrega texto.
 */
final class NotebookPaginator
{
    /** Por debajo de este espacio libre no vale la pena empezar un párrafo: pasa a la hoja siguiente. */
    private const MIN_ROOM = 120;

    /**
     * @param  int  $firstLimit  Caracteres que caben en la primera página (suele llevar título y foto)
     * @param  int  $limit  Caracteres que caben en las siguientes
     * @return list<string> Texto de cada página; los párrafos van separados por una línea en blanco
     */
    public static function pages(?string $text, int $firstLimit, int $limit): array
    {
        $paragraphs = array_values(array_filter(
            array_map('trim', preg_split('/\R\s*\R/u', trim((string) $text)) ?: []),
            fn (string $paragraph) => $paragraph !== '',
        ));

        $pages = [];
        $current = '';
        $budget = max(1, $firstLimit);

        foreach ($paragraphs as $paragraph) {
            while ($paragraph !== '') {
                $room = $budget - mb_strlen($current) - ($current === '' ? 0 : 2);

                if (mb_strlen($paragraph) <= $room) {
                    $current = $current === '' ? $paragraph : $current."\n\n".$paragraph;
                    $paragraph = '';

                    continue;
                }

                if ($current !== '' && $room < self::MIN_ROOM) {
                    $pages[] = $current;
                    $current = '';
                    $budget = max(1, $limit);

                    continue;
                }

                $cut = self::cutPoint($paragraph, max(1, $room));
                $piece = rtrim(mb_substr($paragraph, 0, $cut));
                $paragraph = ltrim(mb_substr($paragraph, $cut));

                $pages[] = $current === '' ? $piece : $current."\n\n".$piece;
                $current = '';
                $budget = max(1, $limit);
            }
        }

        if ($current !== '') {
            $pages[] = $current;
        }

        return $pages;
    }

    /** Posición donde cortar un párrafo que no entra: fin de oración, si no un espacio, si no a la fuerza. */
    private static function cutPoint(string $paragraph, int $room): int
    {
        $window = mb_substr($paragraph, 0, $room);

        if (preg_match_all('/[.!?…»)](?=\s)/u', $window, $matches, PREG_OFFSET_CAPTURE)) {
            $last = end($matches[0]);
            $position = mb_strlen(substr($window, 0, $last[1])) + 1;

            if ($position >= $room * 0.5) {
                return $position;
            }
        }

        $space = mb_strrpos($window, ' ');

        return $space !== false && $space > 0 ? $space : $room;
    }
}
