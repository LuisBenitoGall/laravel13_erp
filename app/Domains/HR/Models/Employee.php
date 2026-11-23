<?php

namespace App\Domains\HR\Models;

use App\Domains\Admin\Models\Company;
use App\Domains\Admin\Models\Workplace;
use App\Domains\HR\Enums\EmployeeStatus;
use App\Models\User;
use App\Support\Models\Concerns\Auditable;
use App\Support\Models\Concerns\BelongsToCompany;
use Carbon\CarbonInterface;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Periodo laboral de un User en la company en sesión (≠ UserCompany).
 *
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property int $contract_type_id
 * @property int|null $workplace_id
 * @property string|null $job_title
 * @property CarbonInterface $start_date
 * @property CarbonInterface|null $end_date
 * @property string $weekly_hours
 * @property bool $time_control
 * @property string|null $year_salary
 * @property EmployeeStatus $status
 * @property string|null $observations
 */
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use Auditable, BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'contract_type_id',
        'workplace_id',
        'job_title',
        'start_date',
        'end_date',
        'weekly_hours',
        'time_control',
        'year_salary',
        'status',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'weekly_hours' => 'decimal:2',
            'time_control' => 'boolean',
            'year_salary' => 'decimal:2',
            'status' => EmployeeStatus::class,
        ];
    }

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

    /** @return BelongsTo<ContractType, $this> */
    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class)->withTrashed();
    }

    /** @return BelongsTo<Workplace, $this> */
    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class)->withTrashed();
    }

    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }
}
