<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\AccountStatus;
use App\Domains\Admin\Models\Account;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateAccount
{
    public function __construct(
        private readonly SetDefaultAccount $setDefaultAccount,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(array $data): Account
    {
        return DB::transaction(function () use ($data): Account {
            $makeDefault = (bool) ($data['is_default'] ?? false);
            unset($data['is_default']);

            $data['slug'] = UniqueSlug::from(
                (string) ($data['name'] ?? ''),
                fn (string $slug): bool => Account::withTrashed()->where('slug', $slug)->exists(),
            );
            $data['status'] ??= AccountStatus::Active->value;
            $data['is_default'] = false;
            $data['rate'] ??= 0;

            $account = Account::query()->create($data);

            if ($makeDefault) {
                $this->setDefaultAccount->handle($account);
            }

            return $account->refresh();
        });
    }
}
