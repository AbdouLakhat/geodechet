@extends('admin.layout')

@section('titre', 'Signalement ' . $signalement->id)

@php
    $types = [
        'ordures_menageres' => 'Ordures ménagères',
        'encombrants' => 'Encombrants',
        'gravats' => 'Gravats',
        'dechets_verts' => 'Déchets verts',
        'autre' => 'Autre',
    ];
@endphp

@section('contenu')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/fond-carte.css') }}">

    <a href="{{ url('/admin/signalements') }}" class="retour-liste">&larr; Retour aux signalements</a>
    <h1>Signalement n° {{ $signalement->id }}</h1>
    <p class="total">Enregistré le {{ date('d/m/Y', strtotime($signalement->date_signalement)) }}</p>

    @if (session('succes'))
        <p class="message-ok">{{ session('succes') }}</p>
    @endif

    <p><a href="{{ url('/admin/signalements/' . $signalement->id . '/modifier') }}" class="btn-modifier">Modifier ce signalement</a></p>
    {{-- Affiche les données du dépôt et les coordonnées du citoyen. --}}
    <div class="detail-grille">
        <section class="fiche">
            <h2>Le signalement</h2>
            <dl>
                <dt>Type</dt>
                <dd>{{ $types[$signalement->type_dechet] ?? $signalement->type_dechet }}</dd>

                <dt>Unité</dt>
                <dd>{{ $signalement->unite ?? 'Hors des Unités' }}</dd>

                <dt>Statut</dt>
                <dd><span class="liste-statut statut-{{ Str::slug($signalement->statut) }}">{{ ucfirst($signalement->statut) }}</span></dd>

                <dt>Description</dt>
                <dd>{{ $signalement->description ?: '–' }}</dd>

                @if ($signalement->note_chauffeur)
                    <dt>Retour chauffeur</dt>
                    <dd>{{ $signalement->note_chauffeur }}</dd>
                @endif

                @if ($signalement->date_traitement)
                    <dt>Traité le</dt>
                    <dd>{{ date('d/m/Y à H:i', strtotime($signalement->date_traitement)) }}</dd>
                @endif

                <dt>Position</dt>
                <dd>{{ number_format((float) $signalement->latitude, 5, '.', '') }}, {{ number_format((float) $signalement->longitude, 5, '.', '') }}</dd>
            </dl>
        </section>

        <section class="fiche">
            <h2>Le citoyen</h2>
            @if ($signalement->nom)
                <dl>
                    <dt>Nom</dt>
                    <dd>{{ $signalement->nom }}</dd>

                    <dt>Prénom</dt>
                    <dd>{{ $signalement->prenom }}</dd>

                    <dt>Téléphone</dt>
                    <dd><a href="tel:{{ $signalement->telephone }}">{{ $signalement->telephone }}</a></dd>

                    <dt>E-mail</dt>
                    <dd><a href="mailto:{{ $signalement->email }}">{{ $signalement->email }}</a></dd>

                    <dt>Consentement</dt>
                    <dd>
                        @if ($signalement->consentement_le)
                            Donné le {{ date('d/m/Y à H:i', strtotime($signalement->consentement_le)) }}
                        @else
                            –
                        @endif
                    </dd>
                </dl>
            @else
                <p class="vide">Aucune coordonnée enregistrée : ce signalement a été fait avant la collecte des coordonnées.</p>
            @endif
        </section>
    </div>

    {{-- Affiche les photos et place le signalement sur une carte. --}}
    <div class="detail-grille">
        <section class="fiche">
            <h2>Photo du dépôt</h2>
            @if ($signalement->photo)
                <a href="{{ asset('storage/' . $signalement->photo) }}" target="_blank" rel="noopener">
                    <img class="photo-detail" src="{{ asset('storage/' . $signalement->photo) }}" alt="Photo du signalement {{ $signalement->id }}">
                </a>
            @else
                <p class="vide">Aucune photo.</p>
            @endif
        </section>

        <section class="fiche">
            <h2>Preuve de traitement</h2>
            @if ($signalement->photo_traitement)
                <a href="{{ asset('storage/' . $signalement->photo_traitement) }}" target="_blank" rel="noopener">
                    <img class="photo-detail" src="{{ asset('storage/' . $signalement->photo_traitement) }}" alt="Photo de preuve du signalement {{ $signalement->id }}">
                </a>
            @else
                <p class="vide">Aucune photo de preuve fournie.</p>
            @endif
        </section>

        <section class="fiche">
            <h2>Emplacement</h2>
            <div id="carte-detail"></div>
        </section>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/fond-carte.js') }}"></script>
    <script>
        var lat = @json((float) $signalement->latitude);
        var lng = @json((float) $signalement->longitude);

        var carte = L.map('carte-detail').setView([lat, lng], 18);

        var fondPlan = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(carte);

        ajouterChoixDeFond(carte, fondPlan);

        L.marker([lat, lng]).addTo(carte);
    </script>
    <script src="{{ asset('js/admin.js') }}"></script>
@endsection
