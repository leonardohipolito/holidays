<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Observers\ParticipantObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy(UserScope::class)]
#[ObservedBy(ParticipantObserver::class)]
class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function holiday():BelongsToMany{
        return $this->belongsToMany(Holiday::class);
    }
}
