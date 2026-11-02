<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\Functionality;
use App\Domains\Admin\Support\CanonicalPermission;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Permission::class);
    }

    protected function prepareForValidation(): void
    {
        $suffix = (string) $this->input('suffix', '');

        $this->merge([
            'suffix' => Str::slug($suffix),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'functionality_id' => ['required', 'integer', 'exists:functionalities,id'],
            'suffix' => [
                'required',
                'string',
                'max:64',
                'alpha_dash',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    if (CanonicalPermission::isReservedSuffix($value)) {
                        $fail(__('permiso_sufijo_reservado'));
                    }
                },
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    $functionalityId = $this->integer('functionality_id');
                    if ($functionalityId <= 0) {
                        return;
                    }

                    $slug = Functionality::query()
                        ->whereKey($functionalityId)
                        ->value('slug');

                    if (! is_string($slug) || $slug === '') {
                        return;
                    }

                    $name = CanonicalPermission::composeName($slug, $value);
                    $exists = Permission::query()
                        ->where('name', $name)
                        ->where('guard_name', 'web')
                        ->exists();

                    if ($exists) {
                        $fail(__('permiso_nombre_duplicado'));
                    }
                },
            ],
        ];
    }
}
