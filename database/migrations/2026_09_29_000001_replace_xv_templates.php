<?php

use App\Support\InvitationTemplates;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Las plantillas de XV se rehicieron: «Carta de baile» pasa a ser «Cuento desplegable» y «Galería
 * Quince», «Caleidoscopio» (la tercera, «Noche de gala», se volvió «Atelier» sin cambiar de clave).
 * Las invitaciones que las usaban pasan al diseño nuevo con todo su contenido, y las dos muestras
 * toman el nombre de la plantilla nueva (el seeder de muestras las actualiza por su slug).
 */
return new class extends Migration
{
    /** Muestras de las plantillas anteriores → las de las nuevas. */
    private const DEMOS = [
        'xv-isabella-carta' => 'xv-isabella-cuento',
        'xv-isabella-galeria' => 'xv-isabella-caleidoscopio',
    ];

    public function up(): void
    {
        foreach (InvitationTemplates::RENAMED as $old => $new) {
            DB::table('invitations')->where('template', $old)->update(['template' => $new]);
        }

        foreach (self::DEMOS as $old => $new) {
            if (DB::table('invitations')->where('slug', $new)->doesntExist()) {
                DB::table('invitations')->where('slug', $old)->whereNull('user_id')->update(['slug' => $new]);
            }
        }
    }

    public function down(): void
    {
        foreach (InvitationTemplates::RENAMED as $old => $new) {
            DB::table('invitations')->where('template', $new)->update(['template' => $old]);
        }

        foreach (self::DEMOS as $old => $new) {
            if (DB::table('invitations')->where('slug', $old)->doesntExist()) {
                DB::table('invitations')->where('slug', $new)->whereNull('user_id')->update(['slug' => $old]);
            }
        }
    }
};
