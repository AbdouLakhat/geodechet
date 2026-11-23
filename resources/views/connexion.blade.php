<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion – GéoDéchet</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icone.png') }}">
    <link rel="stylesheet" href="{{ asset('css/connexion.css') }}">
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
        <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus
               @if ($errors->has('email')) aria-invalid="true" aria-describedby="erreur-email" @endif>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required
               @if ($errors->has('password')) aria-invalid="true" aria-describedby="erreur-password" @endif>

        @error('email')
        <p id="erreur-email" class="erreur" role="alert">{{ $message }}</p>
        @enderror
        @error('password')
        <p id="erreur-password" class="erreur" role="alert">{{ $message }}</p>
        @enderror

        <button type="submit">Se connecter</button>
    </form>
</main>
</body>
</html>
