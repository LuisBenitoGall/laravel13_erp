<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Support\CanonicalPermission;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DeletePermission
{
    public function handle(Permission $permission): void
    {
        if (CanonicalPermission::isCanonical($permission->name)) {
            throw new HttpException(403, 'Canonical permissions cannot be deleted.');
        }

        DB::transaction(fn () => $permission->delete());
    }
}
