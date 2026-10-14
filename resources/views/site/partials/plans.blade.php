{{--
    Precios de los paquetes (pago único por invitación). Recibe $packages (con su enlace de WhatsApp)
    y $contactUrl. Con la promoción encendida, el precio normal va tachado junto al de hoy. La columna
    es la misma que la de los planes mensuales de «Únete» (components/site/price-card).
--}}
@use('App\Support\Money')

<section id="precios" class="scroll-mt-20 border-t border-site-line bg-site-surface" aria-labelledby="precios-titulo">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
        <div class="max-w-3xl">
            <h2 id="precios-titulo" class="site-display site-display--md" data-reveal>{{ $plansTitle ?? 'Elige tu paquete' }}</h2>
            <p class="site-muted-lead" data-reveal>
                {{ $plansLead ?? 'Pago único por invitación, en dólares ('.Money::code().'). Cada paquete incluye todo lo del anterior.' }}
            </p>
        </div>

        <div class="site-plans mt-14">
            @foreach($packages as $index => $package)
                @php($featured = (bool) ($package['featured'] ?? false))
                @php($premium = (bool) ($package['premium'] ?? false))
                <x-site.price-card
                    :name="$package['name']"
                    :price="$package['final_price'] ?? $package['price']"
                    :old="! empty($package['old_price']) ? Money::format($package['old_price']) : null"
                    :suffix="Money::code()"
                    :saving="! empty($package['old_price']) ? 'Ahorras '.Money::format($package['old_price'] - $package['final_price']) : null"
                    :summary="$package['summary']"
                    :features="$package['features']"
                    :featured="$featured"
                    :premium="$premium"
                    :tag="$featured ? 'Recomendado' : ($premium ? 'Experiencia completa' : null)"
                    :url="$package['whatsapp']"
                    :external="true"
                    :cta="'Elegir '.$package['name']"
                    :index="$index" />
            @endforeach
        </div>

        <p class="mt-10 text-site-muted" data-reveal>
            ¿Buscas algo distinto?
            <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="font-medium text-site-ink underline underline-offset-4">Escríbenos</a>
            y armamos un paquete para tu evento.
        </p>
    </div>
</section>
