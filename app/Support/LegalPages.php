<?php

namespace App\Support;

/**
 * Textos legales del sitio: política de privacidad, de cookies y términos de uso. Cada página es
 * una lista de secciones (título, párrafos, una lista si hace falta y una nota al final) que dibuja
 * resources/views/legal/show.blade.php. Los datos de la empresa salen de config('bida.legal').
 *
 * Describen lo que el sistema hace de verdad: qué se guarda, quién lo ve, cuánto dura y qué
 * cookies usa. Si cambia algo de eso (un proveedor nuevo, otro plazo de borrado), se cambia aquí.
 */
final class LegalPages
{
    public const PAGES = ['privacidad', 'cookies', 'terminos'];

    /**
     * @return array{title: string, description: string, intro: string, sections: list<array{title: string, paragraphs?: list<string>, items?: list<string>, note?: list<string>}>}
     */
    public static function get(string $page): array
    {
        return match ($page) {
            'privacidad' => self::privacy(),
            'cookies' => self::cookies(),
            'terminos' => self::terms(),
        };
    }

    /** Enlaces del pie a las tres páginas. */
    public static function links(): array
    {
        return [
            ['url' => route('legal', 'privacidad'), 'label' => 'Privacidad'],
            ['url' => route('legal', 'cookies'), 'label' => 'Cookies'],
            ['url' => route('legal', 'terminos'), 'label' => 'Términos'],
        ];
    }

    private static function owner(): string
    {
        $legal = config('bida.legal', []);
        $name = $legal['name'] ?: config('bida.brand');

        return $legal['nit'] ? "{$name} (NIT {$legal['nit']})" : $name;
    }

    private static function contact(): string
    {
        return collect([config('bida.email'), '+'.ltrim((string) config('bida.whatsapp'), '+').' (WhatsApp)'])->filter()->implode(' o al ');
    }

