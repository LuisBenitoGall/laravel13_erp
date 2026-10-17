<?php

namespace App\Support\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    /** @param  array{name: string, surname: string, email: string, password: string}  $data */
    public function handle(array $data): User
    {
        return DB::transaction(fn () => User::query()->create([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]));
    }
}
