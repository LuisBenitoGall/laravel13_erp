<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Enums\ContentStatus;
use App\Domains\Admin\Models\Content;
use App\Domains\Admin\Services\HelpContentMap;
use Illuminate\Support\Facades\DB;

class ToggleContentStatus
{
    public function __construct(
        private readonly HelpContentMap $helpContentMap,
    ) {}

    public function handle(Content $content, ?ContentStatus $status = null): Content
    {
        return DB::transaction(function () use ($content, $status): Content {
            $content->update([
                'status' => $status ?? ($content->status === ContentStatus::Active
                    ? ContentStatus::Inactive
                    : ContentStatus::Active),
            ]);

            $this->helpContentMap->forget();

            return $content->refresh();
        });
    }
}
