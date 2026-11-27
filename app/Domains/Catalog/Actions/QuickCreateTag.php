<?php

namespace App\Domains\Catalog\Actions;

use App\Domains\Catalog\Enums\TagStatus;
use App\Domains\Catalog\Models\Tag;

class QuickCreateTag
{
    public function __construct(private CreateTag $createTag) {}

    public function handle(string $name): Tag
    {
        return $this->createTag->handle([
            'name' => $name,
            'status' => TagStatus::Active->value,
        ]);
    }
}
