<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Domains\Admin\Support\DelegationPerimeter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SetDelegationPreferredWorkplaceRequest extends FormRequest
{
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
        return [
            'workplace_id' => ['nullable', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $workplaceId = $this->input('workplace_id');
            if ($workplaceId === null || $workplaceId === '') {
                return;
            }

            /** @var Company $delegation */
            $delegation = $this->route('delegation');

            $linked = Workplace::query()
                ->withoutGlobalScope('company')
                ->whereKey((int) $workplaceId)
                ->whereHas(
                    'delegations',
                    fn ($query) => $query->where('companies.id', $delegation->id),
                )
                ->exists();

            if (! $linked) {
                $v->errors()->add('workplace_id', __('sede_preferida_no_vinculada'));
            }
        });
    }
}
