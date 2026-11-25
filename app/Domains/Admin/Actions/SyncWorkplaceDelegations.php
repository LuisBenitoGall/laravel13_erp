<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Replace del mapa N:M Workplace de la matriz ↔ Delegaciones (hijas) + preferida (≤1).
 *
 * Perímetro: el workplace es de la matriz en sesión y cada Company vinculada tiene
 * `parent_id` = esa matriz. Cero preferidas es válido.
 */
class SyncWorkplaceDelegations
{
    public function __construct(
        private readonly CurrentCompany $currentCompany,
    ) {}

    /**
     * @param  list<int|string>  $delegationIds  conjunto completo (replace)
     * @param  int|string|null  $preferredId  debe estar dentro de $delegationIds
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(Workplace $workplace, array $delegationIds, int|string|null $preferredId = null): Workplace
    {
        return DB::transaction(function () use ($workplace, $delegationIds, $preferredId): Workplace {
            $this->assertWorkplaceBelongsToSessionMatrix($workplace);

            $ids = array_values(array_unique(array_map('intval', $delegationIds)));
            $preferred = $preferredId === null || $preferredId === '' ? null : (int) $preferredId;

            if ($ids !== []) {
                $validCount = Company::query()
                    ->where('parent_id', $workplace->company_id)
                    ->whereIn('id', $ids)
                    ->count();

                if ($validCount !== count($ids)) {
                    throw ValidationException::withMessages([
                        'delegation_ids' => __('delegacion_fuera_de_matriz'),
                    ]);
                }
            }

            if ($preferred !== null && ! in_array($preferred, $ids, true)) {
                throw ValidationException::withMessages([
                    'preferred_delegation_id' => __('delegacion_preferida_no_vinculada'),
                ]);
            }

            $workplace->delegations()->sync(
                collect($ids)
                    ->mapWithKeys(fn (int $id): array => [$id => ['is_preferred' => $id === $preferred]])
                    ->all(),
            );

            return $workplace->refresh();
        });
    }

    private function assertWorkplaceBelongsToSessionMatrix(Workplace $workplace): void
    {
        $sessionId = $this->currentCompany->id();

        $isSessionMatrix = $sessionId !== null
            && (int) $workplace->company_id === $sessionId
            && Company::query()->whereKey($sessionId)->whereNull('parent_id')->exists();

        if (! $isSessionMatrix) {
            throw new AuthorizationException;
        }
    }
}
