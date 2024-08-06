<?php

namespace App\Observers;

use App\Models\Participant;

class ParticipantObserver
{
    public function creating(Participant $participant): void
    {
        if(!auth()->check()){
            return;
        }
        $participant->user_id = auth()->id();
    }
}
