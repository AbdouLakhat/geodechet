<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre') – GéoDéchet</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icone.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body>
<div class="admin">
    {{-- Menu commun à l’espace du chauffeur connecté. --}}
    <aside class="lateral">
        <div class="marque">
            <img class="icone-marque" src="{{ asset('images/logo-icone.png') }}" alt="">
            <span>GéoDéchet</span>
        </div>
        <nav class="menu">
            <a href="{{ route('chauffeur.dashboard') }}" class="actif">Mes signalements</a>
        </nav>
        <div class="bas">
            <p class="utilisateur">{{ auth()->user()->name }}</p>
            <a href="{{ route('home') }}">Accueil du site</a>
            <form method="POST" action="{{ url('/deconnexion') }}">
                @csrf
                <button type="submit">Se déconnecter</button>
            </form>
        </div>
    </aside>

    {{-- La page des missions est insérée dans cette zone. --}}
    <main class="contenu">
        @yield('contenu')
    </main>
</div>
<script src="{{ asset('js/admin.js') }}"></script>
<script src="{{ asset('js/chauffeur.js') }}"></script>
</body>
</html>
