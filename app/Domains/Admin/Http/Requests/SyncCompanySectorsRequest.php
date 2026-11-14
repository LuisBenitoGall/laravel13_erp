<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncCompanySectorsRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Company $company */
        $company = $this->route('company');
        $user = $this->user();

        if (! $user instanceof User) {
            return false;
        }

        return $company->isLinkedTo($user) && $user->can('company-sectors.update');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sector_ids' => ['present', 'array'],
            'sector_ids.*' => [
                'integer',
                Rule::exists('sectors', 'id')
                    ->whereNull('deleted_at')
                    ->where('status', SectorStatus::Active->value),
            ],
        ];
    }
}
