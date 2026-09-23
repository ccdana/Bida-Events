<?php

namespace App\Support\Pdf;

/**
 * Reparte los complementos en la segunda hoja de la invitación impresa.
 *
 * La regla es que el PDF tenga dos hojas siempre: una invitación con tres momentos en el
 * itinerario y otra con doce no pueden salir con distinta cantidad de hojas. Para eso, cada
 * bloque estima cuánto alto ocupa (en milímetros, medidos sobre una columna de 93 mm) y, si
 * entre todos se pasan de la hoja, primero se sueltan las descripciones largas y recién después
 * los bloques menos importantes, avisando en el PDF qué quedó solo en la invitación digital.
 *
 * Bloque nuevo: se agrega a blocks() con su vista, su rótulo y su alto estimado. El orden del
 * array es el orden de importancia: lo último es lo primero que se suelta.
 */
final class PrintLayout
{
    /**
     * Alto aprovechable de la hoja 2 sumando las dos columnas, en las dos densidades de texto.
     * Los números salen de medir el PDF de verdad (DomPDF compone más alto que un navegador):
     * ver ClientExportsTest, que comprueba que ninguna invitación se pase de dos hojas.
     */
    private const BUDGET = 330;

    private const BUDGET_TIGHT = 390;

    /** Lo que mide una línea de texto y cuántos caracteres entran en una columna. */
    private const LINE = 5.0;

    private const CHARS = 34;

    /** Las descripciones del itinerario van en una columna más angosta (a la derecha de la hora). */
    private const CHARS_NARROW = 26;

    /** Alto aprovechable de la hoja 1 (mm). Pasado ese punto, la portada se compone más chica. */
    private const COVER_BUDGET = 250;

    /** Alto de la tarjeta de la hoja 1, para que la invitación ocupe la hoja y no flote arriba. */
    private const COVER_FILL = 196;

    /**
     * La portada: cuánto alto llena y si hay que componerla apretada.
     *
     * Una invitación con un mensaje de tres renglones y otra con uno de diez tienen que salir las
     * dos en una sola hoja, así que cuando el texto crece la composición se aprieta.
     *
     * @return array{fill: int, tight: bool}
     */
    public static function cover(array $data): array
    {
        $hero = $data['hero'];

        $weight = 150
            + self::wrapped($hero['message'] ?? null, 46, 6.5)
            + (($hero['date'] || $hero['time'] || $data['location']) ? 28 : 0)
            + 30 + self::wrapped($data['rsvp']['message'] ?? null, 60, 5)
            + (($data['hashtag'] ?? null) ? 6 : 0);

        $tight = $weight > self::COVER_BUDGET;

        return [
            'fill' => $tight ? 0 : self::COVER_FILL,
            'tight' => $tight,
        ];
    }

    /**
     * @param  array<string, mixed>  $data  lo que arma InvitationPrintData
     * @return array{columns: array<int, array<int, string>>, dropped: array<int, string>, compact: bool, tight: bool}
     */
    public static function make(array &$data): array
    {
        $blocks = self::blocks($data);
        $compact = false;

        // 1. Si no entra, las descripciones largas se van antes que cualquier bloque entero
        if (self::total($blocks) > self::BUDGET) {
            $compact = true;
            $data = self::withoutDescriptions($data);
            $blocks = self::blocks($data, compact: true);
        }

        // 2. Si sigue sin entrar, la hoja pasa a letra más apretada antes de perder contenido
        $tight = self::total($blocks) > self::BUDGET;
        $budget = $tight ? self::BUDGET_TIGHT : self::BUDGET;

        // 3. Y recién entonces se sueltan los bloques menos importantes
        $dropped = [];

        while (self::total($blocks) > $budget && count($blocks) > 1) {
            $last = array_pop($blocks);
            $dropped[] = $last['label'];
        }

        return [
            'columns' => self::columns($blocks),
            'dropped' => array_reverse($dropped),
            'compact' => $compact,
            'tight' => $tight,
        ];
    }

