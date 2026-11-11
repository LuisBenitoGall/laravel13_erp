<?php

namespace App\Domains\Admin\Actions;

use App\Domains\Partners\Enums\CompanyStatus;
use App\Models\User;
use App\Models\UserCompany;
use App\Support\Tenancy\CurrentCompany;
use App\Support\Tenancy\SetCompanySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Elimina solo el vínculo user_companies. Si el actor es el owner y pierde
 * la company de sesión → otra company (menor id) o logout.
 *
 * @return array{logout: bool, switched_company_id: int|null}
 */
class DetachUserCompany
{
    public function __construct(
        private readonly SetCompanySession $setCompanySession,
        private readonly CurrentCompany $currentCompany,
    ) {}

    /**
     * @return array{logout: bool, switched_company_id: int|null}
     */
    public function handle(UserCompany $membership, User $actor, Request $request): array
    {
        $owner = $membership->user;
        abort_if($owner === null, 404);

        $detachedCompanyId = (int) $membership->company_id;
        $isSelf = $actor->id === $owner->id;
        $sessionCompanyId = $this->currentCompany->id();
        $detachingSessionCompany = $isSelf
            && $sessionCompanyId !== null
            && (int) $sessionCompanyId === $detachedCompanyId;

        DB::transaction(function () use ($membership): void {
            $membership->delete();
        });

        if (! $detachingSessionCompany) {
            return ['logout' => false, 'switched_company_id' => null];
        }

        $nextCompanyId = $owner->companies()
            ->where('companies.status', CompanyStatus::Active)
            ->orderBy('companies.id')
            ->value('companies.id');

        if ($nextCompanyId === null) {
            $this->setCompanySession->clear($request);

            return ['logout' => true, 'switched_company_id' => null];
        }

        $this->setCompanySession->handle($request, $owner, (int) $nextCompanyId);

        return ['logout' => false, 'switched_company_id' => (int) $nextCompanyId];
    }
}
