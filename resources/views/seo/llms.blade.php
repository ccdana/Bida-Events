{{-- llms.txt en Markdown (ver SeoController::llms). Texto plano servido como text/markdown con nosniff: se imprime sin escapar HTML para que «&» no salga como «&amp;». --}}
# {!! $bida['brand'] !!}

> {!! $bida['brand'] !!} crea invitaciones digitales: una página web por evento que se comparte por WhatsApp, con portada animada, ubicación con mapa, itinerario, música, fotos, confirmación de asistencia por invitado, pase con código QR y control de entrada el día del evento. Atiende en Bolivia y en toda Latinoamérica, en español. Precios en dólares estadounidenses ({!! $currency !!}).

## Qué ofrece

- «Hazlo tú»: cualquiera crea su cuenta y arma su propia invitación con las plantillas, gratis. Se publica gratis con la portada, la cuenta regresiva y la ubicación, o con un pago único por el nivel de lo que usó (Básico, Estándar o Premium).
- A medida: para algo distinto se escribe por WhatsApp o al correo y se conversa; no tiene precio publicado.
- Planes («Únete»): por mes, 3 o 12 meses, para fotógrafos, salones y organizadores que arman invitaciones cada mes, sin comisión por invitación.
- Tarjetas digitales de temporada (Día del Amor, Halloween) listas para compartir.
- Nivel Estándar: cada invitado recibe un enlace personal y confirma por WhatsApp con un mensaje que ya lleva su nombre y cuántas personas van.
- Nivel Premium: la confirmación queda guardada en el panel del organizador y el invitado recibe un pase con código QR.
- Control de entrada (Premium): quien recibe a los invitados escanea el pase con la cámara de su teléfono; el sistema dice si puede pasar, cuántas personas entran y no deja usar el mismo pase dos veces.
- Reportes de invitados en PDF y Excel, y una versión de la invitación para imprimir (Premium).
- Las invitaciones no aparecen en buscadores: solo las abre quien tiene el enlace.

## Hazlo tú (la arma la persona; pago único al publicarla)

- Gratis: portada, cuenta regresiva y ubicación, con la marca de {!! $bida['brand'] !!} a la vista.
@foreach(\App\Payments\InvitationPrice::tierPrices() as $tier => $price)
- {!! \App\Support\Packages::tierNames()[$tier] ?? $tier !!}: {!! \App\Support\Money::format($price['final']) !!}.
@endforeach

## Únete (planes mensuales)

@foreach($plans as $plan)
- Plan {!! $plan['name'] !!}: {!! \App\Support\Money::format($plan['final_price']) !!} al mes, {!! $plan['quota_per_month'] === null ? 'invitaciones sin tope' : ($plan['quota_per_month'] === 1 ? '1 invitación al mes' : $plan['quota_per_month'].' invitaciones al mes') !!}{!! ! empty($plan['white_label']) ? ', con la marca del cliente al pie' : '' !!}.
@endforeach

## Páginas

- [Inicio]({!! lroute('home') !!}): qué es, cómo funciona, muestras que se pueden probar y precios.
- [Precios]({!! lroute('pricing') !!}): Hazlo tú y los planes, lado a lado, con lo que trae cada nivel.
- [Guía de invitaciones digitales]({!! lroute('guide') !!}): qué son, cuánto cuestan, cómo elegir una y cómo funciona el control de entrada con QR.
- [Únete]({!! lroute('diy') !!}): planes mensuales para crear invitaciones propias.
@foreach($landings as $landing)
- [{!! $landing['label'] !!}]({!! $landing['url'] !!})
@endforeach

## Contacto

- WhatsApp: +{!! ltrim((string) $bida['whatsapp'], '+') !!}
- Correo: {!! $bida['email'] !!}
- [Privacidad]({!! lroute('legal', 'privacidad') !!}) · [Términos]({!! lroute('legal', 'terminos') !!})
