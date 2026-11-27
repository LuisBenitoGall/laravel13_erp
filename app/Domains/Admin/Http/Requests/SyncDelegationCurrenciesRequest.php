<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Http\Requests\Concerns\ValidatesCompanyCurrenciesSync;
use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Support\DelegationPerimeter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SyncDelegationCurrenciesRequest extends FormRequest
{
    use ValidatesCompanyCurrenciesSync;

    public function authorize(): bool
    {
        /** @var Company $delegation */
        $delegation = $this->route('delegation');
        app(DelegationPerimeter::class)->assertOwns($delegation);

        return $this->user()?->can('updateDelegation', $delegation) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->companyCurrenciesRules();
    }

    public function withValidator(Validator $validator): void
    {
        /** @var Company $delegation */
        $delegation = $this->route('delegation');
        $this->validateCompanyCurrenciesSync($validator, $delegation);
    }
}
