<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Content;
use App\Domains\Admin\Services\HelpContentMap;
use App\Support\Html\AllowedHtmlSanitizer;
use Illuminate\Support\Facades\DB;

class UpdateContent
{
    public function __construct(
        private readonly HelpContentMap $helpContentMap,
        private readonly AllowedHtmlSanitizer $htmlSanitizer,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function handle(Content $content, array $data): Content
    {
        return DB::transaction(function () use ($content, $data): Content {
            unset($data['kind'], $data['code'], $data['company_id']);

            $title = is_array($data['title'] ?? null) ? $data['title'] : [];
            $excerpt = is_array($data['excerpt'] ?? null) ? $data['excerpt'] : [];

            $payload = [
                'name' => (string) $data['name'],
                'status' => ContentStatus::from((string) $data['status']),
                'title' => $this->htmlSanitizer->sanitizeLocaleMap($title, plain: true),
                'excerpt' => $this->htmlSanitizer->sanitizeLocaleMap($excerpt, plain: true),
                'referrer' => $this->stringList($data['referrer'] ?? null),
                'video' => $this->stringList($data['video'] ?? null),
            ];

            if ($content->kind === ContentKind::Article) {
                $body = is_array($data['content'] ?? null) ? $data['content'] : [];
                $payload['content'] = $this->htmlSanitizer->sanitizeLocaleMap($body);
            }

            $content->update($payload);
            $this->helpContentMap->forget();

            return $content->refresh();
        });
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $item) {
            if (! is_string($item)) {
                continue;
            }

            $trimmed = trim($item);
            if ($trimmed !== '') {
                $out[] = $trimmed;
            }
        }

        return $out;
    }
}
