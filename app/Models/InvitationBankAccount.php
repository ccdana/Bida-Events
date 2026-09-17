<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Cuenta bancaria para regalos por transferencia. */
class InvitationBankAccount extends Model
{
    protected $table = 'invitation_bank_accounts';

    protected $fillable = ['invitation_id', 'bank_name', 'holder', 'document_id', 'account_number', 'qr_image_url', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
