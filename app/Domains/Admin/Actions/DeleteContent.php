<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Content;
use App\Domains\Admin\Services\HelpContentMap;
use Illuminate\Support\Facades\DB;

class DeleteContent
{
    public function __construct(
        private readonly HelpContentMap $helpContentMap,
    ) {}

    public function handle(Content $content): void
    {
        DB::transaction(function () use ($content): void {
            $content->delete();
            $this->helpContentMap->forget();
        });
    }
}
