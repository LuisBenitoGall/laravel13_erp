<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Models\MediaAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class StoreMediaAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MediaAsset::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxKb = (int) config('gallery.max_kilobytes', 1024);
        $maxUpload = (int) config('gallery.max_per_upload', 20);
        /** @var list<string> $mimes */
        $mimes = config('gallery.mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

        return [
            'images' => ['required', 'array', 'min:1', 'max:'.$maxUpload],
            'images.*' => [
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
            'images' => __('galeria_imagen'),
            'images.*' => __('galeria_imagen'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $maxKb = (int) config('gallery.max_kilobytes', 1024);
        $maxUpload = (int) config('gallery.max_per_upload', 20);

        return [
            'images.required' => __('galeria_imagen_requerida'),
            'images.max' => __('imagenes_limite_subida', ['max' => $maxUpload]),
            'images.*.max' => __('imagen_peso_maximo', ['max' => $maxKb]),
            'images.*.mimes' => __('imagen_formato_invalido'),
        ];
    }
}
