<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CrearAdministrador extends Command
{
    protected $signature = 'boxping:crear-administrador';

    protected $description = 'Crea de forma segura una cuenta administradora de Boxping';

    public function handle(): int
    {
        $datos = [
            'name' => $this->ask('Nombre del administrador'),
            'email' => $this->ask('Correo electrónico'),
            'password' => $this->secret('Contraseña (mínimo 12 caracteres)'),
            'password_confirmation' => $this->secret('Confirma la contraseña'),
        ];

        $validator = Validator::make($datos, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'is_admin' => true,
        ]);

        $this->info('Administrador creado. Ya puede iniciar sesión en /admin.');

        return self::SUCCESS;
    }
}
