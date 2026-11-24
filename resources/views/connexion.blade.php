<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion – GéoDéchet</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icone.png') }}">
    <link rel="stylesheet" href="{{ asset('css/connexion.css') }}?v={{ filemtime(public_path('css/connexion.css')) }}">
</head>
<body>
<main class="boite">
    <a class="retour" href="{{ url('/') }}">&larr; Accueil</a>
    <img class="logo" src="{{ asset('images/logo.png') }}" alt="GéoDéchet">
    <h1>Connexion</h1>
    <p class="sous-titre">Espace réservé à l'administration et aux chauffeurs.</p>

    {{-- Envoie les identifiants au contrôleur de connexion avec le jeton CSRF. --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">Adresse e-mail</label>
        <div class="champ-avec-icone">
            <svg class="icone-champ" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="5" width="18" height="14" rx="2"/>
                <path d="m4 7 8 6 8-6"/>
            </svg>
            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus
                   @if ($errors->has('email')) aria-invalid="true" aria-describedby="erreur-email" @endif>
        </div>

        <label for="password">Mot de passe</label>
        <div class="champ-avec-icone champ-mot-de-passe">
            <svg class="icone-champ" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="4" y="10" width="16" height="11" rx="2"/>
                <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                <circle cx="12" cy="15" r="1"/>
                <path d="M12 16v2"/>
            </svg>
            <input type="password" id="password" name="password" autocomplete="current-password" required
                   @if ($errors->has('password')) aria-invalid="true" aria-describedby="erreur-password" @endif>
            <button
                type="button"
                id="basculer-mot-de-passe"
                class="bouton-visibilite"
                aria-label="Afficher le mot de passe"
                aria-controls="password"
                aria-pressed="false"
                hidden
            >
                <svg data-icone-oeil viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"/>
                    <circle cx="12" cy="12" r="2.5"/>
                </svg>
                <svg data-icone-oeil-ferme viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m3 3 18 18"/>
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                    <path d="M9.9 5.2A10 10 0 0 1 12 5c5.7 0 9.5 5.3 10 7-.3 1-1.3 2.5-3 3.8"/>
                    <path d="M6.7 6.7C3.8 8.3 2.3 11.4 2 12c.5 1.7 4.3 7 10 7 1.2 0 2.3-.2 3.3-.7"/>
                </svg>
            </button>
        </div>

        @error('email')
        <p id="erreur-email" class="erreur" role="alert">{{ $message }}</p>
        @enderror
        @error('password')
        <p id="erreur-password" class="erreur" role="alert">{{ $message }}</p>
        @enderror

        <button type="submit" class="bouton-connexion">
            <span>Se connecter</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14"/>
                <path d="m13 6 6 6-6 6"/>
            </svg>
        </button>
    </form>
</main>
<script src="{{ asset('js/connexion.js') }}?v={{ filemtime(public_path('js/connexion.js')) }}"></script>
</body>
</html>
