<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Preference;
use App\Domains\Admin\Services\PreferenceModuleResolver;
use App\Domains\Admin\Support\PreferenceUrlNormalizer;
use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePreference
{
    public function __construct(
        private readonly PreferenceModuleResolver $moduleResolver,
    ) {}

    /** @param  array{name: string, url: string}  $data */
    public function handle(User $user, array $data): Preference
    {
        return DB::transaction(function () use ($user, $data): Preference {
            $companyId = app(CurrentCompany::class)->id();
            if ($companyId === null) {
                abort(403);
            }

            $url = PreferenceUrlNormalizer::forStorage($data['url']);
            $moduleId = $this->moduleResolver->resolve($url);

            $existing = Preference::query()
                ->where('user_id', $user->id)
                ->where('url', $url)
                ->first();

            if ($existing !== null) {
                $existing->update([
                    'name' => $data['name'],
                    'module_id' => $moduleId,
                ]);

                return $existing->refresh();
            }

            $count = Preference::query()
                ->where('user_id', $user->id)
                ->count();

            if ($count >= Preference::MAX_PER_COMPANY) {
                throw ValidationException::withMessages([
                    'name' => __('favoritos_limite_alcanzado'),
                ]);
            }

            return Preference::query()->create([
                'user_id' => $user->id,
                'module_id' => $moduleId,
                'name' => $data['name'],
                'url' => $url,
            ]);
        });
    }
}
