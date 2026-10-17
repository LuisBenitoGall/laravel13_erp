<?php

namespace App\Domains\Partners\Http\Requests;

use App\Domains\Admin\Support\CompanyRoleGate;
use App\Domains\Partners\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignCompanyRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Company $company */
        $company = $this->route('company');
        $user = $this->user();

        return $user !== null && CompanyRoleGate::canAssign($user, $company);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(['manager', 'guest'])],
        ];
    }
}
