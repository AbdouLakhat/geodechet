<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre') – GéoDéchet</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="admin">
    <aside class="lateral">
        <div class="marque">🌿 GéoDéchet</div>

        <nav class="menu">
            <a href="{{ url('/admin') }}" class="{{ request()->is('admin') ? 'actif' : '' }}">
                <svg class="icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                    <line x1="3" y1="20" x2="21" y2="20"/>
                </svg>
                Tableau de bord
            </a>
            <a href="{{ url('/admin/signalements') }}" class="{{ request()->is('admin/signalements*') ? 'actif' : '' }}">
                <svg class="icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 2h6a1 1 0 0 1 1 1v1h1a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1z"/>
                    <line x1="9" y1="12" x2="15" y2="12"/>
                    <line x1="9" y1="16" x2="13" y2="16"/>
                </svg>
                Signalements
            </a>
            <a href="{{ url('/admin/carte') }}" class="{{ request()->is('admin/carte') ? 'actif' : '' }}">🗺️ Carte</a>
        </nav>

        <div class="bas">
            <p class="utilisateur">{{ auth()->user()->name }}</p>
            <a href="{{ url('/') }}">Accueil du site</a>
            <a href="{{ url('/carte') }}">Carte publique</a>
            <form method="POST" action="{{ url('/deconnexion') }}">
                @csrf
                <button type="submit">Se déconnecter</button>
            </form>
        </div>
    </aside>

    <main class="contenu">
        @yield('contenu')
    </main>
</div>
</body>
</html>