    /** Bloques presentes, del más importante al que se suelta primero. */
    private static function blocks(array $data, bool $compact = false): array
    {
        $location = $data['location'] ?? null;
        $dress = $data['dressCode'] ?? null;
        $gifts = $data['gifts'] ?? null;
        $honor = $data['honor'] ?? null;

        return array_values(array_filter([
            $location ? [
                'view' => 'ubicacion',
                'label' => 'cómo llegar',
                'weight' => 12 + 7 + self::lines($location['address'] ?? null)
                    + ($location['note'] ? 4 + self::lines($location['note']) : 0)
                    + ($location['qr'] ? 30 : 0),
            ] : null,
            ($data['itinerary'] ?? []) ? [
                'view' => 'itinerario',
                'label' => 'el itinerario',
                'weight' => 12 + collect($data['itinerary'])->sum(
                    fn (array $item) => 7 + ($compact ? 0 : self::lines($item['description'] ?? null, self::CHARS_NARROW))
                ),
            ] : null,
            $honor ? [
                'view' => 'honor',
                'label' => 'los padrinos',
                'weight' => 12 + count($honor['godparents'] ?? []) * 8
                    + (($honor['chambelanes'] ?? []) ? 4 + self::lines(implode(', ', $honor['chambelanes'])) : 0)
                    + (($honor['damitas'] ?? []) ? 4 + self::lines(implode(', ', $honor['damitas'])) : 0),
            ] : null,
            $dress ? [
                'view' => 'vestimenta',
                'label' => 'cómo vestir',
                'weight' => 12 + ($dress['style'] ? 7 : 0) + self::lines($dress['description'] ?? null)
                    + (int) ceil(count($dress['colors'] ?? []) / 2) * 6
                    + collect($dress['suggestions'] ?? [])->sum(fn (array $item) => 7 + ($compact ? 0 : self::lines($item['description'] ?? null)))
                    + (($dress['avoid'] ?? []) ? 4 + self::lines(implode(', ', $dress['avoid'])) : 0),
            ] : null,
            $gifts ? [
                'view' => 'regalos',
                'label' => 'los regalos',
                'weight' => 12 + ($gifts['envelopes'] ? 7 + self::lines($gifts['envelopes']['address']) : 0)
                    + count($gifts['bank'] ?? []) * 6 + ($gifts['bankQr'] ? 30 : 0)
                    + ($gifts['store'] ? 4 + self::lines($gifts['store']['url']) : 0)
                    + collect($gifts['options'] ?? [])->sum(fn (array $item) => 7 + ($compact ? 0 : self::lines($item['description'] ?? null))),
            ] : null,
            ($data['hashtag'] ?? null) ? ['view' => 'hashtag', 'label' => 'el hashtag', 'weight' => 22] : null,
        ]));
    }

    /** Las descripciones son lo primero que sobra: el dato (hora, título, monto) se queda. */
    private static function withoutDescriptions(array $data): array
    {
        $data['itinerary'] = array_map(fn (array $item) => [...$item, 'description' => null], $data['itinerary'] ?? []);

        if ($data['dressCode'] ?? null) {
            $data['dressCode']['suggestions'] = array_map(
                fn (array $item) => [...$item, 'description' => null],
                $data['dressCode']['suggestions']
            );
        }

        if ($data['gifts'] ?? null) {
            $data['gifts']['options'] = array_map(fn (array $item) => [...$item, 'description' => null], $data['gifts']['options']);
        }

        return $data;
    }

    /** Dos columnas parejas: se va llenando la primera hasta pasar la mitad del alto. */
    private static function columns(array $blocks): array
    {
        $half = self::total($blocks) / 2;
        $carried = 0;
        $columns = [[], []];

        foreach ($blocks as $block) {
            // El bloque que cruza la mitad se queda entero donde empieza: no se parte en dos
            $columns[$carried >= $half ? 1 : 0][] = $block['view'];
            $carried += $block['weight'];
        }

        return $columns;
    }

    private static function total(array $blocks): float
    {
        return array_sum(array_column($blocks, 'weight'));
    }

    /** Alto de un texto envuelto en una columna, en milímetros. */
    private static function lines(?string $text, int $chars = self::CHARS): float
    {
        return self::wrapped($text, $chars, self::LINE);
    }

    /** Lo mismo, con el alto de renglón que corresponda (la portada usa letra más grande). */
    private static function wrapped(?string $text, int $chars, float $line): float
    {
        $length = mb_strlen((string) $text);

        return $length === 0 ? 0 : ceil($length / $chars) * $line;
    }
}
