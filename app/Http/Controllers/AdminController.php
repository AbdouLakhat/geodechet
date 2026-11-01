<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function tableau()
    {
        // Compteurs généraux
        $total = DB::table('signalements')->count();

        $parStatut = DB::table('signalements')
            ->select('statut', DB::raw('count(*) as nombre'))
            ->groupBy('statut')
            ->pluck('nombre', 'statut');

        $enAttente = $parStatut['en attente'] ?? 0;
        $enCours = $parStatut['en cours'] ?? 0;
        $traites = $parStatut['traité'] ?? 0;

        $unitesTouchees = DB::table('signalements')
            ->whereNotNull('unite_id')
            ->distinct()
            ->count('unite_id');

        // Répartition par type de déchet
        $parType = DB::table('signalements')
            ->select('type_dechet', DB::raw('count(*) as nombre'))
            ->groupBy('type_dechet')
            ->orderByDesc('nombre')
            ->get();

        // Signalements par unité (quartiers les plus touchés)
        $parUnite = DB::table('signalements as s')
            ->leftJoin('unites as u', 'u.id', '=', 's.unite_id')
            ->select(DB::raw("coalesce(u.nom, 'Hors des Unités') as unite"), DB::raw('count(*) as nombre'))
            ->groupBy('u.nom')
            ->orderByDesc('nombre')
            ->get();

        // Évolution sur les 6 derniers mois
        $parMois = DB::table('signalements')
            ->select(DB::raw("to_char(date_signalement, 'YYYY-MM') as mois"), DB::raw('count(*) as nombre'))
            ->where('date_signalement', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('nombre', 'mois');

        // 5 derniers signalements
        $derniers = DB::table('signalements as s')
            ->leftJoin('unites as u', 'u.id', '=', 's.unite_id')
            ->select('s.id', 's.type_dechet', 's.statut', 's.date_signalement', 'u.nom as unite')
            ->orderByDesc('s.id')
            ->limit(5)
            ->get();

        return view('admin.tableau', compact(
            'total', 'enAttente', 'enCours', 'traites', 'unitesTouchees',
            'parType', 'parUnite', 'parMois', 'derniers'
        ));
    }
    public function carte()
    {
        return view('admin.carte');
    }
    public function signalements()
    {
        $signalements = DB::table('signalements as s')
            ->leftJoin('unites as u', 'u.id', '=', 's.unite_id')
            ->select('s.id', 's.type_dechet', 's.description', 's.photo', 's.statut', 's.date_signalement', 'u.nom as unite')
            ->orderByDesc('s.id')
            ->paginate(15);

        return view('admin.signalements', compact('signalements'));
    }

    public function changerStatut(Request $request, $id)
    {
        $donnees = $request->validate([
            'statut' => 'required|in:en attente,en cours,traité',
        ], [
            'statut.required' => 'Choisissez un statut.',
            'statut.in' => 'Ce statut n\'est pas valide.',
        ]);

        $modifies = DB::table('signalements')
            ->where('id', $id)
            ->update(['statut' => $donnees['statut'], 'updated_at' => now()]);

        if ($modifies === 0) {
            abort(404);
        }

        return back()->with('succes', 'Statut du signalement ' . $id . ' mis à jour.');
    }

    public function supprimer($id)
    {
        $signalement = DB::table('signalements')->where('id', $id)->first();

        if (!$signalement) {
            abort(404);
        }

        DB::table('signalements')->where('id', $id)->delete();

        if ($signalement->photo) {
            Storage::disk('public')->delete($signalement->photo);
        }

        return redirect('/admin/signalements')->with('succes', 'Signalement ' . $id . ' supprimé.');
    }
}
