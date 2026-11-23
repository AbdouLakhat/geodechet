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
        // Charge les limites des unités depuis le fichier GeoJSON du projet.
        $fichier = file_get_contents(database_path('data/unites.geojson'));
        $donnees = json_decode($fichier, true);

        // Parcourt les zones GeoJSON pour les enregistrer une par une.
        foreach ($donnees['features'] as $unite) {
            $nom = $unite['properties']['name'];
            // Récupère le numéro de l’unité à partir de son nom dans le fichier GeoJSON.
            $numero = (int) preg_replace('/[^0-9]/', '', $nom);
            $forme = json_encode($unite['geometry']);

            $uniteExistante = DB::table('unites')->where('numero', $numero)->first(['id']);

            if ($uniteExistante) {
                // Met à jour l’unité déjà présente pour éviter de créer un doublon.
                DB::statement(
                    'UPDATE unites
                     SET nom = ?, contour = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), updated_at = now()
                     WHERE id = ?',
                    ['Unité '.$numero, $forme, $uniteExistante->id]
                );

                continue;
            }

            DB::insert(
                'INSERT INTO unites (numero, nom, contour, created_at, updated_at)
                 VALUES (?, ?, ST_SetSRID(ST_GeomFromGeoJSON(?), 4326), now(), now())',
                [$numero, 'Unité '.$numero, $forme]
            );
        }
    }
}
