<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion – GéoDéchet</title>
    <link rel="stylesheet" href="{{ asset('css/connexion.css') }}">
</head>
<body>
<main class="boite">
    <a class="retour" href="{{ url('/') }}">&larr; Accueil</a>
    <h1>Connexion</h1>
    <p class="sous-titre">Espace réservé à l'administration et aux chauffeurs.</p>

    <form method="POST" action="{{ url('/connexion') }}">
        @csrf

        <label for="email">Adresse e-mail</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>

        @error('email')
        <p class="erreur">{{ $message }}</p>
        @enderror
        @error('password')
        <p class="erreur">{{ $message }}</p>
        @enderror

        <button type="submit">Se connecter</button>
    </form>
</main>
</body>
</html>
