<?php

namespace App\Events;

use App\Models\GuestContribution;
use Illuminate\Foundation\Events\Dispatchable;

class GuestContributionSubmitted
{
    use Dispatchable;

    public function __construct(
        public GuestContribution $contribution,
    ) {}
}
