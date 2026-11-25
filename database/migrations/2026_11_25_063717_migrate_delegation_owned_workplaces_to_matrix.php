<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Opción A: workplaces con company_id = Delegación pasan a la matriz + pivote.
 * Featured: si la matriz ya tiene uno, el migrado pierde featured (keep matrix).
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('workplaces')
            ->join('companies', 'companies.id', '=', 'workplaces.company_id')
            ->whereNotNull('companies.parent_id')
            ->select([
                'workplaces.id as workplace_id',
                'workplaces.company_id as delegation_id',
                'workplaces.featured',
                'workplaces.deleted_at',
                'companies.parent_id as matrix_id',
            ])
            ->get();

        foreach ($rows as $row) {
            $matrixHasFeatured = DB::table('workplaces')
                ->where('company_id', $row->matrix_id)
                ->where('featured', true)
                ->whereNull('deleted_at')
                ->exists();

            $featured = (bool) $row->featured;
            if ($featured && $matrixHasFeatured) {
                $featured = false;
            }

            DB::table('workplaces')
                ->where('id', $row->workplace_id)
                ->update([
                    'company_id' => $row->matrix_id,
                    'featured' => $featured,
                    'updated_at' => now(),
                ]);

            $exists = DB::table('workplace_delegation')
                ->where('workplace_id', $row->workplace_id)
                ->where('company_id', $row->delegation_id)
                ->exists();

            if (! $exists) {
                DB::table('workplace_delegation')->insert([
                    'workplace_id' => $row->workplace_id,
                    'company_id' => $row->delegation_id,
                    'is_preferred' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Irreversible: no restauramos company_id = hija sin riesgo de datos ambiguos.
    }
};
