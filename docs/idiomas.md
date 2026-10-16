# Idiomas: español e inglés

El sitio público, las cuentas y las invitaciones que ven los invitados existen en español
(en la raíz) y en inglés (bajo `/en`). Para cualquier otro idioma el invitado usa el traductor
de su navegador. Los paneles, el editor y los PDF siguen en español.

## Cómo funciona

| Pieza | Qué hace |
| --- | --- |
| `config/localization.php` | Idiomas, prefijo de URL, direcciones por idioma, formatos de fecha y números |
| `App\Support\Localization` | Idioma activo, direcciones del otro idioma (hreflang, selector), fechas y números |
| `SetLocale` | Idioma del sitio según el prefijo de la dirección |
| `SetInvitationLocale` | Idioma de la invitación: el campo `locale` que eligió quien la creó |
| `lang/en.json` | Traducciones: la clave es el texto en español, el valor, su inglés |
| `lroute()` | Dirección de una ruta del sitio en el idioma activo |

Las invitaciones guardan su idioma en `invitations.locale`; las cuentas, en `users.locale`
(los correos salen en el idioma en que se abrió la cuenta).

## Agregar o cambiar un texto

1. Escribe el texto en español con `__('Texto')` (vistas, controladores, correos).
   Los textos con datos llevan marcadores: `__('Hola, :name', ['name' => $name])`.
2. Agrega la clave a `lang/en.json` con su traducción, conservando los marcadores (`:name`, `{brand}`).
3. Comprueba que no falte ninguno:

```bash
php artisan bida:traducciones            # resumen: sale con error si falta alguno
php artisan bida:traducciones --faltan   # lista los que faltan
php artisan bida:traducciones --sobran   # lista los que ya nadie usa
```

Los textos que viven en la configuración (`config/bida.php`: planes, paquetes, FAQ…) y en las
plantillas de invitación se traducen solos al mostrarse; solo hay que tener su clave en
`lang/en.json`. Las pruebas (`LocalizationTest`) fallan si falta una traducción, si una
traducción pierde un marcador o si una página en inglés muestra texto en español.

## Agregar una página pública

Las rutas del sitio se registran por idioma en `routes/web.php` (`$siteRoutes`). Una página
nueva necesita su dirección en inglés en `config/localization.php` (`paths.en`, o `landings.en`
si es una landing por evento) y se enlaza con `lroute('nombre')`, no con `route()`.

## Lo que no está traducido

- Paneles de administrador y de cliente, editor de invitaciones y PDF/Excel.
- El contenido que escribe quien crea la invitación (nombres, textos propios): se muestra tal cual.
- Las invitaciones de muestra de la portada están en español.
