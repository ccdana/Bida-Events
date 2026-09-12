<?php

namespace App\Events;

use App\Models\PollVote;
use Illuminate\Foundation\Events\Dispatchable;

class PollVoteSubmitted
{
    use Dispatchable;

    public function __construct(
        public PollVote $vote,
    ) {}
}
