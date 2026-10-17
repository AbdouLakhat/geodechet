<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GéoDéchet</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/carte.css') }}">
</head>
<body>
<header class="bandeau">
    <div><strong>GéoDéchet</strong><span id="compteur"></span></div>
    <button id="btn-signaler" type="button">Signaler un dépôt</button>
</header>

<main id="zone-carte">
    <div id="carte"></div>
    <div id="message"></div>

    <aside id="panneau" class="cache">
        <h3>Nouveau signalement</h3>
        <p>Position : <span id="coords"></span></p>

        <label for="type_dechet">Type de déchet *</label>
        <select id="type_dechet">
            <option value="">-- Choisir --</option>
            <option value="ordures_menageres">Ordures ménagères</option>
            <option value="encombrants">Encombrants</option>
            <option value="gravats">Gravats</option>
            <option value="dechets_verts">Déchets verts</option>
            <option value="autre">Autre</option>
        </select>

        <label for="description">Description (facultatif)</label>
        <textarea id="description" maxlength="500" placeholder="500 caractères maximum"></textarea>
        <label for="photo">Photo (facultatif)</label>
        <input type="file" id="photo" accept="image/jpeg,image/png,image/webp">
        <img id="apercu" alt="Aperçu de la photo">

        <p id="erreur-form"></p>

        <div class="boutons">
            <button id="btn-envoyer" type="button">Envoyer</button>
            <button id="btn-annuler" type="button">Annuler</button>
        </div>
    </aside>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/carte.js') }}"></script>
</body>
</html>
