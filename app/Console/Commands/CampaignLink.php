<?php

namespace App\Console\Commands;

use App\Support\LeadSource;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

/**
 * Arma el enlace de una campaña con sus parámetros UTM y muestra el código que va a llegar
 * en el mensaje de WhatsApp, para reconocer ese contacto al responder.
 *
 *   php artisan bida:enlace-campana invitaciones-de-boda --fuente=facebook --medio=anuncio --campana=mayo
 */
class CampaignLink extends Command
{
    protected $signature = 'bida:enlace-campana
                            {pagina=inicio : "inicio" o una página por evento (invitaciones-de-boda, invitaciones-xv-anos…)}
                            {--fuente= : Dónde se publica: facebook, instagram, tiktok, google, qr…}
                            {--medio= : Tipo de publicación: anuncio, historia, perfil, impreso…}
                            {--campana= : Nombre corto de la campaña, por ejemplo mayo o feria}';

    protected $description = 'Genera un enlace con UTM y el código que llegará por WhatsApp';

    public function handle(): int
    {
        $page = (string) $this->argument('pagina');
        $landings = config('bida.landings', []);

        if ($page !== 'inicio' && ! isset($landings[$page])) {
            $this->components->error("No existe la página «{$page}». Usa: inicio, ".implode(', ', array_keys($landings)));

            return self::FAILURE;
        }

        $source = (string) $this->option('fuente');

        if ($source === '') {
            $this->components->error('Indica al menos --fuente (por ejemplo --fuente=facebook).');

            return self::FAILURE;
        }

        $query = array_filter([
            'utm_source' => $source,
            'utm_medium' => $this->option('medio'),
            'utm_campaign' => $this->option('campana'),
        ]);

        $url = ($page === 'inicio' ? route('home') : route('landing', $page)).'?'.http_build_query($query);
        $code = LeadSource::code(Request::create($url), $page === 'inicio' ? LeadSource::HOME : $landings[$page]['code']);

        $this->components->twoColumnDetail('Enlace', $url);
        $this->components->twoColumnDetail('Código en WhatsApp', "Ref. {$code}");

        return self::SUCCESS;
    }
}
