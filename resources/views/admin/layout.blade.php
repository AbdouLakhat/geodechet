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
    {{-- Menu commun aux pages d’administration. --}}
    <aside class="lateral">
        <div class="marque">
            <img class="icone-marque" src="{{ asset('images/logo-icone.png') }}" alt="">
            <span>GéoDéchet</span>
        </div>

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
            <a href="{{ url('/admin/carte') }}" class="{{ request()->is('admin/carte') ? 'actif' : '' }}">
                <svg class="icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m3 6 5-3 8 3 5-3v15l-5 3-8-3-5 3z"/>
                    <path d="M8 3v15M16 6v15"/>
                    <path d="M12 9a2 2 0 0 0-2 2c0 1.5 2 3.5 2 3.5s2-2 2-3.5a2 2 0 0 0-2-2z"/>
                </svg>
                Carte
            </a>
            <a href="{{ url('/admin/chauffeurs') }}" class="{{ request()->is('admin/chauffeurs*') ? 'actif' : '' }}">
                <svg class="icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 6h11v11H3z"/>
                    <path d="M14 10h4l3 3v4h-7z"/>
                    <circle cx="7.5" cy="18" r="1.5"/>
                    <circle cx="17.5" cy="18" r="1.5"/>
                    <path d="M15 13h3"/>
                </svg>
                Chauffeurs
            </a>
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

    {{-- Chaque page admin insère son contenu dans cette zone. --}}
    <main class="contenu">
        @yield('contenu')
    </main>
</div>
</body>
</html>
