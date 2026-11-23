<?php

namespace App\Http\Controllers;

use App\Services\SignalementNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ChauffeurController extends Controller
{
    /** Affiche les missions du chauffeur connecté et applique le filtre de statut demandé. */
    public function tableau(Request $request): View
    {
        $statutFiltre = $request->query('statut', 'tous');

        if (! is_string($statutFiltre) || ! in_array($statutFiltre, ['tous', 'en cours', 'traité'], true)) {
            $statutFiltre = 'tous';
        }

        $requete = DB::table('signalements as s')
            ->leftJoin('unites as u', 'u.id', '=', 's.unite_id')
            ->where('s.chauffeur_id', $request->user()->id)
            // PostGIS utilise ST_Y pour la latitude et ST_X pour la longitude.
            ->select(
                's.id',
                's.type_dechet',
                's.description',
                's.photo',
                's.photo_traitement',
                's.note_chauffeur',
                's.date_traitement',
                's.statut',
                's.date_signalement',
                'u.nom as unite',
                DB::raw('ST_Y(s.position) as latitude'),
                DB::raw('ST_X(s.position) as longitude')
            );

        if ($statutFiltre !== 'tous') {
            $requete->where('s.statut', $statutFiltre);
        }

        $signalements = $requete
            ->orderByDesc('s.id')
            ->paginate(15)
            ->appends(['statut' => $statutFiltre]);

        return view('chauffeur.tableau', compact('signalements', 'statutFiltre'));
    }

    /** Termine une mission après validation de la preuve et de la note du chauffeur. */
    public function terminerSignalement(Request $request, int $id, SignalementNotifier $notifier): RedirectResponse
    {
        $donnees = $request->validate([
            'photo_traitement' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'note_chauffeur' => ['nullable', 'string', 'max:500'],
        ], [
            'photo_traitement.image' => 'La preuve doit être une image.',
            'photo_traitement.mimes' => 'La preuve doit être au format JPG, PNG ou WEBP.',
            'photo_traitement.max' => 'La preuve ne doit pas dépasser 5 Mo.',
            'note_chauffeur.max' => 'La note ne doit pas dépasser 500 caractères.',
        ]);

        $chauffeurId = $request->user()->id;
        $signalement = DB::table('signalements')
            ->where('id', $id)
            ->where('chauffeur_id', $chauffeurId)
            ->first(['id', 'statut', 'photo_traitement']);

        if (! $signalement) {
            abort(404);
        }

        if ($signalement->statut !== 'en cours') {
            return back()->with('erreur', 'Ce signalement ne peut plus être marqué comme traité.');
        }

        $nouvellePhoto = $request->file('photo_traitement')?->store('traitements', 'public');
        $anciennePhoto = $signalement->photo_traitement;

        try {
            // Le statut est revérifié dans la transaction pour éviter deux traitements simultanés.
            $modifies = DB::transaction(function () use ($donnees, $id, $chauffeurId, $nouvellePhoto, $anciennePhoto): int {
                return DB::table('signalements')
                    ->where('id', $id)
                    ->where('chauffeur_id', $chauffeurId)
                    ->where('statut', 'en cours')
                    ->update([
                        'statut' => 'traité',
                        'photo_traitement' => $nouvellePhoto ?? $anciennePhoto,
                        'note_chauffeur' => filled($donnees['note_chauffeur'] ?? null)
                            ? trim($donnees['note_chauffeur'])
                            : null,
                        'date_traitement' => now(),
                        'updated_at' => now(),
                    ]);
            });
        } catch (Throwable $exception) {
            // Supprime la nouvelle photo si l’enregistrement en base a échoué.
            if ($nouvellePhoto) {
                Storage::disk('public')->delete($nouvellePhoto);
            }

            throw $exception;
        }

        if ($modifies === 0) {
            if ($nouvellePhoto) {
                Storage::disk('public')->delete($nouvellePhoto);
            }

            return back()->with('erreur', 'Le statut a changé. Actualisez la page avant de réessayer.');
        }

        if ($nouvellePhoto && $anciennePhoto) {
            Storage::disk('public')->delete($anciennePhoto);
        }

        $notifier->notifierChangementStatut($id, $signalement->statut, 'traité');

        return to_route('chauffeur.dashboard')->with('succes', 'Signalement marqué comme traité.');
    }

    /** Renvoie à l’administration une mission que le chauffeur ne peut pas réaliser. */
    public function signalerProbleme(Request $request, int $id): RedirectResponse
    {
        $donnees = $request->validate([
            'note_chauffeur' => ['required', 'string', 'max:500'],
        ], [
            'note_chauffeur.required' => 'Indiquez pourquoi la mission n’a pas pu être réalisée.',
            'note_chauffeur.max' => 'La note ne doit pas dépasser 500 caractères.',
        ]);

        $chauffeurId = $request->user()->id;
        $signalement = DB::table('signalements')
            ->where('id', $id)
            ->where('chauffeur_id', $chauffeurId)
            ->where('statut', 'en cours')
            ->first(['id']);

        if (! $signalement) {
            abort(404);
        }

        $modifies = DB::table('signalements')
            ->where('id', $id)
            ->where('chauffeur_id', $chauffeurId)
            ->where('statut', 'en cours')
            ->update([
                'statut' => 'en attente',
                'chauffeur_id' => null,
                'note_chauffeur' => trim($donnees['note_chauffeur']),
                'updated_at' => now(),
            ]);

        if ($modifies === 0) {
            return back()->with('erreur', 'Le statut a changé. Actualisez la page avant de réessayer.');
        }

        return to_route('chauffeur.dashboard')->with('succes', 'La mission a été renvoyée à l’administration.');
    }

    /** Affiche la liste des comptes chauffeur dans l’administration. */
    public function index()
    {
        $chauffeurs = DB::table('users')
            ->where('role', 'chauffeur')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.chauffeurs.index', compact('chauffeurs'));
    }

    /** Affiche le formulaire de création d’un chauffeur. */
    public function creer()
    {
        return view('admin.chauffeurs.formulaire', ['chauffeur' => null]);
    }

    /** Valide et enregistre un nouveau compte chauffeur. */
    public function enregistrer(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $donnees = $request->validate($this->regles(null), $this->messages());

        DB::table('users')->insert([
            'name' => $donnees['name'],
            'email' => $donnees['email'],
            'password' => Hash::make($donnees['password']),
            'role' => 'chauffeur',
            'telephone' => $this->formaterTelephone($donnees['telephone']),
            'numero_permis' => strtoupper($donnees['numero_permis']),
            'immatriculation' => strtoupper($donnees['immatriculation']),
            'disponible' => $request->boolean('disponible'),
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/admin/chauffeurs')->with('succes', 'Chauffeur ajouté.');
    }

    /** Charge le formulaire avec les informations du chauffeur à modifier. */
    public function modifier($id)
    {
        return view('admin.chauffeurs.formulaire', ['chauffeur' => $this->trouver($id)]);
    }

    /** Met à jour les informations du chauffeur et change son mot de passe si un nouveau est fourni. */
    public function mettreAJour(Request $request, $id)
    {
        $chauffeur = $this->trouver($id);

        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $donnees = $request->validate($this->regles($chauffeur->id), $this->messages());

        $champs = [
            'name' => $donnees['name'],
            'email' => $donnees['email'],
            'telephone' => $this->formaterTelephone($donnees['telephone']),
            'numero_permis' => strtoupper($donnees['numero_permis']),
            'immatriculation' => strtoupper($donnees['immatriculation']),
            'disponible' => $request->boolean('disponible'),
            'updated_at' => now(),
        ];

        if (! empty($donnees['password'])) {
            $champs['password'] = Hash::make($donnees['password']);
        }

        DB::table('users')->where('id', $chauffeur->id)->update($champs);

        return redirect('/admin/chauffeurs')->with('succes', 'Chauffeur modifié.');
    }

    /** Active ou désactive le compte chauffeur. */
    public function basculer($id)
    {
        $chauffeur = $this->trouver($id);
        $nouvelEtat = ! $chauffeur->actif;

        DB::table('users')->where('id', $chauffeur->id)->update([
            'actif' => $nouvelEtat,
            'updated_at' => now(),
        ]);

        if (! $nouvelEtat) {
            DB::table('sessions')->where('user_id', $chauffeur->id)->delete();
        }

        return back()->with('succes', $nouvelEtat ? 'Compte activé.' : 'Compte désactivé.');
    }

    /** Supprime le compte chauffeur et ses sessions encore ouvertes. */
    public function supprimer($id)
    {
        $chauffeur = $this->trouver($id);

        DB::table('sessions')->where('user_id', $chauffeur->id)->delete();
        DB::table('users')->where('id', $chauffeur->id)->delete();

        return redirect('/admin/chauffeurs')->with('succes', 'Chauffeur supprimé.');
    }

    /** Recherche un compte chauffeur ou renvoie une erreur 404 s’il n’existe pas. */
    private function trouver($id)
    {
        $chauffeur = DB::table('users')
            ->where('id', $id)
            ->where('role', 'chauffeur')
            ->first();

        if (! $chauffeur) {
            abort(404);
        }

        return $chauffeur;
    }

    /** Convertit un numéro local en format international sénégalais. */
    private function formaterTelephone($numero)
    {
        return '+221'.substr(preg_replace('/\D/', '', $numero), -9);
    }

    /** Définit les règles de validation, avec un mot de passe obligatoire à la création. */
    private function regles($id)
    {
        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[\p{L}][\p{L}\s\'’.-]*$/u'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($id)],
            'telephone' => ['required', 'regex:/^(\+221|00221|221)?[0-9]{9}$/'],
            'numero_permis' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9\/ -]+$/'],
            'immatriculation' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9 -]+$/'],
            'password' => [
                $id ? 'nullable' : 'required',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).{10,}$/',
            ],
        ];
    }

    /** Fournit les messages de validation en français pour le formulaire chauffeur. */
    private function messages()
    {
        return [
            'name.required' => 'Entrez le nom et le prénom.',
            'name.regex' => 'Le nom ne doit contenir que des lettres.',
            'name.max' => 'Le nom est trop long.',
            'email.required' => 'Entrez l\'adresse e-mail.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.max' => 'L\'adresse e-mail est trop longue.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'telephone.required' => 'Entrez le numéro de téléphone.',
            'telephone.regex' => 'Numéro invalide : 9 chiffres, par exemple 77 123 45 67.',
            'numero_permis.required' => 'Entrez le numéro de permis.',
            'numero_permis.max' => 'Le numéro de permis est trop long.',
            'numero_permis.regex' => 'Le numéro de permis contient des caractères non autorisés.',
            'immatriculation.required' => 'Entrez l\'immatriculation du véhicule.',
            'immatriculation.max' => 'L\'immatriculation est trop longue.',
            'immatriculation.regex' => 'L\'immatriculation ne doit contenir que des lettres, des chiffres et des tirets.',
            'password.required' => 'Entrez un mot de passe.',
            'password.confirmed' => 'La confirmation ne correspond pas au mot de passe.',
            'password.regex' => 'Le mot de passe doit contenir au moins 10 caractères, avec des lettres et des chiffres.',
        ];
    }
}
