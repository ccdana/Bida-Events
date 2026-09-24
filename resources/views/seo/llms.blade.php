{{-- llms.txt en Markdown (ver SeoController::llms). Texto plano servido como text/markdown con nosniff: se imprime sin escapar HTML para que «&» no salga como «&amp;». --}}
# {!! $bida['brand'] !!}

> {!! $bida['brand'] !!} crea invitaciones digitales: una página web por evento que se comparte por WhatsApp, con portada animada, ubicación con mapa, itinerario, música, fotos, confirmación de asistencia por invitado, pase con código QR y control de entrada el día del evento. Atiende en Bolivia y en toda Latinoamérica, en español. Precios en dólares estadounidenses ({!! $currency !!}).

## Qué ofrece

- Invitaciones digitales para bodas, XV años, bautizos, cumpleaños y graduaciones, armadas por el equipo de {!! $bida['brand'] !!} con las fotos y los datos del evento.
- Tarjetas digitales de temporada (Día del Amor, Halloween) listas para compartir.
- «Hazlo tú»: un plan mensual con panel propio para que familias, fotógrafos, salones y organizadores armen sus propias invitaciones, sin comisión por invitación.
- Paquete Estándar: cada invitado recibe un enlace personal y confirma por WhatsApp con un mensaje que ya lleva su nombre y cuántas personas van.
- Paquete Premium: la confirmación queda guardada en el panel del organizador y el invitado recibe un pase con código QR.
- Control de entrada (Premium): quien recibe a los invitados escanea el pase con la cámara de su teléfono; el sistema dice si puede pasar, cuántas personas entran y no deja usar el mismo pase dos veces.
- Reportes de invitados en PDF y Excel, y una versión de la invitación para imprimir (Premium).
- Las invitaciones no aparecen en buscadores: solo las abre quien tiene el enlace.

## Precios de hoy (pago único por invitación)

@foreach($packages as $package)
- Paquete {!! $package['name'] !!}: {!! \App\Support\Money::format($package['final_price']) !!}@if($package['old_price']) (precio normal {!! \App\Support\Money::format($package['old_price']) !!})@endif. {!! $package['summary'] ?? '' !!}
@endforeach

## Hazlo tú (planes mensuales)

@foreach($plans as $plan)
- Plan {!! $plan['name'] !!}: {!! \App\Support\Money::format($plan['final_price']) !!} al mes, {!! $plan['quota_per_month'] === null ? 'invitaciones sin tope' : $plan['quota_per_month'].' invitaciones al mes' !!}{!! ! empty($plan['white_label']) ? ', con la marca del cliente al pie' : '' !!}.
@endforeach

## Páginas

- [Inicio]({!! route('home') !!}): qué es, cómo funciona, muestras que se pueden probar y precios.
- [Guía de invitaciones digitales]({!! route('guide') !!}): qué son, cuánto cuestan, cómo elegir una y cómo funciona el control de entrada con QR.
- [Hazlo tú]({!! route('diy') !!}): planes mensuales para crear invitaciones propias.
@foreach($landings as $landing)
- [{!! $landing['label'] !!}]({!! $landing['url'] !!})
@endforeach

## Contacto

- WhatsApp: +{!! ltrim((string) $bida['whatsapp'], '+') !!}
- Correo: {!! $bida['email'] !!}
- [Privacidad]({!! route('legal', 'privacidad') !!}) · [Términos]({!! route('legal', 'terminos') !!})
