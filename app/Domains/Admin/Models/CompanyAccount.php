<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\CompanyAccountStatus;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Database\Factories\CompanyAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Vínculo SaaS Company ↔ Account (tenant; R-TEN-05).
 *
 * @property int $id
 * @property int $company_id
 * @property int $account_id
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property string $price
 * @property Carbon|null $payment_date
 * @property CompanyAccountStatus $status
 */
class CompanyAccount extends Model
{
    /** @use HasFactory<CompanyAccountFactory> */
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'account_id',
        'start_date',
        'end_date',
        'price',
        'payment_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'payment_date' => 'date',
            'price' => 'decimal:2',
            'status' => CompanyAccountStatus::class,
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    protected static function newFactory(): CompanyAccountFactory
    {
        return CompanyAccountFactory::new();
    }
}
