<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\TableFilterPreset;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateTableFilterPreset
{
    /** @param  array{name: string}  $data */
    public function handle(TableFilterPreset $preset, array $data): TableFilterPreset
    {
        return DB::transaction(function () use ($preset, $data): TableFilterPreset {
            $name = trim($data['name']);

            $duplicate = TableFilterPreset::query()
                ->where('user_id', $preset->user_id)
                ->where('company_id', $preset->company_id)
                ->where('table_key', $preset->table_key)
                ->where('name', $name)
                ->whereKeyNot($preset->id)
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'name' => __('presets_filtro_nombre_duplicado'),
                ]);
            }

            $preset->update(['name' => $name]);

            return $preset->refresh();
        });
    }
}
