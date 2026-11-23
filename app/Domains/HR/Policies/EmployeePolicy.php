<?php

namespace App\Domains\HR\Policies;

use App\Domains\HR\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('employees.view');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can('employees.viewAny') && $employee->belongsToCurrentCompany();
    }

    public function create(User $user): bool
    {
        return $user->can('employees.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('employees.update') && $employee->belongsToCurrentCompany();
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->can('employees.delete') && $employee->belongsToCurrentCompany();
    }

    /** Renovar = cerrar el periodo activo (update) + crear el nuevo (create). */
    public function renew(User $user, Employee $employee): bool
    {
        return $user->can('employees.update')
            && $user->can('employees.create')
            && $employee->belongsToCurrentCompany();
    }

    /** Finalizar el periodo = update. */
    public function end(User $user, Employee $employee): bool
    {
        return $this->update($user, $employee);
    }
}
