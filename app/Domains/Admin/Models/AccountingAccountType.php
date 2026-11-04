<?php

namespace App\Domains\Admin\Models;

use App\Support\Models\Concerns\Auditable;
use Database\Factories\AccountingAccountTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catálogo global de grupos contables PGC (excepción R-TEN-01 --global).
 *
 * @property int $id
 * @property int $autoreference
 * @property string|null $code
 * @property string|null $name
 * @property string|null $mode
 */
class AccountingAccountType extends Model
{
    /** @use HasFactory<AccountingAccountTypeFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'autoreference',
        'code',
        'name',
        'mode',
    ];

    protected function casts(): array
    {
        return [
            'autoreference' => 'integer',
        ];
    }

    public function getLevelAttribute(): int
    {
        return strlen((string) ($this->code ?? ''));
    }

    /** @return HasMany<AccountingAccountType, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'autoreference');
    }

    protected static function newFactory(): AccountingAccountTypeFactory
    {
        return AccountingAccountTypeFactory::new();
    }
}
