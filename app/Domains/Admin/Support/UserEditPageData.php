<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Models\Company;
use App\Models\Phone;
use App\Models\User;
use App\Models\UserCompany;
use App\Models\UserDoc;
use App\Models\UserEmail;
use App\Models\UserImage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

final class UserEditPageData
{
    /**
     * @return array{
     *     context: 'profile'|'company',
     *     user: array<string, mixed>,
     *     company_role: string|null,
     *     emails: list<array{id: int, email: string, description: string|null, value: string, label: string|null}>,
     *     phones: list<array{
     *         id: int,
     *         e164: string,
     *         ext: string|null,
     *         type: string,
     *         label: string|null,
     *         notes: string|null,
     *         is_primary: bool,
     *         is_whatsapp: bool,
     *         is_verified: bool,
     *         value: string
     *     }>,
     *     images: list<array{
     *         id: int,
     *         url: string,
     *         featured: bool,
     *         public: bool,
     *         publicly_visible: bool
     *     }>,
     *     avatar_url: string|null,
     *     docs: list<array{
     *         id: int,
     *         original_name: string,
     *         mime: string,
     *         size_bytes: int,
     *         created_at: string|null
     *     }>,
     *     can: array{
     *         update_profile: bool,
     *         change_password: bool,
     *         change_visibility_flags: bool,
     *         manage_emails: bool,
     *         manage_phones: bool,
     *         manage_images: bool,
     *         manage_docs: bool,
     *         manage_companies: bool
     *     }
     * }
     */
    public function build(User $user, User $actor, string $context, ?Company $company = null): array
    {
        $isSelf = $actor->id === $user->id;

        $user->loadMissing(['emails', 'phones', 'images', 'docs']);

        $featured = $user->images->firstWhere('featured', true);

        return [
            'context' => $context === 'company' ? 'company' : 'profile',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'nif' => $user->nif,
                'birth_date' => $user->birth_date !== null
                    ? Carbon::parse($user->birth_date)->format('Y-m-d')
                    : null,
                'status' => (bool) $user->status,
                'is_public' => (bool) $user->is_public,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
            'company_role' => $context === 'company' && $company !== null
                ? $this->companyRoleName($user, $company)
                : null,
            'emails' => $user->emails
                ->sortBy('id')
                ->values()
                ->map(fn (UserEmail $email): array => [
                    'id' => $email->id,
                    'email' => $email->email,
                    'description' => $email->description,
                    'value' => $email->email,
                    'label' => $email->description,
                ])
                ->all(),
            'phones' => $user->phones
                ->sortBy(fn (Phone $phone): string => sprintf(
                    '%d-%010d',
                    $phone->is_primary ? 0 : 1,
                    $phone->id,
                ))
                ->values()
                ->map(fn (Phone $phone): array => [
                    'id' => $phone->id,
                    'e164' => $phone->e164,
                    'ext' => $phone->ext,
                    'type' => (string) $phone->getRawOriginal('type'),
                    'label' => $phone->label,
                    'notes' => $phone->notes,
                    'is_primary' => (bool) $phone->is_primary,
                    'is_whatsapp' => (bool) $phone->is_whatsapp,
                    'is_verified' => (bool) $phone->is_verified,
                    'value' => $phone->e164,
                ])
                ->all(),
            'images' => $user->images
                ->sortBy(fn (UserImage $image): string => sprintf(
                    '%d-%010d',
                    $image->featured ? 0 : 1,
                    $image->id,
                ))
                ->values()
                ->map(fn (UserImage $image): array => [
                    'id' => $image->id,
                    'url' => $image->url(),
                    'featured' => (bool) $image->featured,
                    'public' => (bool) $image->public,
                    'publicly_visible' => $image->isPubliclyVisible($user),
                ])
                ->all(),
            'avatar_url' => $featured !== null ? $featured->url() : null,
            'docs' => $user->docs
                ->sortByDesc('id')
                ->values()
                ->map(fn (UserDoc $doc): array => [
                    'id' => $doc->id,
                    'original_name' => $doc->original_name,
                    'mime' => $doc->mime,
                    'size_bytes' => (int) $doc->size_bytes,
                    'created_at' => $doc->created_at?->toIso8601String(),
                ])
                ->all(),
            'can' => [
                'update_profile' => true,
                'change_password' => $isSelf,
                'change_visibility_flags' => UserProfileAuthorization::canChangeVisibilityFlags($actor, $user),
                'manage_emails' => true,
                'manage_phones' => true,
                'manage_images' => true,
                'manage_docs' => Gate::forUser($actor)->allows('upload', [UserDoc::class, $user]),
                'manage_companies' => Gate::forUser($actor)->allows('viewAny', [UserCompany::class, $user]),
            ],
        ];
    }

    private function companyRoleName(User $user, Company $company): ?string
    {
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($company->id);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        try {
            $user->unsetRelation('roles');
            $teamKey = (string) config('permission.column_names.team_foreign_key');
            $rolesTable = (string) config('permission.table_names.roles');

            $name = $user->roles()
                ->where("{$rolesTable}.{$teamKey}", $company->id)
                ->orderBy('name')
                ->value('name');

            return is_string($name) ? $name : null;
        } finally {
            setPermissionsTeamId($previousTeamId);
            $user->unsetRelation('roles');
        }
    }
}
