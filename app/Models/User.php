<?php

namespace App\Models;

use App\Domains\Partners\Enums\CompanyStatus;
use App\Domains\Partners\Models\Company;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** @return BelongsToMany<Company, $this> */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'user_companies')->withTimestamps();
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
