<?php

namespace App\Domains\Admin\Http\Requests\Concerns;

use App\Domains\Admin\Enums\CurrencyStatus;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Currency;
use Illuminate\Validation\Validator;

trait ValidatesCompanyCurrenciesSync
{
    /** @return array<string, mixed> */
    protected function companyCurrenciesRules(): array
    {
        return [
            'currency_ids' => ['present', 'array'],
            'currency_ids.*' => ['integer', 'distinct'],
            'default_currency_id' => ['nullable', 'integer'],
        ];
    }

    protected function validateCompanyCurrenciesSync(Validator $validator, Company $company): void
    {
        $validator->after(function (Validator $v) use ($company): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            /** @var list<int|string>|null $rawIds */
            $rawIds = $this->input('currency_ids');
            $ids = array_values(array_unique(array_map('intval', $rawIds ?? [])));
            $defaultRaw = $this->input('default_currency_id');
            $defaultId = $defaultRaw === null || $defaultRaw === ''
                ? null
                : (int) $defaultRaw;

            if ($ids === []) {
                if ($defaultId !== null) {
                    $v->errors()->add('default_currency_id', __('moneda_default_requiere_permitidas'));
                }

                return;
            }

            if ($defaultId === null) {
                $v->errors()->add('default_currency_id', __('moneda_default_obligatoria'));

                return;
            }

            if (! in_array($defaultId, $ids, true)) {
                $v->errors()->add('default_currency_id', __('moneda_default_debe_estar_en_permitidas'));

                return;
            }

            $existingLinked = $company->currencies()
                ->pluck('currencies.id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $newIds = array_values(array_diff($ids, $existingLinked));

            $found = Currency::query()
                ->whereIn('id', $ids)
                ->get(['id', 'status']);

            if ($found->count() !== count($ids)) {
                $v->errors()->add('currency_ids', __('moneda_inexistente'));

                return;
            }

            $inactiveNew = $found
                ->filter(fn (Currency $currency): bool => in_array((int) $currency->id, $newIds, true)
                    && $currency->status !== CurrencyStatus::Active)
                ->pluck('id')
                ->all();

            if ($inactiveNew !== []) {
                $v->errors()->add('currency_ids', __('moneda_nueva_debe_estar_activa'));
            }
        });
    }
}
