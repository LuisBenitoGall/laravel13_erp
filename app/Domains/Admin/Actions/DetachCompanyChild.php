<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DetachCompanyChild
{
    /** Quita el vínculo padre→hijo sin borrar la Company hija. */
    public function handle(Company $parent, Company $child): Company
    {
        if ((int) $child->parent_id !== (int) $parent->id) {
            throw new InvalidArgumentException(__('jerarquia_hijo_no_pertenece'));
        }

        return DB::transaction(function () use ($child): Company {
            $child->update([
                'parent_id' => null,
            ]);

            return $child->refresh();
        });
    }
}
