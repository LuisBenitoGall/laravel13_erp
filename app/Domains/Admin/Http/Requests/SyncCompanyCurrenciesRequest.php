<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Http\Requests\Concerns\ValidatesCompanyCurrenciesSync;
use App\Domains\Admin\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SyncCompanyCurrenciesRequest extends FormRequest
{
    use ValidatesCompanyCurrenciesSync;

    public function authorize(): bool
    {
        /** @var Company $company */
        $company = $this->route('company');
        $user = $this->user();

        if (! $user instanceof User) {
            return false;
        }

        return $company->isLinkedTo($user) && $user->can('update', $company);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->companyCurrenciesRules();
    }

    public function withValidator(Validator $validator): void
    {
        /** @var Company $company */
        $company = $this->route('company');
        $this->validateCompanyCurrenciesSync($validator, $company);
    }
}
