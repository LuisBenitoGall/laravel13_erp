<?php

namespace App\Domains\Admin\Support;

use App\Domains\Admin\Models\Company;
use App\Models\Phone;
use App\Models\User;
use App\Models\UserEmail;

final class UserQuickViewData
{
    /**
     * @param  'profile'|'company'|'directory'  $imageContext
     * @return array{
     *     user: array{name: string, surname: string|null, email: string|null},
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
     *     companies: list<array{id: int, name: string, href: null}>,
     *     avatar_url: string|null,
     *     can_edit: bool
     * }
     */
    public function build(User $user, bool $canEdit, string $imageContext = 'company'): array
    {
        $user->loadMissing(['emails', 'phones', 'images', 'companies']);

        $featured = $user->images->firstWhere('featured', true);

        return [
            'user' => [
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
            ],
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
            'companies' => $user->companies
                ->sortBy(fn (Company $linked): string => mb_strtolower($linked->name))
                ->values()
                ->map(fn (Company $linked): array => [
                    'id' => $linked->id,
                    'name' => $linked->name,
                    'href' => null,
                ])
                ->all(),
            'avatar_url' => $featured !== null ? $featured->url($imageContext) : null,
            'can_edit' => $canEdit,
        ];
    }
}
