<?php

namespace App\Observers;

use App\Models\Holiday;

class HolidayObserver
{
    public function creating(Holiday $holiday): void
    {
        if(!auth()->check()){
            return;
        }
        $holiday->user_id = auth()->id();
    }
}
