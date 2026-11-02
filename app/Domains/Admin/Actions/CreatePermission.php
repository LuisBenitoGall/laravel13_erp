<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Support\CanonicalPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class CreatePermission
{
    /**
     * @param  array{functionality_id: int, suffix: string}  $data
     */
    public function handle(array $data): Permission
    {
        return DB::transaction(function () use ($data) {
            $functionality = Functionality::query()->findOrFail($data['functionality_id']);
            $suffix = Str::slug((string) $data['suffix']);
            $name = CanonicalPermission::composeName($functionality->slug, $suffix);

            return Permission::query()->create([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        });
    }
}
