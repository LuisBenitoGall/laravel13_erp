<?php

namespace App\Domains\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreUserDocsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxKb = (int) config('user_docs.max_kilobytes', 20480);
        $maxUpload = (int) config('user_docs.max_per_upload', 10);
        /** @var list<string> $mimes */
        $mimes = config('user_docs.mimes', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

        return [
            'docs' => ['required', 'array', 'min:1', 'max:'.$maxUpload],
            'docs.*' => [
                'required',
                File::types($mimes)->max($maxKb),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'docs.required' => __('docs_requeridos'),
            'docs.max' => __('docs_limite_subida', ['max' => (int) config('user_docs.max_per_upload', 10)]),
            'docs.*.max' => __('doc_peso_maximo', ['max' => (int) config('user_docs.max_kilobytes', 20480)]),
            'docs.*.mimes' => __('doc_formato_invalido'),
            'docs.*.extensions' => __('doc_formato_invalido'),
        ];
    }
}
