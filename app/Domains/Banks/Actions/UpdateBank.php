<?php

namespace App\Domains\Banks\Actions;

use App\Domains\Banks\Models\Bank;
use App\Domains\Banks\Support\SwiftCode;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateBank
{
    /** @param  array<string, mixed>  $data */
    public function handle(Bank $bank, array $data): Bank
    {
        return DB::transaction(function () use ($bank, $data): Bank {
            unset($data['slug']);

            $name = $data['name'] ?? $bank->name;

            if ($bank->slug === null || $bank->slug === '' || $name !== $bank->name) {
                $data['slug'] = UniqueSlug::from(
                    (string) $name,
                    fn (string $slug): bool => Bank::withTrashed()
                        ->whereKeyNot($bank->id)
                        ->where('slug', $slug)
                        ->exists(),
                );
            }

            if (array_key_exists('swift', $data)) {
                $swift = SwiftCode::normalize(
                    is_string($data['swift']) ? $data['swift'] : null,
                );
                $data['swift'] = $swift;

                if ($swift !== $bank->swift) {
                    if ($swift !== null) {
                        $data['swift_source'] = 'manual';
                        $data['swift_updated_at'] = now();
                    } else {
                        $data['swift_source'] = null;
                        $data['swift_updated_at'] = null;
                    }
                }
            }

            $bank->update($data);

            return $bank->refresh();
        });
    }
}
