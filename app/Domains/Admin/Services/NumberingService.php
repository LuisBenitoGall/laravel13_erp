<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\ValueObjects\DocumentNumber;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Numeración transaccional con bloqueo FOR UPDATE (R-BD-05).
 */
class NumberingService
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    public function next(string $docType): DocumentNumber
    {
        $companyId = $this->currentCompany->id();

        if ($companyId === null) {
            throw new RuntimeException('Cannot allocate document number without CurrentCompany.');
        }

        return DB::transaction(function () use ($companyId, $docType): DocumentNumber {
            $row = DB::table('document_numbers')
                ->where('company_id', $companyId)
                ->where('doc_type', $docType)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                DB::table('document_numbers')->insert([
                    'company_id' => $companyId,
                    'doc_type' => $docType,
                    'last_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sequence = 1;
            } else {
                $sequence = (int) $row->last_number + 1;

                DB::table('document_numbers')
                    ->where('id', $row->id)
                    ->update([
                        'last_number' => $sequence,
                        'updated_at' => now(),
                    ]);
            }

            return new DocumentNumber(
                docType: $docType,
                sequence: $sequence,
                formatted: sprintf('%s-%d', strtoupper($docType), $sequence),
            );
        });
    }
}
