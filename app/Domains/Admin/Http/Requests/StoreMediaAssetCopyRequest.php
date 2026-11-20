<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\MediaAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class StoreMediaAssetCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        /** @var MediaAsset $source */
        $source = $this->route('media_asset');

        return $user->can('create', MediaAsset::class)
            && $user->can('view', $source);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxKb = (int) config('gallery.max_kilobytes', 1024);
        /** @var list<string> $mimes */
        $mimes = config('gallery.mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

        return [
            'image' => [
                'required',
                File::image()
                    ->types($mimes)
                    ->max($maxKb),
            ],
            'company_id' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('company_id')) {
                $validator->errors()->add('company_id', __('galeria_upload_empresa_bloqueado'));
            }
        });
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'image' => __('galeria_imagen'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $maxKb = (int) config('gallery.max_kilobytes', 1024);

        return [
            'image.required' => __('galeria_imagen_requerida'),
            'image.max' => __('imagen_peso_maximo', ['max' => $maxKb]),
            'image.mimes' => __('imagen_formato_invalido'),
        ];
    }
}
