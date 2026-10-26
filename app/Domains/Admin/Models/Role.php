<?php

namespace App\Domains\Admin\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

/**
 * Spatie Role extendido: columnas `universal` / `description` y scopes de plataforma.
 *
 * Spatie trata `company_id` NULL como rol «global» (OR en findByParam). Aquí el match
 * es exacto: las plantillas NULL no sustituyen roles locales de tenant.
 *
 * @property bool $universal
 * @property string|null $description
 * @property int|null $company_id
 */
class Role extends SpatieRole
{
    public const SUPER_ADMIN = 'Super Admin';

    public const MANAGER = 'manager';

    public const GUEST = 'guest';

    /** @var list<string> */
    public const PROTECTED_TEMPLATE_NAMES = [self::MANAGER, self::GUEST];

    // Sin $fillable: Spatie usa $guarded = [] e inyecta company_id (Teams).

    protected function casts(): array
    {
        return [
            'universal' => 'boolean',
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected static function findByParam(array $params = []): ?RoleContract
    {
        $query = static::query();
        $registrar = app(PermissionRegistrar::class);

        if ($registrar->teams) {
            $teamsKey = $registrar->teamsKey;
            $teamId = $params[$teamsKey] ?? getPermissionsTeamId();

            if ($teamId === null) {
                $query->whereNull($teamsKey);
            } else {
                $query->where($teamsKey, $teamId);
            }

            unset($params[$teamsKey]);
        }

        foreach ($params as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first();
    }

    /** @param  Builder<Role>  $query */
    public function scopePlatform(Builder $query): void
    {
        $teamKey = config('permission.column_names.team_foreign_key');
        $query->whereNull($teamKey);
    }

    /** @param  Builder<Role>  $query */
    public function scopeUniversalTemplates(Builder $query): void
    {
        $teamKey = config('permission.column_names.team_foreign_key');
        $query->whereNull($teamKey)
            ->where('universal', true)
            ->where('name', '!=', self::SUPER_ADMIN);
    }

    /** @param  Builder<Role>  $query */
    public function scopePlatformManaged(Builder $query): void
    {
        $teamKey = config('permission.column_names.team_foreign_key');
        $query->whereNull($teamKey)->where('name', '!=', self::SUPER_ADMIN);
    }

    /** @param  Builder<Role>  $query */
    public function scopeForCompany(Builder $query, int $companyId): void
    {
        $teamKey = config('permission.column_names.team_foreign_key');
        $query->where($teamKey, $companyId);
    }

    public function isSuperAdmin(): bool
    {
        return $this->name === self::SUPER_ADMIN && $this->getAttribute($this->teamForeignKey()) === null;
    }

    public function isProtectedTemplate(): bool
    {
        return $this->getAttribute($this->teamForeignKey()) === null
            && in_array($this->name, self::PROTECTED_TEMPLATE_NAMES, true);
    }

    public function isManagerTemplate(): bool
    {
        return $this->name === self::MANAGER
            && $this->getAttribute($this->teamForeignKey()) === null
            && $this->universal;
    }

    public function isLocalRole(): bool
    {
        return $this->getAttribute($this->teamForeignKey()) !== null;
    }

    public function isLocalProtected(): bool
    {
        return $this->isLocalRole()
            && in_array($this->name, self::PROTECTED_TEMPLATE_NAMES, true);
    }

    public function isLocalManager(): bool
    {
        return $this->isLocalRole() && $this->name === self::MANAGER;
    }

    public function isLocalGuest(): bool
    {
        return $this->isLocalRole() && $this->name === self::GUEST;
    }

    private function teamForeignKey(): string
    {
        return (string) config('permission.column_names.team_foreign_key');
    }
}
