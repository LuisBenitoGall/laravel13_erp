<?php

namespace App\Models;

use App\Domains\Admin\Enums\PhoneType;
use App\Domains\Admin\Models\Country;
use Database\Factories\PhoneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'phoneable_type',
    'phoneable_id',
    'e164',
    'country_id',
    'ext',
    'type',
    'label',
    'is_primary',
    'is_whatsapp',
    'is_verified',
    'verified_at',
    'notes',
])]
class Phone extends Model
{
    /** @use HasFactory<PhoneFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => PhoneType::class,
            'is_primary' => 'boolean',
            'is_whatsapp' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function phoneable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<Country, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
