<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AttachCompanyChild
{
    /** Asigna `parent_id` del hijo a la company editada (reassign si ya tenía padre). */
    public function handle(Company $parent, Company $child): Company
    {
        if ($parent->wouldCycleWithChild($child)) {
            throw new InvalidArgumentException(__('jerarquia_ciclo_rechazado'));
        }

        return DB::transaction(function () use ($parent, $child): Company {
            $child->update([
                'parent_id' => $parent->id,
            ]);

            return $child->refresh();
        });
    }
}
