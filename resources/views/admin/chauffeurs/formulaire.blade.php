@extends('admin.layout')

@section('titre', $chauffeur ? 'Modifier le chauffeur' : 'Ajouter un chauffeur')

@php
    $disponible = $errors->any()
        ? old('disponible') !== null
        : ($chauffeur ? (bool) $chauffeur->disponible : true);
@endphp

@section('contenu')
    <a href="{{ url('/admin/chauffeurs') }}" class="retour-liste">&larr; Retour aux chauffeurs</a>
    <h1>{{ $chauffeur ? 'Modifier le chauffeur' : 'Ajouter un chauffeur' }}</h1>
    <p class="total">Les champs sont tous obligatoires{{ $chauffeur ? ', sauf le mot de passe.' : '.' }}</p>

    {{-- Utilise le même formulaire pour ajouter un chauffeur ou modifier ses informations. --}}
    <form method="POST"
          action="{{ $chauffeur ? url('/admin/chauffeurs/' . $chauffeur->id) : url('/admin/chauffeurs') }}"
          class="formulaire-admin" novalidate>
        @csrf
        @if ($chauffeur)
            @method('PUT')
        @endif

        {{-- Coordonnées et informations de contact du chauffeur. --}}
        <section class="fiche">
            <h2>Identité</h2>

            <label for="name">Nom et prénom</label>
            <input type="text" name="name" id="name" maxlength="100" value="{{ old('name', $chauffeur->name ?? '') }}" class="{{ $errors->has('name') ? 'invalide' : '' }}">
            @error('name')
            <p class="message-champ">{{ $message }}</p>
            @enderror

            <label for="email">Adresse e-mail</label>
            <input type="text" name="email" id="email" maxlength="120" value="{{ old('email', $chauffeur->email ?? '') }}" class="{{ $errors->has('email') ? 'invalide' : '' }}">
            @error('email')
            <p class="message-champ">{{ $message }}</p>
            @enderror

            <label for="telephone">Téléphone</label>
            <input type="text" name="telephone" id="telephone" maxlength="20" placeholder="77 123 45 67" value="{{ old('telephone', $chauffeur->telephone ?? '') }}" class="{{ $errors->has('telephone') ? 'invalide' : '' }}">
            @error('telephone')
            <p class="message-champ">{{ $message }}</p>
            @enderror
        </section>

        {{-- Informations du véhicule, disponibilité et mot de passe du compte. --}}
        <section class="fiche">
            <h2>Véhicule et accès</h2>

            <label for="numero_permis">Numéro de permis</label>
            <input type="text" name="numero_permis" id="numero_permis" maxlength="30" value="{{ old('numero_permis', $chauffeur->numero_permis ?? '') }}" class="{{ $errors->has('numero_permis') ? 'invalide' : '' }}">
            @error('numero_permis')
            <p class="message-champ">{{ $message }}</p>
            @enderror

            <label for="immatriculation">Immatriculation du camion</label>
            <input type="text" name="immatriculation" id="immatriculation" maxlength="20" value="{{ old('immatriculation', $chauffeur->immatriculation ?? '') }}" class="{{ $errors->has('immatriculation') ? 'invalide' : '' }}">
            @error('immatriculation')
            <p class="message-champ">{{ $message }}</p>
            @enderror

            <label class="case-a-cocher">
                <input type="checkbox" name="disponible" value="1" @checked($disponible)>
                <span>Disponible pour recevoir des signalements</span>
            </label>

            <label for="password">{{ $chauffeur ? 'Nouveau mot de passe' : 'Mot de passe provisoire' }}</label>
            <input type="password" name="password" id="password" autocomplete="new-password" class="{{ $errors->has('password') ? 'invalide' : '' }}">
            <p class="indication">10 caractères minimum, avec des lettres et des chiffres.{{ $chauffeur ? ' Laissez vide pour ne pas le changer.' : '' }}</p>
            @error('password')
            <p class="message-champ">{{ $message }}</p>
            @enderror

            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password">
        </section>

        <div class="actions-formulaire">
            <button type="submit" class="btn-vert">{{ $chauffeur ? 'Enregistrer' : 'Ajouter le chauffeur' }}</button>
            <a href="{{ url('/admin/chauffeurs') }}" class="btn-gris">Annuler</a>
        </div>
    </form>
@endsection
