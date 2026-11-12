<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SignalementController extends Controller
{
    public function index()
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
                    'photo' => $ligne->photo ? asset('storage/' . $ligne->photo) : null,
                    'statut' => $ligne->statut,
                    'date_signalement' => $ligne->date_signalement,
                    'unite' => $ligne->unite,
                ],
                'geometry' => json_decode($ligne->forme),
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'type_dechet' => 'required|string|in:ordures_menageres,encombrants,gravats,dechets_verts,autre',
            'description' => 'nullable|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'nom' => ['required', 'string', 'max:60', 'regex:/^[\p{L}][\p{L}\s\'’-]*$/u'],
            'prenom' => ['required', 'string', 'max:60', 'regex:/^[\p{L}][\p{L}\s\'’-]*$/u'],
            'telephone' => ['required', 'regex:/^(\+221|00221|221)?[0-9]{9}$/'],
            'email' => 'required|email|max:120',
            'consentement' => 'accepted',
        ], [
            'type_dechet.required' => 'Choisis un type de déchet.',
            'type_dechet.in' => 'Ce type de déchet n\'est pas valide.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
            'latitude.required' => 'Choisis d\'abord la position du dépôt.',
            'longitude.required' => 'Choisis d\'abord la position du dépôt.',
            'latitude.between' => 'La position n\'est pas valide.',
            'longitude.between' => 'La position n\'est pas valide.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'La photo doit être au format JPG, PNG ou WEBP.',
            'photo.max' => 'La photo ne doit pas dépasser 5 Mo.',
            'nom.required' => 'Entrez votre nom.',
            'nom.regex' => 'Le nom ne doit contenir que des lettres.',
            'nom.max' => 'Le nom est trop long.',
            'prenom.required' => 'Entrez votre prénom.',
            'prenom.regex' => 'Le prénom ne doit contenir que des lettres.',
            'prenom.max' => 'Le prénom est trop long.',
            'telephone.required' => 'Entrez votre numéro de téléphone.',
            'telephone.regex' => 'Entrez un numéro valide : 9 chiffres, par exemple 77 123 45 67.',
            'email.required' => 'Entrez votre adresse e-mail.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.max' => 'L\'adresse e-mail est trop longue.',
            'consentement.accepted' => 'Vous devez accepter l\'utilisation de vos informations.',
        ]);

        $unite = DB::selectOne(
            'SELECT id, nom
         FROM unites
         WHERE ST_DWithin(contour::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, 30)
         ORDER BY ST_Distance(contour::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography)
         LIMIT 1',
            [$donnees['longitude'], $donnees['latitude'], $donnees['longitude'], $donnees['latitude']]
        );

        if (!$unite) {
            return response()->json([
                'message' => 'Position hors de la commune.',
                'errors' => [
                    'position' => ['Cette position est en dehors de la commune des Parcelles Assainies.'],
                ],
            ], 422);
        }

        $chemin = null;
        if ($request->hasFile('photo')) {
            $chemin = $request->file('photo')->store('signalements', 'public');
        }

        $telephone = '+221' . substr(preg_replace('/\D/', '', $donnees['telephone']), -9);

        DB::insert(
            'INSERT INTO signalements
            (unite_id, type_dechet, description, photo, statut, nom, prenom, telephone, email, consentement_le, date_signalement, position, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, now(), now(), ST_SetSRID(ST_MakePoint(?, ?), 4326), now(), now())',
            [
                $unite->id,
                $donnees['type_dechet'],
                $donnees['description'] ?? null,
                $chemin,
                'en attente',
                $donnees['nom'],
                $donnees['prenom'],
                $telephone,
                strtolower($donnees['email']),
                $donnees['longitude'],
                $donnees['latitude'],
            ]
        );

        return response()->json(['message' => 'Signalement enregistré dans ' . $unite->nom]);
    }
}
