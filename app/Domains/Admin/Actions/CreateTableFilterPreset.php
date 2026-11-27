<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\TableFilterPreset;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateTableFilterPreset
{
    /**
     * @param  array{table_key: string, name: string, filters: list<array{field: string, type: string, value?: mixed}>}  $data
     */
    public function handle(User $user, array $data): TableFilterPreset
    {
        return DB::transaction(function () use ($user, $data): TableFilterPreset {
            $companyId = app(CurrentCompany::class)->id();
            if ($companyId === null) {
                abort(403);
            }

            $tableKey = $data['table_key'];
            $name = trim($data['name']);

            $count = TableFilterPreset::query()
                ->where('user_id', $user->id)
                ->where('table_key', $tableKey)
                ->count();

            if ($count >= TableFilterPreset::MAX_PER_TABLE) {
                throw ValidationException::withMessages([
                    'name' => __('presets_filtro_limite_alcanzado', [
                        'max' => TableFilterPreset::MAX_PER_TABLE,
                    ]),
                ]);
            }

            $duplicate = TableFilterPreset::query()
                ->where('user_id', $user->id)
                ->where('table_key', $tableKey)
                ->where('name', $name)
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'name' => __('presets_filtro_nombre_duplicado'),
                ]);
            }

            return TableFilterPreset::query()->create([
                'user_id' => $user->id,
                'table_key' => $tableKey,
                'name' => $name,
                'filters' => $data['filters'],
            ]);
        });
    }
}
