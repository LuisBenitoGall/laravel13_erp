<?php

namespace App\Domains\Calendar\Policies;

use App\Domains\Calendar\Models\WorkCalendar;
use App\Models\User;

class WorkCalendarPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('calendars.view');
    }

    public function view(User $user, WorkCalendar $workCalendar): bool
    {
        return $user->can('calendars.viewAny') && $workCalendar->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('calendars.create');
    }

    public function update(User $user, WorkCalendar $workCalendar): bool
    {
        return $user->can('calendars.update') && $workCalendar->belongsToCurrentCompany();
    }

    public function delete(User $user, WorkCalendar $workCalendar): bool
    {
        return $user->can('calendars.delete') && $workCalendar->belongsToCurrentCompany();
    }
}
