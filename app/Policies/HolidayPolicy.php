<?php

namespace App\Policies;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HolidayPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->tokenCan('holiday:viewAny')
            ? Response::allow()
            : Response::deny('You do not have permission to view any holidays.');
    }

    public function view(User $user, Holiday $holiday): Response
    {
        return $user->tokenCan('holiday:view')
            ? Response::allow()
            : Response::deny('You do not own this holiday.');
    }

    public function create(User $user): Response
    {
        return $user->tokenCan('holiday:create') ? Response::allow() : Response::deny('You do not have permission to create a holiday.');
    }

    public function update(User $user, Holiday $holiday): Response
    {
        return $user->tokenCan('holiday:update') && $user->id === $holiday->user_id
            ? Response::allow()
            : Response::deny('You do not own this holiday.');
    }

    public function delete(User $user, Holiday $holiday): Response
    {
        return $user->tokenCan('holiday:delete') && $user->id === $holiday->user_id
            ? Response::allow()
            : Response::deny('You do not own this holiday.');
    }
}
