<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GéoDéchet</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icone.png') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/carte.css') }}?v={{ filemtime(public_path('css/carte.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fond-carte.css') }}">
</head>
<body>
{{-- Barre du haut avec le retour à l’accueil et l’accès au signalement. --}}
<header class="bandeau">
    <div class="bandeau-gauche">
        <a class="retour" href="{{ url('/') }}">&larr;</a>
        <strong>GéoDéchet</strong>
    </div>
    <button id="btn-signaler" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 21s-6.5-5.5-6.5-11a6.5 6.5 0 1 1 13 0c0 5.5-6.5 11-6.5 11Z"/>
            <circle cx="12" cy="10" r="2.4"/>
        </svg>
        <span>Signaler un dépôt</span>
    </button>
</header>

<main id="zone-carte">
    {{-- La carte Leaflet occupe l’espace principal de la page. --}}
    <div id="carte"></div>
    <div id="message" role="status" aria-live="polite"></div>

    {{-- Premier choix de position : GPS de l’appareil ou clic sur la carte. --}}
    <div id="choix" class="cache">
        <h3>Où se trouve le dépôt ?</h3>
        <button id="btn-choix-position" class="choix-position" type="button">
            <span class="icone-choix" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 21s-6.5-5.5-6.5-11a6.5 6.5 0 1 1 13 0c0 5.5-6.5 11-6.5 11Z"/>
                    <circle cx="12" cy="10" r="2.4"/>
                    <path d="M3 5V3h2M21 5V3h-2M3 19v2h2M21 19v2h-2"/>
                </svg>
            </span>
            <span>Utiliser ma position</span>
        </button>
        <button id="btn-choix-carte" class="choix-carte" type="button">
            <span class="icone-choix" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 6 5-3 8 3 5-3v15l-5 3-8-3-5 3z"/>
                    <path d="M8 3v15M16 6v15"/>
                    <path d="M12 16s-2.8-2.5-2.8-4.8a2.8 2.8 0 1 1 5.6 0C14.8 13.5 12 16 12 16Z"/>
                    <circle cx="12" cy="11.2" r=".9"/>
                </svg>
            </span>
            <span>Choisir sur la carte</span>
        </button>
        <p id="choix-info"></p>
    </div>

    {{-- Formulaire affiché après le choix de la position du dépôt. --}}
    <aside id="panneau" class="cache" aria-labelledby="titre-panneau">
        <h3 id="titre-panneau">Nouveau signalement</h3>
        <p>Position : <span id="coords"></span></p>
        <p class="astuce">Vous pouvez déplacer le repère pour corriger la position.</p>

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
        <fieldset class="coordonnees">
            <legend>Vos coordonnées *</legend>

            <label for="nom">Nom</label>
            <input type="text" id="nom" maxlength="60" autocomplete="family-name">

            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" maxlength="60" autocomplete="given-name">

            <label for="telephone">Téléphone</label>
            <input type="tel" id="telephone" maxlength="20" autocomplete="tel" placeholder="77 123 45 67">

            <label for="email">Adresse e-mail</label>
            <input type="email" id="email" maxlength="120" autocomplete="email">

            <label class="consentement">
                <input type="checkbox" id="consentement">
                <span>J'accepte que mon nom, mon téléphone et mon e-mail soient utilisés pour traiter mon signalement. Ils ne sont visibles que par l'administration.</span>
            </label>
        </fieldset>
        <p id="erreur-form" role="alert" aria-live="assertive"></p>

        <div class="boutons">
            <button id="btn-envoyer" type="button">Envoyer</button>
            <button id="btn-annuler" type="button">Annuler</button>
        </div>
    </aside>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/fond-carte.js') }}"></script>
<script src="{{ asset('js/carte.js') }}"></script>
</body>
</html>