    private static function privacy(): array
    {
        $brand = config('bida.brand');
        $photosDays = (int) config('optimizations.retention.photos_days', 180);

        return [
            'title' => 'Política de privacidad',
            'description' => "Qué datos guarda {$brand}, para qué los usa, quién los ve y cómo pedir que se borren.",
            'intro' => "En {$brand} hacemos invitaciones y tarjetas digitales. Para que funcionen guardamos algunos datos del organizador del evento y de sus invitados. Aquí explicamos cuáles, para qué y qué puedes hacer con ellos.",
            'sections' => [
                [
                    'title' => 'Quién es responsable',
                    'paragraphs' => [
                        'El responsable de los datos es '.self::owner().', con domicilio en '.config('bida.city').'. Puedes escribirnos a '.self::contact().'.',
                        'Cuando un profesional (fotógrafo, organizador de eventos u otro revendedor) arma una invitación con nuestra plataforma, esa persona decide qué datos de sus invitados carga y es responsable de ellos frente a su cliente; nosotros los guardamos y los procesamos por encargo suyo.',
                    ],
                ],
                [
                    'title' => 'Qué datos guardamos',
                    'items' => [
                        'Del organizador o cliente: nombre, usuario y contraseña de acceso (la contraseña se guarda cifrada), teléfono si nos lo da, y el contenido de su invitación: nombres, fechas, lugares, fotos, videos, música y textos.',
                        'De los invitados: el nombre y el teléfono que carga el organizador, la respuesta de asistencia, cuántas personas van y, si lo escriben, sus alergias o restricciones alimentarias.',
                        'Lo que los invitados comparten por su cuenta: canciones sugeridas, votos en encuestas, respuestas a una tarjeta y fotos subidas al fotomural.',
                        'De quienes visitan este sitio: desde qué campaña llegaron (por ejemplo, un anuncio en Facebook) durante 30 días, para saber qué publicidad funciona. No guardamos el nombre ni el teléfono de un visitante hasta que nos escribe.',
                    ],
                ],
                [
                    'title' => 'Para qué los usamos',
                    'items' => [
                        'Mostrar la invitación, recibir las confirmaciones y armar la lista de invitados, el pase con código QR y los reportes en PDF o Excel del organizador.',
                        'Dar acceso al panel al organizador y, si corresponde, al profesional que armó la invitación.',
                        'Responder consultas y coordinar pedidos y pagos por WhatsApp.',
                        'Proteger el servicio: limitar intentos repetidos y guardar registros técnicos para detectar abusos.',
                    ],
                    'note' => [
                        'No vendemos datos, no los usamos para publicidad de terceros y no enviamos mensajes automáticos a los invitados: los enlaces los comparte el organizador.',
                    ],
                ],
                [
                    'title' => 'Quién más los ve',
                    'items' => [
                        'El organizador del evento ve las respuestas de sus invitados. Los invitados solo ven la invitación y, en su enlace personal, su propio pase.',
                        'Las invitaciones no aparecen en buscadores: solo las abre quien tiene el enlace.',
                        'Las fotos, videos y audios se alojan en Cloudinary; los mapas se muestran con Google Maps y las tipografías se cargan desde Google Fonts. Esos servicios reciben la dirección IP de quien abre la invitación, como cualquier sitio que usa sus herramientas.',
                    ],
                ],
                [
                    'title' => 'Cuánto tiempo los guardamos',
                    'items' => [
                        'La invitación y sus respuestas se conservan mientras el organizador la tenga activa y hasta su fecha de vencimiento.',
                        "Las fotos que suben los invitados al fotomural se borran {$photosDays} días después del evento.",
                        'Los reportes descargables (PDF y Excel) se borran a los pocos días de generarse.',
                        'Las copias de seguridad se renuevan de forma periódica y las antiguas se eliminan.',
                    ],
                ],
                [
                    'title' => 'Tus derechos',
                    'paragraphs' => [
                        'Puedes pedirnos ver, corregir o borrar tus datos, o que dejemos de usarlos, escribiéndonos a '.self::contact().'. Si eres invitado de un evento, también puedes pedírselo al organizador. Respondemos en un plazo razonable y, si no podemos borrar algo (por ejemplo, porque el organizador todavía lo necesita para su evento), te explicamos por qué.',
                        'Estos derechos se apoyan en la Constitución Política del Estado Plurinacional de Bolivia (artículos 21 y 130) y en la Ley N.º 164 General de Telecomunicaciones y Tecnologías de Información y Comunicación.',
                    ],
                ],
                [
                    'title' => 'Seguridad',
                    'paragraphs' => [
                        'El sitio funciona con conexión cifrada (HTTPS), las contraseñas se guardan cifradas y el acceso al panel está limitado a cada organizador. Ningún sistema es infalible: si detectamos un problema que afecte tus datos, te avisaremos.',
                    ],
                ],
                [
                    'title' => 'Menores de edad',
                    'paragraphs' => [
                        'Muchas invitaciones son de cumpleaños, XV años o bautizos. Los datos y las fotos de menores los carga su familia o el organizador adulto del evento, que es quien decide qué se publica.',
                    ],
                ],
                [
                    'title' => 'Cambios',
                    'paragraphs' => [
                        'Si cambiamos esta política, publicamos la nueva versión en esta misma página con su fecha.',
                    ],
                ],
            ],
        ];
    }

    private static function cookies(): array
    {
        $brand = config('bida.brand');

        return [
            'title' => 'Política de cookies',
            'description' => "Qué cookies usa {$brand}, para qué sirve cada una y cómo borrarlas.",
            'intro' => 'Una cookie es un archivo pequeño que el sitio guarda en tu navegador. Usamos pocas y ninguna es de publicidad: estas son todas.',
            'sections' => [
                [
                    'title' => 'Cookies necesarias',
                    'paragraphs' => ['Sin ellas el sitio no funciona; no se pueden desactivar desde aquí.'],
                    'items' => [
                        'Sesión: mantiene abierta tu cuenta mientras navegas por el panel. Dura mientras la sesión esté activa ('.(int) config('session.lifetime', 120).' minutos sin uso).',
                        'Protección de formularios (XSRF-TOKEN): evita que otro sitio envíe formularios en tu nombre, por ejemplo una confirmación de asistencia. Dura lo mismo que la sesión.',
                        'Control de entrada (bida_puerta_…): solo en el teléfono de quien controla la entrada de un evento, después de abrir el enlace de puerta que le compartió el organizador. Permite registrar el ingreso de los invitados y dura 16 horas.',
                    ],
                ],
                [
                    'title' => 'Cookie de campaña',
                    'items' => [
                        'bida_origen: recuerda durante '.LeadSource::DAYS.' días desde qué anuncio o enlace llegaste (Facebook, Instagram, un código QR…). Solo sirve para que, si nos escribes por WhatsApp, sepamos qué campaña funcionó. No te identifica ni se comparte con nadie.',
                    ],
                ],
                [
                    'title' => 'Preferencias guardadas en tu navegador',
                    'items' => [
                        'Tema claro u oscuro del sitio y el aviso de cookies ya leído: se guardan en el almacenamiento local de tu navegador, no viajan a nuestro servidor.',
                    ],
                ],
                [
                    'title' => 'Servicios de terceros',
                    'paragraphs' => [
                        'Las invitaciones pueden mostrar un mapa de Google Maps, videos o música alojados en Cloudinary y tipografías de Google Fonts. Al cargarlos, esos servicios pueden guardar sus propias cookies o registrar tu dirección IP según sus políticas. No usamos herramientas de analítica ni píxeles de publicidad.',
                    ],
                ],
                [
                    'title' => 'Cómo borrarlas o bloquearlas',
                    'paragraphs' => [
                        'Puedes borrar las cookies o bloquearlas desde la configuración de tu navegador. Si bloqueas las necesarias, no podrás entrar al panel ni confirmar asistencia en una invitación.',
                    ],
                ],
            ],
        ];
    }

