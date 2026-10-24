{{--
    Pie del sitio público. A la izquierda, la marca con una frase en serif y una fila de íconos para escribirnos
    (WhatsApp, correo y redes); al lado, los enlaces ordenados por lo que busca cada visitante (invitaciones por
    evento, lo demás que ofrecemos y lo legal) y, abajo, la línea de derechos. Sin tarjetas ni sombras: solo filetes
    y el dorado como único acento. Recibe $landings, $contactUrl, $accountUrl, $accountLabel, $bida y, opcional,
    $socials. Estilos: home.css («Pie»).
--}}
@php
    $footerSocials = $socials ?? [];
    if ($footerSocials === []) {
        $footerSocials = array_values(array_filter([
            ! empty($bida['instagram']) ? ['label' => 'Instagram', 'url' => 'https://www.instagram.com/'.$bida['instagram'].'/', 'icon' => 'instagram-logo'] : null,
            ! empty($bida['facebook']) ? ['label' => 'Facebook', 'url' => 'https://www.facebook.com/'.$bida['facebook'], 'icon' => 'facebook-logo'] : null,
            ! empty($bida['tiktok']) ? ['label' => 'TikTok', 'url' => 'https://www.tiktok.com/@'.$bida['tiktok'], 'icon' => 'tiktok-logo'] : null,
        ]));
    }
    $eventLandings = collect($landings ?? [])->reject(fn (array $landing) => in_array(config("bida.landings.{$landing['slug']}.kind"), ['card', 'season'], true));
@endphp
<footer class="site-foot">
    <div class="site-foot__inner">
        <div class="site-foot__brand">
            <a href="{{ lroute('home') }}" class="site-foot__logo" aria-label="{{ __(':brand, inicio', ['brand' => $bida['brand']]) }}">
                <x-brand.logo />
            </a>
            <p class="site-foot__statement">{{ __('Cada celebración merece una invitación') }} <em>{{ __('a su altura.') }}</em></p>
            <ul class="site-foot__social" aria-label="{{ __('Escríbenos y síguenos') }}">
                <li>
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-icon-btn site-icon-btn--whatsapp" aria-label="{{ __('Escríbenos por WhatsApp') }}" title="{{ __('Escríbenos por WhatsApp') }}">
                        <x-phosphor-whatsapp-logo aria-hidden="true" />
                    </a>
                </li>
                @if(! empty($bida['email']))
                    <li>
                        <a href="mailto:{{ $bida['email'] }}" class="site-icon-btn" aria-label="{{ __('Escribir al correo :email', ['email' => $bida['email']]) }}" title="{{ $bida['email'] }}">
                            <x-phosphor-envelope-simple aria-hidden="true" />
                        </a>
                    </li>
                @endif
                @foreach($footerSocials as $social)
                    <li>
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="site-icon-btn" aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}">
                            <x-dynamic-component :component="'phosphor-'.$social['icon'].'-light'" aria-hidden="true" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="site-foot__cols">
            <nav class="site-foot__col" aria-label="{{ __('Invitaciones por evento') }}">
                <p class="site-foot__title">{{ __('Invitaciones') }}</p>
                <ul>
                    @foreach($eventLandings as $landing)
                        <li><a href="{{ $landing['url'] }}">{{ $landing['name'] ?? $landing['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <nav class="site-foot__col" aria-label="{{ __('Más de :brand', ['brand' => $bida['brand']]) }}">
                <p class="site-foot__title">{{ __('También') }}</p>
                <ul>
                    <li><a href="{{ lroute('pricing') }}">{{ __('Precios') }}</a></li>
                    <li><a href="{{ lroute('diy') }}">{{ __('Planes') }}</a></li>
                    <li><a href="{{ lroute('guide') }}">{{ __('Guía de invitaciones') }}</a></li>
                    <li><a href="{{ $accountUrl }}">{{ $accountLabel }}</a></li>
                </ul>
            </nav>

        </div>
    </div>

    <div class="site-foot__bar">
        <p>© {{ now()->year }} {{ $bida['brand'] }}@if(! empty($bida['city'])) <span aria-hidden="true">·</span> {{ $bida['city'] }}@endif</p>
        <nav class="site-foot__legal" aria-label="{{ __('Información legal') }}">
            @foreach(\App\Support\LegalPages::links() as $legalLink)
                <a href="{{ $legalLink['url'] }}">{{ $legalLink['label'] }}</a>
            @endforeach
        </nav>
        <x-site.language-switch touch />
    </div>
</footer>
