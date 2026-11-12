@extends('admin.layout')

@section('titre', 'Carte')

@section('contenu')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/fond-carte.css') }}">

    <h1>Carte</h1>
    <p class="total">Unités et signalements de la commune</p>

    <div class="barre-filtres">
        <label>Type
            <select id="filtre-type">
                <option value="">Tous les types</option>
                <option value="ordures_menageres">Ordures ménagères</option>
                <option value="encombrants">Encombrants</option>
                <option value="gravats">Gravats</option>
                <option value="dechets_verts">Déchets verts</option>
                <option value="autre">Autre</option>
            </select>
        </label>

        <label>Statut
            <select id="filtre-statut">
                <option value="">Tous les statuts</option>
                <option value="en attente">En attente</option>
                <option value="en cours">En cours</option>
                <option value="traité">Traité</option>
            </select>
        </label>

        <button type="button" id="btn-reinitialiser">Réinitialiser</button>
        <span id="compteur"></span>
    </div>

    <div id="carte-admin"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/fond-carte.js') }}"></script>
    <script src="{{ asset('js/admin-carte.js') }}"></script>
@endsection
