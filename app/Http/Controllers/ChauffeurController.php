<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ChauffeurController extends Controller
{
    public function index()
    {
        $chauffeurs = DB::table('users')
            ->where('role', 'chauffeur')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.chauffeurs.index', compact('chauffeurs'));
    }

    public function creer()
    {
        return view('admin.chauffeurs.formulaire', ['chauffeur' => null]);
    }

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

    public function modifier($id)
    {
        return view('admin.chauffeurs.formulaire', ['chauffeur' => $this->trouver($id)]);
    }

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

        if (!empty($donnees['password'])) {
            $champs['password'] = Hash::make($donnees['password']);
        }

        DB::table('users')->where('id', $chauffeur->id)->update($champs);

        return redirect('/admin/chauffeurs')->with('succes', 'Chauffeur modifié.');
    }

    public function basculer($id)
    {
        $chauffeur = $this->trouver($id);
        $nouvelEtat = !$chauffeur->actif;

        DB::table('users')->where('id', $chauffeur->id)->update([
            'actif' => $nouvelEtat,
            'updated_at' => now(),
        ]);

        if (!$nouvelEtat) {
            DB::table('sessions')->where('user_id', $chauffeur->id)->delete();
        }

        return back()->with('succes', $nouvelEtat ? 'Compte activé.' : 'Compte désactivé.');
    }

    public function supprimer($id)
    {
        $chauffeur = $this->trouver($id);

        DB::table('sessions')->where('user_id', $chauffeur->id)->delete();
        DB::table('users')->where('id', $chauffeur->id)->delete();

        return redirect('/admin/chauffeurs')->with('succes', 'Chauffeur supprimé.');
    }

    private function trouver($id)
    {
        $chauffeur = DB::table('users')
            ->where('id', $id)
            ->where('role', 'chauffeur')
            ->first();

        if (!$chauffeur) {
            abort(404);
        }

        return $chauffeur;
    }

    private function formaterTelephone($numero)
    {
        return '+221' . substr(preg_replace('/\D/', '', $numero), -9);
    }

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
