{{--
    Pie del sitio público. A la izquierda, la marca con una frase en serif, cómo escribirnos y las
    redes; al lado, los enlaces ordenados por lo que busca cada visitante (invitaciones por evento,
    lo demás que ofrecemos y lo legal) y, abajo, la línea de derechos. Sin tarjetas ni sombras:
    solo filetes y el dorado como único acento. Recibe $landings, $contactUrl, $accountUrl,
    $accountLabel, $bida y, opcional, $socials. Estilos: home.css («Pie»).
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
            <a href="{{ route('home') }}" class="site-foot__logo" aria-label="{{ $bida['brand'] }}, inicio">
                <x-brand.logo />
            </a>
            <p class="site-foot__statement">Cada celebración merece una invitación <em>a su altura.</em></p>
            <p class="site-foot__pitch">Invitaciones digitales con confirmación de asistencia, pase QR y control de entrada.</p>
            <div class="site-foot__contact">
                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-btn">
                    <x-phosphor-whatsapp-logo aria-hidden="true" />
                    Escríbenos por WhatsApp
                </a>
                @if(! empty($bida['email']))
                    <a href="mailto:{{ $bida['email'] }}" class="site-foot__mail">{{ $bida['email'] }}</a>
                @endif
            </div>
            <ul class="site-foot__social" aria-label="Redes sociales">
                @foreach($footerSocials as $social)
                    <li>
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="site-social" aria-label="{{ $social['label'] }}">
                            <x-dynamic-component :component="'phosphor-'.$social['icon'].'-light'" class="size-5" aria-hidden="true" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="site-foot__cols">
            <nav class="site-foot__col" aria-label="Invitaciones por evento">
                <p class="site-foot__title">Invitaciones</p>
                <ul>
                    @foreach($eventLandings as $landing)
                        <li><a href="{{ $landing['url'] }}">{{ $landing['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <nav class="site-foot__col" aria-label="Más de {{ $bida['brand'] }}">
                <p class="site-foot__title">También</p>
                <ul>
                    <li><a href="{{ route('pricing') }}">Precios</a></li>
                    <li><a href="{{ route('diy') }}">Planes para profesionales</a></li>
                    <li><a href="{{ route('guide') }}">Guía de invitaciones</a></li>
                    <li><a href="{{ $accountUrl }}">{{ $accountLabel }}</a></li>
                </ul>
            </nav>

            <nav class="site-foot__col" aria-label="Información legal">
                <p class="site-foot__title">Legal</p>
                <ul>
                    @foreach(\App\Support\LegalPages::links() as $legalLink)
                        <li><a href="{{ $legalLink['url'] }}">{{ $legalLink['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </div>

    <div class="site-foot__bar">
        <p>© {{ now()->year }} {{ $bida['brand'] }}@if(! empty($bida['city'])) <span aria-hidden="true">·</span> {{ $bida['city'] }}@endif</p>
    </div>
</footer>
