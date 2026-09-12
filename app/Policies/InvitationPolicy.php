<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;

class InvitationPolicy
{
    /**
     * Los administradores gestionan todas las invitaciones.
     */
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return (int) $invitation->user_id === (int) $user->id;
    }

    public function export(User $user, Invitation $invitation): bool
    {
        return $this->view($user, $invitation);
    }

    public function update(User $user, Invitation $invitation): bool
    {
        return false;
    }

    public function manageGuests(User $user, Invitation $invitation): bool
    {
        return false;
    }
}
