<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $motDePasse = env('ADMIN_PASSWORD');

        if (!$email || !$motDePasse) {
            $this->command->error('ADMIN_EMAIL et ADMIN_PASSWORD manquent dans le fichier .env');
            return;
        }

        $admin = User::where('email', $email)->first();

        if (!$admin) {
            $admin = new User();
            $admin->email = $email;
        }

        $admin->name = 'Administrateur';
        $admin->password = Hash::make($motDePasse);
        $admin->role = 'administrateur';
        $admin->save();
    }
}
