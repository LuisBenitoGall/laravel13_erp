<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Module;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DeleteModule
{
    /** @var list<string> */
    private const SHELL_SLUGS = ['admin', 'workspace'];

    public function handle(Module $module): void
    {
        if (in_array($module->slug, self::SHELL_SLUGS, true)) {
            throw new HttpException(403, 'Shell modules cannot be soft-deleted.');
        }

        DB::transaction(fn () => $module->delete());
    }
}
