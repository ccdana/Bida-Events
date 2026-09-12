<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Las vistas de resources/views/pages se consolidaron: 'pages.invitations.templates.x' pasa a 'invitations.templates.x'.
     * Los valores antiguos que queden en JSON o en sesión se resuelven con InvitationDefaults::resolveTemplate().
     */
    public function up(): void
    {
        foreach (['invitations', 'invitation_settings'] as $table) {
            DB::table($table)
                ->where('template', 'like', 'pages.%')
                ->orderBy('id')
                ->get(['id', 'template'])
                ->each(fn ($row) => DB::table($table)
                    ->where('id', $row->id)
                    ->update(['template' => substr($row->template, strlen('pages.'))]));
        }
    }

    public function down(): void
    {
        // Sin reversión: los nombres antiguos siguen resolviéndose en tiempo de ejecución.
    }
};
