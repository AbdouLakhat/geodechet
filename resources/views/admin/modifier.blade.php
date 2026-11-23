@extends('admin.layout')

@section('titre', 'Modifier le signalement ' . $signalement->id)

@section('contenu')
    <a href="{{ url('/admin/signalements/' . $signalement->id) }}" class="retour-liste">&larr; Retour au détail</a>
    <h1>Modifier le signalement n° {{ $signalement->id }}</h1>
    <p class="total">La position et la photo ne peuvent pas être modifiées.</p>

    {{-- Le formulaire permet de corriger les informations sans changer la position ni la photo. --}}
    <form method="POST" action="{{ url('/admin/signalements/' . $signalement->id) }}" class="formulaire-admin" novalidate>
        @csrf
        @method('PUT')

        <section class="fiche">
            <h2>Le signalement</h2>

            <label for="type_dechet">Type de déchet</label>
            <select name="type_dechet" id="type_dechet" class="{{ $errors->has('type_dechet') ? 'invalide' : '' }}">
                @foreach (['ordures_menageres' => 'Ordures ménagères', 'encombrants' => 'Encombrants', 'gravats' => 'Gravats', 'dechets_verts' => 'Déchets verts', 'autre' => 'Autre'] as $valeur => $libelle)
                    <option value="{{ $valeur }}" @selected(old('type_dechet', $signalement->type_dechet) === $valeur)>{{ $libelle }}</option>
                @endforeach
            </select>
            @error('type_dechet')
            <p class="message-champ">{{ $message }}</p>
            @enderror

            <label for="statut">Statut</label>
            <select name="statut" id="statut" class="{{ $errors->has('statut') ? 'invalide' : '' }}">
                @foreach (['en attente', 'en cours', 'traité'] as $option)
                    <option value="{{ $option }}" @selected(old('statut', $signalement->statut) === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
            @error('statut')
            <p class="message-champ">{{ $message }}</p>
            @enderror

            <label for="description">Description</label>
            <textarea name="description" id="description" maxlength="500" class="{{ $errors->has('description') ? 'invalide' : '' }}">{{ old('description', $signalement->description) }}</textarea>
            @error('description')
            <p class="message-champ">{{ $message }}</p>
            @enderror
        </section>

        <section class="fiche">
            <h2>Le citoyen</h2>

            @if (!$signalement->nom)
                <p class="vide" style="text-align:left;">Ce signalement n'a pas de coordonnées. Vous pouvez en ajouter, ou laisser les champs vides.</p>
            @endif

            <label for="nom">Nom</label>
            <input type="text" name="nom" id="nom" maxlength="60" value="{{ old('nom', $signalement->nom) }}" class="{{ $errors->has('nom') ? 'invalide' : '' }}">
            @error('nom')
            <p class="message-champ">{{ $message }}</p>
            @enderror

            <label for="prenom">Prénom</label>
            <input type="text" name="prenom" id="prenom" maxlength="60" value="{{ old('prenom', $signalement->prenom) }}" class="{{ $errors->has('prenom') ? 'invalide' : '' }}">
            @error('prenom')
            <p class="message-champ">{{ $message }}</p>
            @enderror

            <label for="telephone">Téléphone</label>
            <input type="text" name="telephone" id="telephone" maxlength="20" value="{{ old('telephone', $signalement->telephone) }}" class="{{ $errors->has('telephone') ? 'invalide' : '' }}">
            @error('telephone')
            <p class="message-champ">{{ $message }}</p>
            @enderror

            <label for="email">Adresse e-mail</label>
            <input type="text" name="email" id="email" maxlength="120" value="{{ old('email', $signalement->email) }}" class="{{ $errors->has('email') ? 'invalide' : '' }}">
            @error('email')
            <p class="message-champ">{{ $message }}</p>
            @enderror
        </section>

        <div class="actions-formulaire">
            <button type="submit" class="btn-vert">Enregistrer</button>
            <a href="{{ url('/admin/signalements/' . $signalement->id) }}" class="btn-gris">Annuler</a>
        </div>
    </form>
@endsection
