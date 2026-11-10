<?php

namespace App\Domains\Admin\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Cambio de contraseña solo self. El assert `actor.id === target.id` es
 * explícito (ni Super Admin bypassa este camino).
 */
class UpdateOwnPassword
{
    /**
     * @param  array{current_password: string, password: string}  $data
     */
    public function handle(User $actor, User $target, array $data): void
    {
        if ($actor->id !== $target->id) {
            throw new HttpException(403, 'Password change is limited to the account owner.');
        }

        $current = (string) $target->getAuthPassword();

        if ($current === '' || ! Hash::check($data['current_password'], $current)) {
            throw ValidationException::withMessages([
                'current_password' => __('contrasena_actual_incorrecta'),
            ]);
        }

        DB::transaction(function () use ($target, $data): void {
            $target->forceFill([
                'password' => $data['password'],
            ])->save();
        });
    }
}
