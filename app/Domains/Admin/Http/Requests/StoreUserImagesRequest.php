<?php

namespace App\Domains\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreUserImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxKb = (int) config('user_images.max_kilobytes', 1024);
        $maxUpload = (int) config('user_images.max_per_upload', 20);
        /** @var list<string> $mimes */
        $mimes = config('user_images.mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

        return [
            'images' => ['required', 'array', 'min:1', 'max:'.$maxUpload],
            'images.*' => [
                'required',
                File::image()
                    ->types($mimes)
                    ->max($maxKb),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'images.required' => __('imagenes_requeridas'),
            'images.max' => __('imagenes_limite_subida', ['max' => (int) config('user_images.max_per_upload', 20)]),
            'images.*.max' => __('imagen_peso_maximo', ['max' => (int) config('user_images.max_kilobytes', 1024)]),
            'images.*.mimes' => __('imagen_formato_invalido'),
        ];
    }
}
