<?php

namespace App\Http\Controllers;

use App\Services\SignalementNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /** Prépare les compteurs, graphiques et derniers signalements du tableau de bord. */
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

        // Affiche le mois actuel et les cinq mois qui le précèdent.
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

    /** Affiche la carte réservée à l’administration. */
    public function carte()
    {
        return view('admin.carte');
    }

    /** Renvoie les signalements au format GeoJSON pour les afficher sur la carte. */
    public function donneesSignalements()
    {
        $lignes = DB::select(
            'SELECT s.id, s.type_dechet, s.description, s.photo, s.statut, s.date_signalement,
                u.nom AS unite, ST_AsGeoJSON(s.position) AS forme
         FROM signalements s
         LEFT JOIN unites u ON u.id = s.unite_id
         ORDER BY s.id DESC'
        );

        $features = [];

        foreach ($lignes as $ligne) {
            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $ligne->id,
                    'type_dechet' => $ligne->type_dechet,
                    'description' => $ligne->description,
                    'statut' => $ligne->statut,
                    'date_signalement' => $ligne->date_signalement,
                    'unite' => $ligne->unite,
                    'photo' => $ligne->photo ? asset('storage/'.$ligne->photo) : null,
                ],
                'geometry' => json_decode($ligne->forme),
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /** Affiche la liste paginée et les chauffeurs disponibles pour l’affectation. */
    public function signalements()
    {
        $signalements = DB::table('signalements as s')
            ->leftJoin('unites as u', 'u.id', '=', 's.unite_id')
            ->leftJoin('users as c', 'c.id', '=', 's.chauffeur_id')
            ->select('s.id', 's.type_dechet', 's.description', 's.photo', 's.note_chauffeur', 's.statut', 's.date_signalement', 'u.nom as unite', 's.chauffeur_id', 'c.name as chauffeur')
            ->orderByDesc('s.id')
            ->paginate(15);

        $chauffeurs = DB::table('users')
            ->where('role', 'chauffeur')
            ->where('actif', true)
            ->where('disponible', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.signalements', compact('signalements', 'chauffeurs'));
    }

    /** Charge les informations détaillées d’un signalement. */
    public function detail($id)
    {
        $signalement = DB::selectOne(
            'SELECT s.id, s.type_dechet, s.description, s.photo, s.photo_traitement, s.note_chauffeur, s.date_traitement, s.statut, s.date_signalement,
                s.nom, s.prenom, s.telephone, s.email, s.consentement_le,
                u.nom AS unite,
                ST_Y(s.position) AS latitude, ST_X(s.position) AS longitude
         FROM signalements s
         LEFT JOIN unites u ON u.id = s.unite_id
         WHERE s.id = ?',
            [$id]
        );

        if (! $signalement) {
            abort(404);
        }

        return view('admin.detail', compact('signalement'));
    }

    /** Ouvre le formulaire de modification d’un signalement existant. */
    public function modifier($id)
    {
        $signalement = DB::table('signalements')->where('id', $id)->first();

        if (! $signalement) {
            abort(404);
        }

        return view('admin.modifier', compact('signalement'));
    }

    /** Valide les changements, met à jour le signalement et prévient le citoyen si son statut change. */
    public function mettreAJour(Request $request, $id, SignalementNotifier $notifier)
    {
        $signalement = DB::table('signalements')->where('id', $id)->first();

        if (! $signalement) {
            abort(404);
        }

        $presence = $signalement->nom ? 'required' : 'nullable';
        $motifNom = 'regex:/^[\p{L}][\p{L}\s\'’-]*$/u';

        $donnees = $request->validate([
            'type_dechet' => 'required|in:ordures_menageres,encombrants,gravats,dechets_verts,autre',
            'statut' => 'required|in:en attente,en cours,traité',
            'description' => 'nullable|string|max:500',
            'nom' => [$presence, 'string', 'max:60', $motifNom],
            'prenom' => [$presence, 'string', 'max:60', $motifNom],
            'telephone' => [$presence, 'regex:/^(\+221|00221|221)?[0-9]{9}$/'],
            'email' => [$presence, 'email', 'max:120'],
        ], [
            'type_dechet.required' => 'Choisissez un type de déchet.',
            'type_dechet.in' => 'Ce type de déchet n\'est pas valide.',
            'statut.required' => 'Choisissez un statut.',
            'statut.in' => 'Ce statut n\'est pas valide.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
            'nom.required' => 'Entrez le nom.',
            'nom.regex' => 'Le nom ne doit contenir que des lettres.',
            'nom.max' => 'Le nom est trop long.',
            'prenom.required' => 'Entrez le prénom.',
            'prenom.regex' => 'Le prénom ne doit contenir que des lettres.',
            'prenom.max' => 'Le prénom est trop long.',
            'telephone.required' => 'Entrez le numéro de téléphone.',
            'telephone.regex' => 'Numéro invalide : 9 chiffres, par exemple 77 123 45 67.',
            'email.required' => 'Entrez l\'adresse e-mail.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.max' => 'L\'adresse e-mail est trop longue.',
        ]);

        $telephone = null;
        if (! empty($donnees['telephone'])) {
            // Enregistre les numéros sénégalais dans un format unique.
            $telephone = '+221'.substr(preg_replace('/\D/', '', $donnees['telephone']), -9);
        }

        DB::table('signalements')->where('id', $id)->update([
            'type_dechet' => $donnees['type_dechet'],
            'statut' => $donnees['statut'],
            'date_traitement' => $donnees['statut'] === 'traité' ? now() : null,
            'description' => $donnees['description'] ?? null,
            'nom' => $donnees['nom'] ?? null,
            'prenom' => $donnees['prenom'] ?? null,
            'telephone' => $telephone,
            'email' => isset($donnees['email']) ? strtolower($donnees['email']) : null,
            'updated_at' => now(),
        ]);
        $notifier->notifierChangementStatut((int) $id, $signalement->statut, $donnees['statut']);

        return redirect('/admin/signalements/'.$id)->with('succes', 'Signalement '.$id.' modifié.');
    }

    /** Met à jour le statut et envoie la notification correspondante au citoyen. */
    public function changerStatut(Request $request, $id, SignalementNotifier $notifier)
    {
        $donnees = $request->validate([
            'statut' => 'required|in:en attente,en cours,traité',
        ], [
            'statut.required' => 'Choisissez un statut.',
            'statut.in' => 'Ce statut n\'est pas valide.',
        ]);

        $ancienStatut = DB::table('signalements')->where('id', $id)->value('statut');

        $modifies = DB::table('signalements')
            ->where('id', $id)
            ->update([
                'statut' => $donnees['statut'],
                'date_traitement' => $donnees['statut'] === 'traité' ? now() : null,
                'updated_at' => now(),
            ]);

        if ($modifies === 0) {
            abort(404);
        }

        $notifier->notifierChangementStatut((int) $id, $ancienStatut, $donnees['statut']);

        return back()->with('succes', 'Statut du signalement '.$id.' mis à jour.');
    }

    /** Vérifie le chauffeur, lui attribue la mission et lui envoie les détails par e-mail. */
    public function affecterChauffeur(Request $request, $id, SignalementNotifier $notifier)
    {
        $donnees = $request->validate([
            'chauffeur_id' => 'required|integer',
        ], [
            'chauffeur_id.required' => 'Choisissez un chauffeur.',
            'chauffeur_id.integer' => 'Ce chauffeur n\'est pas valide.',
        ]);

        $chauffeur = DB::table('users')
            ->where('id', $donnees['chauffeur_id'])
            ->where('role', 'chauffeur')
            ->where('actif', true)
            ->where('disponible', true)
            ->first();

        if (! $chauffeur) {
            return back()->withErrors(['chauffeur_id' => 'Ce chauffeur n\'est pas disponible.']);
        }
        $signalementExistant = DB::table('signalements')
            ->where('id', $id)
            ->first(['id', 'statut', 'photo_traitement']);

        if (! $signalementExistant) {
            abort(404);
        }

        $ancienStatut = $signalementExistant->statut;
        $modifies = DB::table('signalements')
            ->where('id', $id)
            ->update([
                'chauffeur_id' => $chauffeur->id,
                'statut' => 'en cours',
                'photo_traitement' => null,
                'note_chauffeur' => null,
                'date_traitement' => null,
                'updated_at' => now(),
            ]);

        if ($modifies === 0) {
            abort(404);
        }

        if ($signalementExistant->photo_traitement) {
            Storage::disk('public')->delete($signalementExistant->photo_traitement);
        }

        $notifier->notifierChangementStatut((int) $id, $ancienStatut, 'en cours');

        // Récupère les informations de la mission qui seront incluses dans l’e-mail.
        $signalement = DB::table('signalements as s')
            ->leftJoin('unites as u', 'u.id', '=', 's.unite_id')
            ->where('s.id', $id)
            ->select(
                's.id', 's.type_dechet', 's.description', 'u.nom as unite',
                DB::raw('ST_Y(s.position) as latitude'),
                DB::raw('ST_X(s.position) as longitude')
            )
            ->first();
        $type = ucfirst(str_replace('_', ' ', $signalement->type_dechet));

        Mail::send('emails.affectation', compact('chauffeur', 'signalement', 'type'), function ($message) use ($chauffeur, $signalement) {
            $message->to($chauffeur->email)
                ->subject('GéoDéchet : signalement n° '.$signalement->id.' affecté');
        });

        return back()->with('succes', 'Signalement '.$id.' affecté à '.$chauffeur->name.'.');
    }

    /** Supprime le signalement ainsi que les photos qui lui sont associées. */
    public function supprimer($id)
    {
        $signalement = DB::table('signalements')->where('id', $id)->first();

        if (! $signalement) {
            abort(404);
        }

        DB::table('signalements')->where('id', $id)->delete();

        if ($signalement->photo) {
            Storage::disk('public')->delete($signalement->photo);
        }

        if ($signalement->photo_traitement) {
            Storage::disk('public')->delete($signalement->photo_traitement);
        }

        return redirect('/admin/signalements')->with('succes', 'Signalement '.$id.' supprimé.');
    }
}
