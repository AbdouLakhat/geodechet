<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarteController extends Controller
{
    public function unites()
    {
        $lignes = DB::select('SELECT id, numero, nom, ST_AsGeoJSON(contour) AS forme FROM unites ORDER BY numero');

        $features = [];

        foreach ($lignes as $ligne) {
            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $ligne->id,
                    'numero' => $ligne->numero,
                    'nom' => $ligne->nom,
                ],
                'geometry' => json_decode($ligne->forme),
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
