<?php

namespace App\Exports;

use App\Models\Invitation;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class GuestsExport implements FromView
{
    public function __construct(
        protected Invitation $invitation,
        protected array $stats = [],
        protected array $modulos = [],
        protected $guests = null,
    ) {}

    public function view(): View
    {
        $guests = $this->guests ?? $this->invitation->guests()->orderBy('name')->get();

        return view('client.exports.guests-excel', [
            'invitation' => $this->invitation,
            'guests' => $guests,
            'stats' => $this->stats,
            'modulos' => $this->modulos,
        ]);
    }
}