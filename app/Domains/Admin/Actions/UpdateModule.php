<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ModuleStatus;
use App\Domains\Admin\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateModule
{
    public function __construct(
        private readonly AuthorizeModuleForActiveCompanies $authorizeModuleForActiveCompanies,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(Module $module, array $data): Module
    {
        return DB::transaction(function () use ($module, $data) {
            unset($data['slug']);

            if ($module->isShellCritical() && $this->wouldDeactivate($data)) {
                throw ValidationException::withMessages([
                    'status' => __('modulo_shell_no_desactivable'),
                ]);
            }

            $module->update($data);
            $module->refresh();

            $this->authorizeModuleForActiveCompanies->handle($module);

            return $module;
        });
    }

    /** @param  array<string, mixed>  $data */
    private function wouldDeactivate(array $data): bool
    {
        if (! array_key_exists('status', $data)) {
            return false;
        }

        $status = $data['status'];

        if ($status instanceof ModuleStatus) {
            return $status === ModuleStatus::Inactive;
        }

        return is_string($status) && $status === ModuleStatus::Inactive->value;
    }
}
