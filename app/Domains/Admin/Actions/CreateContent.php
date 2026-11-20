<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ContentKind;
use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Content;
use App\Domains\Admin\Services\HelpContentMap;
use App\Support\Strings\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateContent
{
    public function __construct(
        private readonly HelpContentMap $helpContentMap,
    ) {}

    /**
     * @param  array{name: string, kind: string, code?: string|null}  $data
     */
    public function handle(array $data): Content
    {
        return DB::transaction(function () use ($data): Content {
            $name = (string) $data['name'];
            $kind = ContentKind::from((string) $data['kind']);

            $code = $kind === ContentKind::Help
                ? (string) $data['code']
                : UniqueSlug::from(
                    $name,
                    fn (string $candidate): bool => Content::withTrashed()
                        ->where('code', $candidate)
                        ->exists(),
                );

            $content = Content::query()->create([
                'name' => $name,
                'code' => $code,
                'kind' => $kind,
                'company_id' => null,
                'title' => [],
                'excerpt' => [],
                'content' => [],
                'referrer' => [],
                'video' => [],
                'status' => ContentStatus::Inactive,
            ]);

            $this->helpContentMap->forget();

            return $content;
        });
    }
}
