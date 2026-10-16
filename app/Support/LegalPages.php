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
            ['url' => lroute('legal', 'privacidad'), 'label' => __('Privacidad')],
            ['url' => lroute('legal', 'cookies'), 'label' => __('Cookies')],
            ['url' => lroute('legal', 'terminos'), 'label' => __('Términos')],
        ];
    }

    private static function owner(): string
    {
        $legal = config('bida.legal', []);
        $name = $legal['name'] ?: config('bida.brand');

        return $legal['nit'] ? __(':name (NIT :nit)', ['name' => $name, 'nit' => $legal['nit']]) : $name;
    }

    private static function contact(): string
    {
        return collect([config('bida.email'), '+'.ltrim((string) config('bida.whatsapp'), '+').' (WhatsApp)'])->filter()->implode(' '.__('o al').' ');
    }

    private static function privacy(): array
    {
        $brand = config('bida.brand');
        $photosDays = (int) config('optimizations.retention.photos_days', 180);

        return [
            'title' => __('Política de privacidad'),
            'description' => __('Qué datos guarda :brand, para qué los usa, quién los ve y cómo pedir que se borren.', ['brand' => $brand]),
            'intro' => __('En :brand hacemos invitaciones y tarjetas digitales. Para que funcionen guardamos algunos datos del organizador del evento y de sus invitados. Aquí explicamos cuáles, para qué y qué puedes hacer con ellos.', ['brand' => $brand]),
            'sections' => [
                [
                    'title' => __('Quién es responsable'),
                    'paragraphs' => [
                        __('El responsable de los datos es :value, con domicilio en :city. Puedes escribirnos a :value2.', ['value' => self::owner(), 'city' => config('bida.city'), 'value2' => self::contact()]),
                        __('Cuando un profesional (fotógrafo, organizador de eventos u otro revendedor) arma una invitación con nuestra plataforma, esa persona decide qué datos de sus invitados carga y es responsable de ellos frente a su cliente; nosotros los guardamos y los procesamos por encargo suyo.'),
                    ],
                ],
                [
                    'title' => __('Qué datos guardamos'),
                    'items' => [
                        __('Del organizador o cliente: nombre, usuario y contraseña de acceso (la contraseña se guarda cifrada), teléfono si nos lo da, y el contenido de su invitación: nombres, fechas, lugares, fotos, videos, música y textos.'),
                        __('De los invitados: el nombre y el teléfono que carga el organizador, la respuesta de asistencia, cuántas personas van y, si lo escriben, sus alergias o restricciones alimentarias.'),
                        __('Lo que los invitados comparten por su cuenta: canciones sugeridas, votos en encuestas, respuestas a una tarjeta y fotos subidas al fotomural.'),
                        __('De quienes visitan este sitio: desde qué campaña llegaron (por ejemplo, un anuncio en Facebook) durante 30 días, para saber qué publicidad funciona. No guardamos el nombre ni el teléfono de un visitante hasta que nos escribe.'),
                    ],
                ],
                [
                    'title' => __('Para qué los usamos'),
                    'items' => [
                        __('Mostrar la invitación, recibir las confirmaciones y armar la lista de invitados, el pase con código QR y los reportes en PDF o Excel del organizador.'),
                        __('Dar acceso al panel al organizador y, si corresponde, al profesional que armó la invitación.'),
                        __('Responder consultas y coordinar pedidos y pagos por WhatsApp.'),
                        __('Proteger el servicio: limitar intentos repetidos y guardar registros técnicos para detectar abusos.'),
                    ],
                    'note' => [
                        __('No vendemos datos, no los usamos para publicidad de terceros y no enviamos mensajes automáticos a los invitados: los enlaces los comparte el organizador.'),
                    ],
                ],
                [
                    'title' => __('Quién más los ve'),
                    'items' => [
                        __('El organizador del evento ve las respuestas de sus invitados. Los invitados solo ven la invitación y, en su enlace personal, su propio pase.'),
                        __('Las invitaciones no aparecen en buscadores: solo las abre quien tiene el enlace.'),
                        __('Las fotos, videos y audios se alojan en Cloudinary; los mapas se muestran con Google Maps y las tipografías se cargan desde Google Fonts. Esos servicios reciben la dirección IP de quien abre la invitación, como cualquier sitio que usa sus herramientas.'),
                    ],
                ],
                [
                    'title' => __('Cuánto tiempo los guardamos'),
                    'items' => [
                        __('La invitación y sus respuestas se conservan mientras el organizador la tenga activa y hasta su fecha de vencimiento.'),
                        __('Las fotos que suben los invitados al fotomural se borran :photosDays días después del evento.', ['photosDays' => $photosDays]),
                        __('Los reportes descargables (PDF y Excel) se borran a los pocos días de generarse.'),
                        __('Las copias de seguridad se renuevan de forma periódica y las antiguas se eliminan.'),
                    ],
                ],
                [
                    'title' => __('Tus derechos'),
                    'paragraphs' => [
                        __('Puedes pedirnos ver, corregir o borrar tus datos, o que dejemos de usarlos, escribiéndonos a :value. Si eres invitado de un evento, también puedes pedírselo al organizador. Respondemos en un plazo razonable y, si no podemos borrar algo (por ejemplo, porque el organizador todavía lo necesita para su evento), te explicamos por qué.', ['value' => self::contact()]),
                        __('Estos derechos se apoyan en la Constitución Política del Estado Plurinacional de Bolivia (artículos 21 y 130) y en la Ley N.º 164 General de Telecomunicaciones y Tecnologías de Información y Comunicación.'),
                    ],
                ],
                [
                    'title' => __('Seguridad'),
                    'paragraphs' => [
                        __('El sitio funciona con conexión cifrada (HTTPS), las contraseñas se guardan cifradas y el acceso al panel está limitado a cada organizador. Ningún sistema es infalible: si detectamos un problema que afecte tus datos, te avisaremos.'),
                    ],
                ],
                [
                    'title' => __('Menores de edad'),
                    'paragraphs' => [
                        __('Muchas invitaciones son de cumpleaños, XV años o bautizos. Los datos y las fotos de menores los carga su familia o el organizador adulto del evento, que es quien decide qué se publica.'),
                    ],
                ],
                [
                    'title' => __('Cambios'),
                    'paragraphs' => [
                        __('Si cambiamos esta política, publicamos la nueva versión en esta misma página con su fecha.'),
                    ],
                ],
            ],
        ];
    }

    private static function cookies(): array
    {
        $brand = config('bida.brand');

        return [
            'title' => __('Política de cookies'),
            'description' => __('Qué cookies usa :brand, para qué sirve cada una y cómo borrarlas.', ['brand' => $brand]),
            'intro' => __('Una cookie es un archivo pequeño que el sitio guarda en tu navegador. Usamos pocas y ninguna es de publicidad: estas son todas.'),
            'sections' => [
                [
                    'title' => __('Cookies necesarias'),
                    'paragraphs' => [__('Sin ellas el sitio no funciona; no se pueden desactivar desde aquí.')],
                    'items' => [
                        __('Sesión: mantiene abierta tu cuenta mientras navegas por el panel. Dura mientras la sesión esté activa (:value minutos sin uso).', ['value' => (int) config('session.lifetime', 120)]),
                        __('Protección de formularios (XSRF-TOKEN): evita que otro sitio envíe formularios en tu nombre, por ejemplo una confirmación de asistencia. Dura lo mismo que la sesión.'),
                        __('Control de entrada (bida_puerta_…): solo en el teléfono de quien controla la entrada de un evento, después de abrir el enlace de puerta que le compartió el organizador. Permite registrar el ingreso de los invitados y dura 16 horas.'),
                    ],
                ],
                [
                    'title' => __('Cookie de campaña'),
                    'items' => [
                        __('bida_origen: recuerda durante :value días desde qué anuncio o enlace llegaste (Facebook, Instagram, un código QR…). Solo sirve para que, si nos escribes por WhatsApp, sepamos qué campaña funcionó. No te identifica ni se comparte con nadie.', ['value' => LeadSource::DAYS]),
                    ],
                ],
                [
                    'title' => __('Preferencias guardadas en tu navegador'),
                    'items' => [
                        __('Tema claro u oscuro del sitio y el aviso de cookies ya leído: se guardan en el almacenamiento local de tu navegador, no viajan a nuestro servidor.'),
                    ],
                ],
                [
                    'title' => __('Servicios de terceros'),
                    'paragraphs' => [
                        __('Las invitaciones pueden mostrar un mapa de Google Maps, videos o música alojados en Cloudinary y tipografías de Google Fonts. Al cargarlos, esos servicios pueden guardar sus propias cookies o registrar tu dirección IP según sus políticas. No usamos herramientas de analítica ni píxeles de publicidad.'),
                    ],
                ],
                [
                    'title' => __('Cómo borrarlas o bloquearlas'),
                    'paragraphs' => [
                        __('Puedes borrar las cookies o bloquearlas desde la configuración de tu navegador. Si bloqueas las necesarias, no podrás entrar al panel ni confirmar asistencia en una invitación.'),
                    ],
                ],
            ],
        ];
    }

    private static function terms(): array
    {
        $brand = config('bida.brand');

        return [
            'title' => __('Términos de uso'),
            'description' => __('Las reglas para usar :brand: pedidos, pagos, contenido, suscripciones de profesionales y responsabilidades.', ['brand' => $brand]),
            'intro' => __('Al usar :brand, pedir una invitación o suscribirte como profesional, aceptas estas condiciones. Las escribimos para que se entiendan.', ['brand' => $brand]),
            'sections' => [
                [
                    'title' => __('El servicio'),
                    'paragraphs' => [
                        __(':brand ofrece invitaciones y tarjetas digitales que se comparten con un enlace, con confirmación de asistencia, reportes y otros módulos según el paquete o plan contratado. El servicio lo presta :value.', ['brand' => $brand, 'value' => self::owner()]),
                    ],
                ],
                [
                    'title' => __('Pedidos y pagos'),
                    'items' => [
                        __('Los precios se muestran en dólares estadounidenses en este sitio. Los pagos con QR de Bolivia se cobran en bolivianos, al tipo de cambio oficial.'),
                        __('Una invitación que armas tú se puede publicar gratis con las partes básicas o con un pago único por el nivel de las partes que uses. Subir de nivel después cobra solo la diferencia.'),
                        __('Los pagos en línea se hacen con tarjeta, a través de nuestro procesador de pagos, o con QR de Bolivia. No guardamos los datos de tu tarjeta.'),
                        __('Los paquetes que arma nuestro equipo se confirman y se pagan por WhatsApp antes de empezar el diseño.'),
                        __('Las promociones valen hasta la fecha que se indica en cada una.'),
                    ],
                ],
                [
                    'title' => __('Tu contenido'),
                    'items' => [
                        __('Eres responsable de las fotos, textos, música y datos que cargas o nos envías, y de tener permiso para usarlos, en especial las fotos de otras personas.'),
                        __('No se permite contenido ilegal, que ofenda o discrimine, o que use marcas o imágenes de terceros sin autorización. Podemos retirar una invitación que no cumpla estas reglas.'),
                        __('Nos das permiso para alojar y mostrar ese contenido solo para que la invitación funcione. No lo usamos en nuestra publicidad sin pedirte autorización.'),
                    ],
                ],
                [
                    'title' => __('Suscripción para profesionales'),
                    'items' => [
                        __('Los profesionales (fotógrafos, organizadores, decoradores y otros revendedores) pagan un plan que les permite publicar un número de invitaciones por mes, con las plantillas y funciones de su plan.'),
                        __('El plan se paga por mes o por 3 o 12 meses. Cada pago extiende la suscripción por ese tiempo desde la fecha de vencimiento vigente, o desde el día del pago si ya había vencido. Con tarjeta, la suscripción se renueva sola en el mismo ciclo hasta que la canceles.'),
                        __('Puedes cancelar tu plan cuando quieras desde tu cuenta. Al cancelar no se hacen más cobros y conservas el acceso hasta el final del período ya pagado. No devolvemos meses parciales ni períodos ya pagados.'),
                        __('Si la suscripción vence, las invitaciones ya publicadas siguen en línea y el panel sigue disponible para ver invitados y descargar reportes, pero no se pueden crear ni editar invitaciones hasta renovar.'),
                        __('El profesional fija libremente lo que cobra a sus clientes y es responsable frente a ellos por el servicio que les ofrece.'),
                    ],
                ],
                [
                    'title' => __('Disponibilidad'),
                    'paragraphs' => [
                        __('Trabajamos para que las invitaciones estén siempre en línea y hacemos copias de seguridad, pero puede haber interrupciones breves por mantenimiento o por fallas de proveedores. Una invitación está disponible hasta su fecha de vencimiento.'),
                    ],
                ],
                [
                    'title' => __('Responsabilidad'),
                    'paragraphs' => [
                        __(':brand no responde por la organización del evento, por cambios de fecha o lugar que no se hayan actualizado en la invitación, ni por el uso que cada organizador haga de los datos de sus invitados.', ['brand' => $brand]),
                    ],
                ],
                [
                    'title' => __('Cambios y contacto'),
                    'paragraphs' => [
                        __('Podemos actualizar estos términos; la versión vigente es la publicada en esta página con su fecha. Para cualquier consulta, escríbenos a :value.', ['value' => self::contact()]),
                    ],
                ],
            ],
        ];
    }
}
