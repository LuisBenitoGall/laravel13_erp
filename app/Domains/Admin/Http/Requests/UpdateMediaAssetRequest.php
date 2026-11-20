<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\MediaAsset;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var MediaAsset $asset */
        $asset = $this->route('media_asset');

        return $this->user()?->can('update', $asset) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.name'),
            'alt' => __('galeria_alt'),
        ];
    }
}
