<?php

namespace App\Domains\Admin\Support;

use App\Models\User;

/**
 * Authz de flags de visibilidad/estado en ficha User (`is_public`, `status`).
 *
 * Self siempre puede. Un permiso futuro (slug TBD) también podrá vía
 * `config('erp.user_profile.visibility_permission')` cuando producto lo cierre.
 */
final class UserProfileAuthorization
{
    /**
     * Punto de extensión: config `erp.user_profile.visibility_permission`.
     * Null/vacío = solo self (slug definitivo aún TBD).
     */
    public static function visibilityPermissionSlug(): ?string
    {
        $slug = config('erp.user_profile.visibility_permission');

        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    public static function canChangeVisibilityFlags(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return true;
        }

        $slug = self::visibilityPermissionSlug();

        if ($slug === null) {
            return false;
        }

        return $actor->can($slug);
    }
}
