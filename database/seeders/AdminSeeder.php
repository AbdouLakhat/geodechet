<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /** Crée ou met à jour le compte administrateur à partir de la configuration. */
    public function run(): void
    {
        $email = config('geodechet.administrateur.email');
        $motDePasse = config('geodechet.administrateur.password');

        if (! $email || ! $motDePasse) {
            $this->command->error('ADMIN_EMAIL et ADMIN_PASSWORD manquent dans le fichier .env');

            return;
        }

        $admin = User::where('email', $email)->first();

        if (! $admin) {
            $admin = new User;
            $admin->email = $email;
        }

        $admin->name = 'Administrateur';
        $admin->password = Hash::make($motDePasse);
        $admin->role = 'administrateur';
        $admin->save();
    }
}
