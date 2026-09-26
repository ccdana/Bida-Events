<?php

namespace App\Support;

/**
 * Medida y forma de cada espacio de foto de las invitaciones, medidas sobre las plantillas en un
 * celular (donde las ve casi todo el mundo). El recortador del editor usa esto para encuadrar con
 * la proporción real, dibujar la forma (arco, círculo, óvalo) y guardar la foto en su tamaño justo.
 *
 * Cada marco: [ancho, alto, forma, nombre]. Formas: rect, rounded, arch, oval, circle.
 * Si una plantilla cambia la proporción de un espacio en su CSS, hay que cambiarla aquí también.
 */
final class ImageFrames
{
    /** Marco de la portada que se usa si la plantilla no tiene uno propio. */
    public const DEFAULT_HERO = [1080, 1350, 'rect', 'Foto de portada'];

    /** Portada: cambia con cada plantilla (clave del catálogo sin «invitations.templates.»). */
    public const HERO = [
        // Foto a pantalla completa detrás del nombre
        'xv-premium' => [1080, 1920, 'rect', 'Portada a pantalla completa'],
        // Arco de jardín (.inv-boda-arch, 3:4)
        'boda-jardin' => [1080, 1440, 'arch', 'Foto dentro del arco'],
        // Medallón ovalado (.inv-bautizo-medallion, 4:5)
        'bautizo-cielo' => [1080, 1350, 'oval', 'Foto del medallón'],
        // Foto instantánea inclinada (.inv-cumple-photo, 4:5)
        'cumple-fiesta' => [1080, 1350, 'rounded', 'Foto de la portada'],
        // Arco con doble filete dorado (.inv-grad-arch__frame, 4:5)
        'graduacion-birrete' => [1080, 1350, 'arch', 'Foto dentro del arco'],
        'lienzo' => [1080, 1350, 'rounded', 'Foto de la portada'],
        // La foto es la luna llena (.inv-hw-moon__disc, círculo)
        'halloween-calabazas' => [1200, 1200, 'circle', 'Foto dentro de la luna'],
        'tarjeta-amor' => [1080, 1350, 'rect', 'Foto de la tarjeta'],
        // Foto pegada en la tapa del libro (.nb-cover__photo, 4:4.4)
        'tarjeta-aventura' => [1000, 1100, 'rect', 'Foto de la tapa'],
        // Foto vista a través del agua (.story-underwater, 4:5 con arco arriba)
        'we-story-together' => [1080, 1350, 'arch', 'Foto de la portada'],
        // Foto sujeta con esquineros dentro de la carta (.cb-photo, 4:5)
        'xv-carta-de-baile' => [1080, 1350, 'rect', 'Foto de la carta'],
        // La foto es el punto donde se juntan los caminos (.dc-meet__photo, círculo)
        'boda-dos-caminos' => [1200, 1200, 'circle', 'Foto del punto de encuentro'],
        // Ventanilla del avión (.ps-window, 4:5 con esquinas muy redondas)
        'graduacion-proxima-salida' => [1080, 1350, 'rounded', 'Foto en la ventanilla'],
        // Centro de las ondas (.gt-center, círculo)
        'bautizo-la-gota' => [1200, 1200, 'circle', 'Foto en el centro de las ondas'],
        // Sticker de foto con borde troquelado (.st-photo, 4:5)
        'cumple-stickers' => [1080, 1350, 'rounded', 'Foto del sticker'],
        // Foto instantánea con clip (.ex-photo, cuadrada)
        'halloween-expediente' => [1080, 1080, 'rect', 'Foto del expediente'],
    ];

    /** El resto de los espacios, por el contexto con el que sube la foto el editor. */
    public const CONTEXTS = [
        // Galería en pila (.inv-gallery__stack, 4:5)
        'gallery' => [1080, 1350, 'rect', 'Foto de la galería'],
        'post-evento' => [1080, 1350, 'rect', 'Foto de después del evento'],
        // Foto del lugar junto al mapa (.inv-location__photo, 16:10)
        'ubicacion' => [1600, 1000, 'rect', 'Foto del lugar'],
        'video-poster' => [1600, 900, 'rect', 'Portada del video'],
        // Ejemplo de vestimenta (.inv-dress__image, 4:5)
        'dress-code' => [1080, 1350, 'rect', 'Ejemplo de vestimenta'],
        'qr-banco' => [900, 900, 'rect', 'Código QR para transferir'],
        // «Nuestra historia»: foto de cada momento (.story-moment__photo, 4:3)
        'story' => [1600, 1200, 'rect', 'Foto del momento'],
        // «Libro de aventuras»
        'historia' => [1600, 1000, 'rect', 'Foto del capítulo'],
        'recuerdos' => [1080, 1350, 'rect', 'Foto del recuerdo'],
        'marcos' => [1080, 1350, 'oval', 'Foto del marco'],
        'memoria' => [1000, 1000, 'rect', 'Foto del juego de memoria'],
        'collage' => [1000, 1000, 'rect', 'Foto del collage'],
    ];

    /** Lo que recibe el editor: el marco de portada de cada plantilla y el de cada contexto. */
    public static function forEditor(): array
    {
        $shape = fn (array $frame) => ['width' => $frame[0], 'height' => $frame[1], 'shape' => $frame[2], 'label' => $frame[3]];

        return [
            'hero' => collect(InvitationTemplates::all())
                ->keys()
                ->mapWithKeys(fn (string $key) => [$key => $shape(self::HERO[self::short($key)] ?? self::DEFAULT_HERO)])
                ->all(),
            'contexts' => array_map($shape, self::CONTEXTS),
        ];
    }

    private static function short(string $template): string
    {
        return str_replace('invitations.templates.', '', $template);
    }
}
