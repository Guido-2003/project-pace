<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreatePaceAdmin extends Command
{
    protected $signature = 'pace:admin-create';
    protected $description = 'Create a new authorized Project Pace administrator interactively';

    public function handle(): int
    {
        if (!$this->input->isInteractive()) {
            $this->error('Use an interactive terminal.');
            return self::FAILURE;
        }
        $name = trim((string) $this->ask('Nombre del administrador'));
        $email = strtolower(trim((string) $this->ask('Correo electronico')));
        $validator = Validator::make(compact('name', 'email'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) $this->error($error);
            return self::FAILURE;
        }
        $password = (string) $this->secret('Contrasena (minimo 12 caracteres)', false);
        $confirmation = (string) $this->secret('Repetir contrasena', false);
        if (mb_strlen($password) < 12 || strlen($password) > 72 || $password !== $confirmation) {
            $this->error('Use entre 12 caracteres y 72 bytes; ambas contrasenas deben coincidir.');
            return self::FAILURE;
        }
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->save();
        $this->info('Administrador creado correctamente.');
        return self::SUCCESS;
    }
}
