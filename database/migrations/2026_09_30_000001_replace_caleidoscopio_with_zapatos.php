<?php

use App\Support\TrendTemplates;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * «Caleidoscopio» se reemplaza por «El cambio de zapatos». Las invitaciones que la usaban pasan al
 * diseño nuevo con todo su contenido y la muestra toma el nombre de la plantilla nueva (el seeder de
 * muestras la actualiza por su slug).
 *
 * Los colores y las letras que el cliente eligió se respetan. Las que seguían con los de
 * «Caleidoscopio» tal cual (la noche del visor: fondo oscuro y texto claro, que eran su identidad y no
 * una elección del cliente) pasan a los de «El cambio de zapatos».
 */
return new class extends Migration
{
    private const OLD = 'invitations.templates.xv-caleidoscopio';

    private const NEW = 'invitations.templates.xv-zapatos';

    private const OLD_DEMO = 'xv-isabella-caleidoscopio';

    private const NEW_DEMO = 'xv-isabella-zapatos';

    /** Con lo que nacía una invitación de «Caleidoscopio». */
    private const OLD_THEME = [
        'color_primary' => '#E0457B',
        'color_secondary' => '#2EC4B6',
        'color_accent' => '#FFB84D',
        'color_text' => '#F5F1FF',
        'color_background' => '#14121F',
    ];

    private const OLD_FONTS = [
        'font_titles' => 'Bricolage Grotesque',
        'font_body' => 'Outfit',
        'font_script' => 'Instrument Serif',
    ];

    public function up(): void
    {
        $ids = DB::table('invitations')->where('template', self::OLD)->pluck('id');

        DB::table('invitations')->whereIn('id', $ids)->update(['template' => self::NEW]);

        $shoes = TrendTemplates::themes()['zapatos'];

        foreach (DB::table('invitation_themes')->whereIn('invitation_id', $ids)->get() as $theme) {
            $changes = [];

            if ($this->matches($theme, self::OLD_THEME)) {
                $changes += [
                    'color_primary' => $shoes['palette']['primary'],
                    'color_secondary' => $shoes['palette']['secondary'],
                    'color_accent' => $shoes['palette']['accent'],
                    'color_text' => $shoes['palette']['text'],
                    'color_background' => $shoes['palette']['background'],
                ];
            }

            if ($this->matches($theme, self::OLD_FONTS)) {
                $changes += [
                    'font_titles' => $shoes['fonts']['titulos'],
                    'font_body' => $shoes['fonts']['cuerpo'],
                    'font_script' => $shoes['fonts']['script'],
                ];
            }

            if ($changes) {
                DB::table('invitation_themes')->where('id', $theme->id)->update($changes + ['updated_at' => now()]);
            }
        }

        if (DB::table('invitations')->where('slug', self::NEW_DEMO)->doesntExist()) {
            DB::table('invitations')->where('slug', self::OLD_DEMO)->whereNull('user_id')->update(['slug' => self::NEW_DEMO]);
        }
    }

    public function down(): void
    {
        DB::table('invitations')->where('template', self::NEW)->update(['template' => self::OLD]);

        if (DB::table('invitations')->where('slug', self::OLD_DEMO)->doesntExist()) {
            DB::table('invitations')->where('slug', self::NEW_DEMO)->whereNull('user_id')->update(['slug' => self::OLD_DEMO]);
        }
    }

    /** ¿El tema sigue exactamente con estos valores? (los colores, sin importar mayúsculas) */
    private function matches(object $theme, array $values): bool
    {
        foreach ($values as $column => $value) {
            if (strtolower(trim((string) ($theme->{$column} ?? ''))) !== strtolower($value)) {
                return false;
            }
        }

        return true;
    }
};
