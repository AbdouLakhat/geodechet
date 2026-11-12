<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GéoDéchet – Gestion des déchets urbains</title>
    <link rel="stylesheet" href="{{ asset('css/accueil.css') }}">
</head>
<body>
<header class="entete">
    <a class="logo" href="{{ url('/') }}">
        <img src="{{ asset('images/logo-icone.png') }}" alt="" class="logo-icone">
        <span>GéoDéchet</span>
    </a>
    <nav>
        <a href="#chiffres">Chiffres</a>
        <a href="#fonctionnement">Comment ça marche</a>
        <a class="bouton-nav" href="{{ url('/carte') }}">Voir la carte</a>
    </nav>
</header>

<main>
    <section class="banniere">
        <div class="fond"></div>

        <div class="contenu">
            <p class="sur-titre">Commune des Parcelles Assainies</p>
            <h1>Signalez un dépôt de déchets, aidez votre quartier à rester propre</h1>
            <p class="texte">
                GéoDéchet permet aux habitants de signaler un dépôt de déchets en quelques clics,
                directement sur la carte des 20 Unités de la commune.
            </p>
            <div class="actions">
                <a class="btn btn-plein" href="{{ url('/carte?signaler=1') }}">Signaler un dépôt</a>
            </div>
        </div>

        <a class="fleche" href="#chiffres" aria-label="Descendre">&#8595;</a>
    </section>
    <section class="chiffres" id="chiffres">
        <h2>La commune en chiffres</h2>
        <div class="grille-chiffres">
            <div class="carte-chiffre">
                <span class="nombre" data-valeur="{{ $nbUnites }}">{{ $nbUnites }}</span>
                <p>Unités cartographiées</p>
            </div>
            <div class="carte-chiffre">
                <span class="nombre" data-valeur="{{ $nbSignalements }}">{{ $nbSignalements }}</span>
                <p>Signalements enregistrés</p>
            </div>
            <div class="carte-chiffre">
                <span class="nombre" data-valeur="{{ $nbUnitesTouchees }}">{{ $nbUnitesTouchees }}</span>
                <p>Unités concernées</p>
            </div>
            <div class="carte-chiffre">
                <span class="nombre" data-valeur="5">5</span>
                <p>Types de déchets suivis</p>
            </div>
        </div>
    </section>
    <section class="etapes" id="fonctionnement">
        <h2>Comment ça marche</h2>
        <div class="grille-etapes">
            <div class="etape">
                <span class="numero">1</span>
                <h3>Placez le dépôt</h3>
                <p>Cliquez sur « Signaler un dépôt », puis sur la carte à l'endroit exact. L'application trouve toute seule l'Unité concernée.</p>
            </div>
            <div class="etape">
                <span class="numero">2</span>
                <h3>Décrivez-le</h3>
                <p>Choisissez le type de déchet. Vous pouvez ajouter une description et une photo.</p>
            </div>
            <div class="etape">
                <span class="numero">3</span>
                <h3>Suivez-le sur la carte</h3>
                <p>Votre signalement apparaît tout de suite sur la carte, avec sa couleur, son Unité et son statut.</p>
            </div>
        </div>
        <a class="btn btn-vert" href="{{ url('/carte') }}">Ouvrir la carte</a>
    </section>
</main>
<script src="{{ asset('js/accueil.js') }}"></script>
</body>
</html>
