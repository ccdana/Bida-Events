<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La confirmación de asistencia pasa a ser dos módulos: con pase QR (rsvp) y por WhatsApp
 * (rsvp_whatsapp). Hasta hoy, una invitación del paquete Estándar con «rsvp» encendido confirmaba
 * por WhatsApp: se le enciende el módulo nuevo y se apaga el de pase, para que siga igual.
 * El número ya está guardado en invitation_rsvp_settings.whatsapp, que usa el módulo nuevo.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rsvp = DB::table('features')->where('code', 'rsvp')->value('id');

        if (! $rsvp) {
            return;
        }

        $whatsapp = DB::table('features')->where('code', 'rsvp_whatsapp')->value('id')
            ?? DB::table('features')->insertGetId(['code' => 'rsvp_whatsapp', 'name' => 'Confirmación por WhatsApp']);

        $invitations = DB::table('invitation_features')
            ->join('invitations', 'invitations.id', '=', 'invitation_features.invitation_id')
            ->where('invitation_features.feature_id', $rsvp)
            ->where('invitation_features.is_enabled', true)
            ->where('invitations.package', 'estandar')
            ->pluck('invitations.id');

        foreach ($invitations as $invitationId) {
            DB::table('invitation_features')->updateOrInsert(
                ['invitation_id' => $invitationId, 'feature_id' => $whatsapp],
                ['is_enabled' => true],
            );

            DB::table('invitation_features')
                ->where('invitation_id', $invitationId)
                ->where('feature_id', $rsvp)
                ->update(['is_enabled' => false]);
        }
    }

    public function down(): void
    {
        $rsvp = DB::table('features')->where('code', 'rsvp')->value('id');
        $whatsapp = DB::table('features')->where('code', 'rsvp_whatsapp')->value('id');

        if (! $rsvp || ! $whatsapp) {
            return;
        }

        $invitations = DB::table('invitation_features')
            ->where('feature_id', $whatsapp)
            ->where('is_enabled', true)
            ->pluck('invitation_id');

        foreach ($invitations as $invitationId) {
            DB::table('invitation_features')->updateOrInsert(
                ['invitation_id' => $invitationId, 'feature_id' => $rsvp],
                ['is_enabled' => true],
            );
        }

        DB::table('invitation_features')->where('feature_id', $whatsapp)->delete();
    }
};
