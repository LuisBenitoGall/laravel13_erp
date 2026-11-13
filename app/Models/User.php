<?php

namespace App\Models;

use App\Domains\Admin\Enums\CompanyStatus;
use App\Domains\Admin\Models\Company;
use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'surname', 'email', 'locale', 'password', 'is_admin', 'is_public', 'status', 'nif', 'birth_date'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<UserFactory> */
    use CanResetPassword, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'status' => 'boolean',
            'is_admin' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Company, $this> */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'user_companies')
            ->withPivot(['id', 'position', 'department'])
            ->withTimestamps();
    }

    /** @return HasMany<UserCompany, $this> */
    public function companyMemberships(): HasMany
    {
        return $this->hasMany(UserCompany::class);
    }

    /** @return HasMany<UserEmail, $this> */
    public function emails(): HasMany
    {
        return $this->hasMany(UserEmail::class);
    }

    /** @return MorphMany<Phone, $this> */
    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /** @return HasMany<UserImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(UserImage::class);
    }

    /** @return HasMany<UserDoc, $this> */
    public function docs(): HasMany
    {
        return $this->hasMany(UserDoc::class);
    }

    /** @return HasOne<UserImage, $this> */
    public function featuredImage(): HasOne
    {
        return $this->hasOne(UserImage::class)->where('featured', 1);
    }

    /** @return Collection<int, Company> */
    public function activeCompanies(): Collection
    {
        return $this->companies()
            ->where('companies.status', CompanyStatus::Active)
            ->orderBy('companies.name')
            ->get();
    }

    public function belongsToCompany(int $companyId): bool
    {
        return $this->companies()
            ->where('companies.id', $companyId)
            ->where('companies.status', CompanyStatus::Active)
            ->exists();
    }
}
