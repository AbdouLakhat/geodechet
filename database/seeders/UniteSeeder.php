<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UniteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fichier = file_get_contents(database_path('data/unites.geojson'));
        $donnees = json_decode($fichier, true);

        foreach ($donnees['features'] as $unite) {
            $nom = $unite['properties']['name'];
            $numero = (int) preg_replace('/[^0-9]/', '', $nom);
            $forme = json_encode($unite['geometry']);

            DB::insert(
                'INSERT INTO unites (numero, nom, contour, created_at, updated_at)
             VALUES (?, ?, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), now(), now())',
                [$numero, 'Unité ' . $numero, $forme]
            );
        }
    }
}