    private static function terms(): array
    {
        $brand = config('bida.brand');

        return [
            'title' => 'Términos de uso',
            'description' => "Las reglas para usar {$brand}: pedidos, pagos, contenido, suscripciones de profesionales y responsabilidades.",
            'intro' => "Al usar {$brand}, pedir una invitación o suscribirte como profesional, aceptas estas condiciones. Las escribimos para que se entiendan.",
            'sections' => [
                [
                    'title' => 'El servicio',
                    'paragraphs' => [
                        "{$brand} ofrece invitaciones y tarjetas digitales que se comparten con un enlace, con confirmación de asistencia, reportes y otros módulos según el paquete o plan contratado. El servicio lo presta ".self::owner().'.',
                    ],
                ],
                [
                    'title' => 'Pedidos y pagos',
                    'items' => [
                        'Los precios se muestran en bolivianos en este sitio y se confirman por WhatsApp antes de empezar el diseño.',
                        'El pago se coordina por WhatsApp (transferencia o QR). No cobramos automáticamente ni guardamos datos de tarjetas.',
                        'Las promociones valen hasta la fecha que se indica en cada una.',
                    ],
                ],
                [
                    'title' => 'Tu contenido',
                    'items' => [
                        'Eres responsable de las fotos, textos, música y datos que cargas o nos envías, y de tener permiso para usarlos, en especial las fotos de otras personas.',
                        'No se permite contenido ilegal, que ofenda o discrimine, o que use marcas o imágenes de terceros sin autorización. Podemos retirar una invitación que no cumpla estas reglas.',
                        'Nos das permiso para alojar y mostrar ese contenido solo para que la invitación funcione. No lo usamos en nuestra publicidad sin pedirte autorización.',
                    ],
                ],
                [
                    'title' => 'Suscripción para profesionales',
                    'items' => [
                        'Los profesionales (fotógrafos, organizadores, decoradores y otros revendedores) pagan un plan mensual que les permite armar un número de invitaciones por mes, con las plantillas y funciones de su plan.',
                        'Cada pago extiende la suscripción un mes desde la fecha de vencimiento vigente, o desde el día del pago si ya había vencido.',
                        'Si la suscripción vence, las invitaciones ya publicadas siguen en línea y el panel sigue disponible para ver invitados y descargar reportes, pero no se pueden crear ni editar invitaciones hasta renovar.',
                        'El profesional fija libremente lo que cobra a sus clientes y es responsable frente a ellos por el servicio que les ofrece.',
                    ],
                ],
                [
                    'title' => 'Disponibilidad',
                    'paragraphs' => [
                        'Trabajamos para que las invitaciones estén siempre en línea y hacemos copias de seguridad, pero puede haber interrupciones breves por mantenimiento o por fallas de proveedores. Una invitación está disponible hasta su fecha de vencimiento.',
                    ],
                ],
                [
                    'title' => 'Responsabilidad',
                    'paragraphs' => [
                        "{$brand} no responde por la organización del evento, por cambios de fecha o lugar que no se hayan actualizado en la invitación, ni por el uso que cada organizador haga de los datos de sus invitados.",
                    ],
                ],
                [
                    'title' => 'Cambios y contacto',
                    'paragraphs' => [
                        'Podemos actualizar estos términos; la versión vigente es la publicada en esta página con su fecha. Para cualquier consulta, escríbenos a '.self::contact().'.',
                    ],
                ],
            ],
        ];
    }
}
