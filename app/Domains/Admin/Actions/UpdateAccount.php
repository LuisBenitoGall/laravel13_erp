<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Account;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class UpdateAccount
{
    public function __construct(
        private readonly SetDefaultAccount $setDefaultAccount,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(Account $account, array $data): Account
    {
        return DB::transaction(function () use ($account, $data): Account {
            $makeDefault = array_key_exists('is_default', $data) && (bool) $data['is_default'];
            unset($data['is_default'], $data['slug']);

            $name = $data['name'] ?? $account->name;

            if ($account->slug === null || $account->slug === '' || $name !== $account->name) {
                $data['slug'] = UniqueSlug::from(
                    (string) $name,
                    fn (string $slug): bool => Account::withTrashed()
                        ->whereKeyNot($account->id)
                        ->where('slug', $slug)
                        ->exists(),
                );
            }

            $account->update($data);

            if ($makeDefault) {
                $this->setDefaultAccount->handle($account);
            }

            return $account->refresh();
        });
    }
}
