<?php

namespace App\Domains\Admin\Http\Requests;

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Content;
use App\Support\I18n\LocaleResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Content $content */
        $content = $this->route('content');

        return $this->user()?->can('update', $content) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Content $content */
        $content = $this->route('content');
        $locales = app(LocaleResolver::class);
        $default = $locales->default();
        $isArticle = $content->kind === ContentKind::Article;

        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'title' => ['required', 'array'],
            'excerpt' => ['sometimes', 'nullable', 'array'],
            'referrer' => ['sometimes', 'nullable', 'array'],
            'referrer.*' => ['nullable', 'string', 'url', 'max:255'],
            'video' => ['sometimes', 'nullable', 'array'],
            'video.*' => ['nullable', 'string', 'url', 'max:255'],
        ];

        foreach ($locales->supported() as $locale) {
            $titlePresence = $locale === $default ? 'required' : 'nullable';
            $rules['title.'.$locale] = [$titlePresence, 'string', 'max:255'];
            $rules['excerpt.'.$locale] = ['nullable', 'string', 'max:2000'];

            if ($isArticle) {
                $contentPresence = $locale === $default ? 'required' : 'nullable';
                $rules['content.'.$locale] = [$contentPresence, 'string', 'max:20000'];
            }
        }

        if ($isArticle) {
            $rules['content'] = ['required', 'array'];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.name'),
            'status' => __('validation.attributes.status'),
            'title' => __('validation.attributes.title'),
            'excerpt' => __('validation.attributes.excerpt'),
            'content' => __('validation.attributes.content'),
            'referrer' => __('validation.attributes.referrer'),
            'video' => __('validation.attributes.video'),
        ];
    }
}
