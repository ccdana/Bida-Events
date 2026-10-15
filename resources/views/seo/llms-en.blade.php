{{-- llms.txt in English (see SeoController::llms; the Spanish version is seo/llms). Plain text served as text/markdown with nosniff: printed unescaped so "&" does not come out as "&amp;". --}}
# {!! $bida['brand'] !!}

> {!! $bida['brand'] !!} creates digital invitations: one web page per event that is shared over WhatsApp, with an animated cover, a map location, a schedule, music, photos, per-guest RSVP, a QR-code pass and door check-in on the day of the event. It serves Bolivia and all of Latin America, in Spanish and English. Prices in US dollars ({!! $currency !!}).

## What it offers

- "Do it yourself" («Hazlo tú»): anyone creates an account and builds their own invitation with the templates, for free. It is published free with the cover, the countdown and the location, or with a one-time payment for the level of what they used (Basic, Standard or Premium).
- "We make it for you" («Te la hacemos»): invitations for weddings, quinceañeras, baptisms, birthdays and graduations built by the {!! $bida['brand'] !!} team with the event's photos and details, with a delivery time and rounds of changes.
- Plans («Únete», "Join"): monthly, 3 or 12 months, for photographers, venues and organizers who build invitations every month, with no commission per invitation.
- Seasonal digital cards (Love Day, Halloween) ready to share.
- Standard package: each guest gets a personal link and confirms over WhatsApp with a message that already carries their name and how many people are coming.
- Premium package: the RSVP is saved in the organizer's dashboard and the guest receives a pass with a QR code.
- Door check-in (Premium): whoever welcomes the guests scans the pass with their phone camera; the system says whether the guest may enter, how many people come in and does not let the same pass be used twice.
- Guest reports in PDF and Excel, and a printable version of the invitation (Premium).
- Invitations do not appear in search engines: only people who have the link can open them.

## Do it yourself (the person builds it; one-time payment when publishing)

- Free: cover, countdown and location, with the {!! $bida['brand'] !!} mark visible.
@foreach(\App\Payments\InvitationPrice::tierPrices() as $tier => $price)
- {!! __(\App\Support\Packages::tierNames()[$tier] ?? $tier) !!}: {!! \App\Support\Money::format($price['final']) !!}, the same parts as the package of the same name.
@endforeach

## We make it for you (one-time payment per invitation)

@foreach($packages as $package)
- {!! $package['name'] !!} package: {!! \App\Support\Money::format($package['final_price']) !!}@if($package['old_price']) (regular price {!! \App\Support\Money::format($package['old_price']) !!})@endif. {!! $package['summary'] ?? '' !!}
@endforeach

## Join (monthly plans)

@foreach($plans as $plan)
- {!! $plan['name'] !!} plan: {!! \App\Support\Money::format($plan['final_price']) !!} per month, {!! $plan['quota_per_month'] === null ? 'unlimited invitations' : ($plan['quota_per_month'] === 1 ? '1 invitation per month' : $plan['quota_per_month'].' invitations per month') !!}{!! ! empty($plan['white_label']) ? ', with the client\'s brand in the footer' : '' !!}.
@endforeach

## Pages

- [Home]({!! lroute('home') !!}): what it is, how it works, samples you can try and prices.
- [Pricing]({!! lroute('pricing') !!}): Do it yourself, the plans and We make it for you, side by side, with what each level includes.
- [Digital invitations guide]({!! lroute('guide') !!}): what they are, how much they cost, how to choose one and how QR door check-in works.
- [Join]({!! lroute('diy') !!}): monthly plans to create your own invitations.
@foreach($landings as $landing)
- [{!! $landing['label'] !!}]({!! $landing['url'] !!})
@endforeach

## Contact

- WhatsApp: +{!! ltrim((string) $bida['whatsapp'], '+') !!}
- Email: {!! $bida['email'] !!}
- [Privacy]({!! lroute('legal', 'privacidad') !!}) · [Terms]({!! lroute('legal', 'terminos') !!})
