<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AccueilController extends Controller
{
    /** Récupère les chiffres affichés sur la page d’accueil. */
    public function index()
    {
        $nbUnites = DB::table('unites')->count();
        $nbSignalements = DB::table('signalements')->count();
        $nbUnitesTouchees = DB::table('signalements')
            ->whereNotNull('unite_id')
            ->distinct()
            ->count('unite_id');

        return view('accueil', compact('nbUnites', 'nbSignalements', 'nbUnitesTouchees'));
    }
}
