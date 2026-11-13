<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ChauffeurSeeder extends Seeder
{
    public function run(): void
    {
        $motDePasse = env('CHAUFFEUR_PASSWORD');

        if (!$motDePasse) {
            $this->command->error('Ajoutez CHAUFFEUR_PASSWORD dans le fichier .env');
            return;
        }

        $chauffeurs = [
            ['Moussa Diallo', 'moussa.diallo@geodechet.test', '+221770000001', 'SN-PC-2019-0101', 'DK-4521-AB', true, true],
            ['Aminata Sow', 'aminata.sow@geodechet.test', '+221770000002', 'SN-PC-2020-0202', 'DK-7834-CD', true, true],
            ['Cheikh Ba', 'cheikh.ba@geodechet.test', '+221770000003', 'SN-PC-2018-0303', 'DK-1296-EF', true, true],
            ['Fatou Sarr', 'fatou.sarr@geodechet.test', '+221770000004', 'SN-PC-2021-0404', 'DK-5503-GH', false, true],
            ['Ibrahima Ndiaye', 'ibrahima.ndiaye@geodechet.test', '+221770000005', 'SN-PC-2017-0505', 'DK-9088-IJ', true, false],
        ];

        foreach ($chauffeurs as $c) {
            DB::table('users')->updateOrInsert(
                ['email' => $c[1]],
                [
                    'name' => $c[0],
                    'password' => Hash::make($motDePasse),
                    'telephone' => $c[2],
                    'role' => 'chauffeur',
                    'numero_permis' => $c[3],
                    'immatriculation' => $c[4],
                    'disponible' => $c[5],
                    'actif' => $c[6],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
