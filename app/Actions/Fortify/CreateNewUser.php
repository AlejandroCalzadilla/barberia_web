<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'fecha_nacimiento' => ['nullable', 'date'],
            'ci' => ['nullable', 'string', 'max:20'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // Crear usuario con rol de cliente por defecto
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'is_propietario' => false,
            'is_barbero' => false,
            'is_cliente' => true, // Por defecto es cliente
            'tipo_usuario' => 'cliente',
            'estado' => 'activo',
        ]);

        // Crear registro de Cliente asociado con los datos adicionales
        Cliente::create([
            'id_usuario' => $user->id,
            'fecha_nacimiento' => $input['fecha_nacimiento'] ?? null,
            'ci' => $input['ci'] ?? null,
        ]);

        return $user;
    }
}
