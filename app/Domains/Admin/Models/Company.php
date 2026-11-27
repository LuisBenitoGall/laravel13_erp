<?php

namespace App\Domains\Admin\Models;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Models\User;
use App\Support\Models\Concerns\Auditable;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\Translatable\HasTranslations;

/**
 * Tenant organizativo (Admin). Excepción R-TEN-01: es el tenant, no un hijo scoped.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $tradename
 * @property string|null $nif
 * @property CompanyStatus $status
 * @property string|null $logo
 * @property int|null $parent_id
 * @property array<string, string|null>|string|null $excerpt
 * @property array<string, string|null>|string|null $description
 */
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use Auditable, HasFactory, HasTranslations, SoftDeletes;

    /** @var list<string> */
    public array $translatable = [
        'excerpt',
        'description',
    ];

    protected $fillable = [
        'name',
        'slug',
        'tradename',
        'nif',
        'status',
        'logo',
        'parent_id',
        'excerpt',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'status' => CompanyStatus::class,
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<Company, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_companies')
            ->withPivot(['id', 'position', 'department'])
            ->withTimestamps();
    }

    /** @return BelongsToMany<Sector, $this> */
    public function sectors(): BelongsToMany
    {
        return $this->belongsToMany(Sector::class);
    }

    /** @return BelongsToMany<Currency, $this> */
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'company_currencies')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function defaultCurrency(): ?Currency
    {
        if ($this->relationLoaded('currencies')) {
            /** @var Currency|null $currency */
            $currency = $this->currencies->first(
                fn (Currency $item): bool => (bool) data_get($item->getRelations(), 'pivot.is_default', false),
            );

            return $currency;
        }

        return $this->currencies()
            ->wherePivot('is_default', true)
            ->first();
    }

    /**
     * Sedes de la matriz vinculadas a esta Delegación (lectura inversa del mapa
     * `workplace_delegation`). Sin global scope de tenant: el perímetro lo fija quien consume.
     *
     * @return BelongsToMany<Workplace, $this>
     */
    public function workplacesViaMap(): BelongsToMany
    {
        return $this->belongsToMany(Workplace::class, 'workplace_delegation', 'company_id', 'workplace_id')
            ->withoutGlobalScope('company')
            ->withPivot(['is_preferred', 'is_preferred_seat'])
            ->withTimestamps();
    }

    /**
     * Companies vinculadas al usuario vía `user_companies` (perímetro Super Admin inclusive).
     *
     * @param  Builder<Company>  $query
     */
    public function scopeForUser(Builder $query, User $user): void
    {
        $query->whereHas(
            'users',
            fn (Builder $users) => $users->where('users.id', $user->id),
        );
    }

    public function isLinkedTo(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }

    public function isActive(): bool
    {
        return $this->status === CompanyStatus::Active;
    }

    /**
     * Ancestros desde el padre inmediato hasta la raíz (sin incluir $this).
     *
     * @return Collection<int, Company>
     */
    public function ancestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current !== null) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    public function isDescendantOf(Company $other): bool
    {
        if ($this->is($other)) {
            return false;
        }

        $current = $this->parent;

        while ($current !== null) {
            if ($current->is($other)) {
                return true;
            }

            $current = $current->parent;
        }

        return false;
    }

    public function isAncestorOf(Company $other): bool
    {
        return $other->isDescendantOf($this);
    }

    /** ¿Asignar $candidate como padre de $this crearía un ciclo? */
    public function wouldCycleWithParent(?Company $candidate): bool
    {
        if ($candidate === null) {
            return false;
        }

        if ($candidate->is($this)) {
            return true;
        }

        return $candidate->isDescendantOf($this);
    }

    /** ¿Asignar $this como padre de $child crearía un ciclo? */
    public function wouldCycleWithChild(Company $child): bool
    {
        if ($child->is($this)) {
            return true;
        }

        return $this->isDescendantOf($child);
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }
}
