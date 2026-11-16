<?php

namespace App\Domains\Admin\Models;

use App\Models\User;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\PreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Favorito de vista del ERP (user + company de sesión).
 *
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property int|null $module_id
 * @property string $name
 * @property string $url
 */
class Preference extends Model
{
    public const MAX_PER_COMPANY = 20;

    /** @use HasFactory<PreferenceFactory> */
    use Auditable, BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'module_id',
        'name',
        'url',
    ];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Module, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    protected static function newFactory(): PreferenceFactory
    {
        return PreferenceFactory::new();
    }
}
