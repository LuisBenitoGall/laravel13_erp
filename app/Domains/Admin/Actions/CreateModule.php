<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class CreateModule
{
    public function __construct(
        private readonly AuthorizeModuleForActiveCompanies $authorizeModuleForActiveCompanies,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Module
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = Str::slug((string) ($data['name'] ?? ''));
            $data['status'] ??= ModuleStatus::Active->value;

            $module = Module::query()->create($data);

            Permission::findOrCreate($module->slug.'.access');

            $this->authorizeModuleForActiveCompanies->handle($module);

            return $module;
        });
    }
}
