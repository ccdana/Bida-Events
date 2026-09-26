<?php

namespace Database\Seeders;

use App\Support\InvitationTemplates;
use Illuminate\Support\Arr;

/**
 * Una muestra armada sobre otra: toma los datos de una invitación de muestra (database/seeders/showcase)
 * y le cambia la plantilla, los colores, las letras y lo que haga falta. Las plantillas nuevas de un
 * evento se enseñan con el mismo contenido que la clásica, para que se note que solo cambia el diseño.
 *
 * Los invitados y los votos llevan claves únicas: se derivan de las originales con el slug nuevo.
 */
final class ShowcaseVariant
{
    /**
     * @param  array<string, mixed>  $changes  claves con puntos (modules.bienvenida.mensaje => …) que se reemplazan
     */
    public static function of(string $base, string $slug, string $title, string $template, array $changes = []): array
    {
        $data = ShowcaseInvitationsSeeder::data($base);
        $palette = InvitationTemplates::palette($template);
        $fonts = InvitationTemplates::get($template)['fonts'] ?? null;

        $data['invitation']['slug'] = $slug;
        $data['invitation']['title'] = $title;
        $data['invitation']['template'] = $template;
        $data['modules']['config']['template'] = $template;
        $data['modules']['config']['colores'] = $palette;

        if ($fonts) {
            $data['modules']['config']['tipografias'] = $fonts;
        }

        foreach ($changes as $key => $value) {
            Arr::set($data, $key, $value);
        }

        $key = fn (?string $original) => $original === null ? null : substr(hash('sha256', $slug.'|'.$original), 0, strlen($original));

        $data['guests'] = array_map(fn (array $guest) => ['qr_code_token' => $key($guest['qr_code_token'])] + $guest, $data['guests']);
        $data['contributions'] = array_map(fn (array $item) => ['guest' => $key($item['guest'])] + $item, $data['contributions']);
        $data['poll_votes'] = array_map(fn (array $vote) => ['guest' => $key($vote['guest']), 'voter_key' => $key($vote['voter_key'])] + $vote, $data['poll_votes']);

        return $data;
    }
}
