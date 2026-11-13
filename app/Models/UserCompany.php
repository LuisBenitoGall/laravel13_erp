<?php

namespace App\Models;

use App\Domains\Admin\Models\Company;
use Database\Factories\UserCompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Membresía User↔Company (`user_companies`). ≠ Empleo (entidad futura).
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property string|null $position
 * @property string|null $department
 */
#[Fillable(['user_id', 'company_id', 'position', 'department'])]
class UserCompany extends Model
{
    /** @use HasFactory<UserCompanyFactory> */
    use HasFactory;

    protected $table = 'user_companies';

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
