<?php

namespace App\Policies;

use App\EventProfiles\EventProfiles;
use App\Models\Invitation;
use App\Models\User;
use App\Modules\Module;

class InvitationPolicy
{
    /**
     * Los administradores gestionan todas las invitaciones.
     */
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /** La ve su cliente y, si la armó un revendedor, también ese revendedor. */
    public function view(User $user, Invitation $invitation): bool
    {
        return (int) $invitation->user_id === (int) $user->id
            || ($invitation->reseller_id !== null && (int) $invitation->reseller_id === (int) $user->id);
    }

    public function export(User $user, Invitation $invitation): bool
    {
        return $this->view($user, $invitation);
    }

    /**
     * Crear invitaciones propias: solo un revendedor con la suscripción al día (el cupo del mes se
     * revisa al guardar, en Client\InvitationController, para poder explicar por qué no se puede).
     */
    public function create(User $user): bool
    {
        return $user->isReseller() && $user->hasActiveSubscription();
    }

    /**
     * Editar es del administrador (pasa por before) y del revendedor que armó la invitación, mientras
     * su suscripción esté al día. Un cliente nunca edita: se la arma el equipo o su revendedor.
     */
    public function update(User $user, Invitation $invitation): bool
    {
        return $invitation->reseller_id !== null
            && (int) $invitation->reseller_id === (int) $user->id
            && $user->isReseller()
            && $user->hasActiveSubscription();
    }

    public function manageGuests(User $user, Invitation $invitation): bool
    {
        return false;
    }

    /** Borrar una invitación es solo del administrador (pasa por before); el cliente no. */
    public function delete(User $user, Invitation $invitation): bool
    {
        return false;
    }

    /**
     * El dueño arma la lista de invitados de su evento desde su panel. Las tarjetas se mandan
     * a una sola persona: no tienen lista, así que ahí nadie agrega invitados.
     */
    public function manageOwnGuests(User $user, Invitation $invitation): bool
    {
        return $this->view($user, $invitation)
            && app(EventProfiles::class)->forTemplate($invitation->template)->kind() === Module::KIND_INVITATION;
    }

    /** El cliente puede ocultar o volver a mostrar las fotos y canciones de su propia invitación. */
    public function moderateContributions(User $user, Invitation $invitation): bool
    {
        return $this->view($user, $invitation);
    }
}
