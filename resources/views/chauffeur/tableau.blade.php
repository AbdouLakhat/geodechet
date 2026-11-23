@extends('chauffeur.layout')

@section('titre', 'Mes signalements')

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
    <h1>Mes signalements</h1>
    <p class="total">Les missions qui vous ont été affectées</p>

    @if (session('succes'))
        <p class="message-ok" role="status">{{ session('succes') }}</p>
    @endif
    @if (session('erreur'))
        <p class="message-erreur" role="alert">{{ session('erreur') }}</p>
    @endif
    @if ($errors->any())
        <div class="message-erreur" role="alert">
            <ul>
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Le filtre recharge uniquement les missions correspondant au statut choisi. --}}
    <form class="barre-filtres chauffeur-filtres" method="GET" action="{{ route('chauffeur.dashboard') }}">
        <label for="filtre-statut">
            Statut des missions
            <select id="filtre-statut" name="statut">
                <option value="tous" @selected($statutFiltre === 'tous')>Toutes les missions</option>
                <option value="en cours" @selected($statutFiltre === 'en cours')>En cours</option>
                <option value="trait&#233;" @selected($statutFiltre === 'traité')>Termin&eacute;es</option>
            </select>
        </label>
        <button type="submit">Filtrer</button>
        @if ($statutFiltre !== 'tous')
            <a class="reinitialiser-filtre" href="{{ route('chauffeur.dashboard') }}">Tout afficher</a>
        @endif
        <span class="compteur-resultats">
            {{ $signalements->total() }} mission{{ $signalements->total() === 1 ? '' : 's' }}
        </span>
    </form>

    {{-- Présente les missions, leurs coordonnées, leurs photos et leur statut. --}}
    <div class="defilement">
        <table>
            <thead>
            <tr>
                <th>N°</th>
                <th>Date</th>
                <th>Type de déchet</th>
                <th>Unité</th>
                <th>Description</th>
                <th>Photo du dépôt</th>
                <th>Emplacement</th>
                <th>Statut</th>
                <th>Retour chauffeur</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($signalements as $signalement)
                <tr>
                    <td>{{ $signalement->id }}</td>
                    <td>{{ date('d/m/Y', strtotime($signalement->date_signalement)) }}</td>
                    <td>{{ $types[$signalement->type_dechet] ?? $signalement->type_dechet }}</td>
                    <td>{{ $signalement->unite ?? 'Unité inconnue' }}</td>
                    <td>{{ $signalement->description ?: '—' }}</td>
                    <td>
                        @if ($signalement->photo)
                            <a href="{{ asset('storage/' . $signalement->photo) }}" target="_blank" rel="noopener">
                                <img class="miniature" src="{{ asset('storage/' . $signalement->photo) }}" alt="Photo du dépôt signalement {{ $signalement->id }}">
                            </a>
                        @else
                            <span class="vide">Aucune photo</span>
                        @endif
                    </td>
                    <td>
                        @if ($signalement->latitude !== null && $signalement->longitude !== null)
                            <a class="btn-modifier" target="_blank" rel="noopener"
                               href="https://www.google.com/maps/dir/?api=1&amp;destination={{ $signalement->latitude }},{{ $signalement->longitude }}&amp;travelmode=driving">
                                Voir l’itinéraire
                            </a>
                        @else
                            <span class="vide">Indisponible</span>
                        @endif
                    </td>
                    <td><span class="liste-statut statut-{{ Str::slug($signalement->statut) }}">{{ ucfirst($signalement->statut) }}</span></td>
                    <td>
                        @if ($signalement->date_traitement)
                            <p>Traité le {{ date('d/m/Y à H:i', strtotime($signalement->date_traitement)) }}</p>
                        @endif
                        @if ($signalement->photo_traitement)
                            <a href="{{ asset('storage/' . $signalement->photo_traitement) }}" target="_blank" rel="noopener">
                                <img class="miniature" src="{{ asset('storage/' . $signalement->photo_traitement) }}" alt="Preuve de traitement signalement {{ $signalement->id }}">
                            </a>
                        @endif
                        @if ($signalement->note_chauffeur)
                            <p>{{ $signalement->note_chauffeur }}</p>
                        @elseif (!$signalement->date_traitement && !$signalement->photo_traitement)
                            <span class="vide">—</span>
                        @endif
                    </td>
                    <td class="actions-chauffeur">
                        @if ($signalement->statut === 'en cours')
                            {{-- Le chauffeur peut confirmer le traitement ou signaler un problème. --}}
                            <details>
                                <summary>Terminer la mission</summary>
                                <form id="form-terminer-{{ $signalement->id }}" method="POST" enctype="multipart/form-data" action="{{ route('chauffeur.signalements.terminer', $signalement->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <span class="label-photo">Photo apr&egrave;s collecte (facultative)</span>
                                    <div class="selection-photo">
                                        <input
                                            id="photo-traitement-{{ $signalement->id }}"
                                            class="champ-photo-cache"
                                            type="file"
                                            name="photo_traitement"
                                            accept="image/jpeg,image/png,image/webp"
                                            data-photo-status="nom-photo-{{ $signalement->id }}"
                                            aria-hidden="true"
                                            tabindex="-1"
                                        >
                                        <button
                                            type="button"
                                            class="btn-photo"
                                            data-photo-dialog-open="choix-photo-{{ $signalement->id }}"
                                            aria-haspopup="dialog"
                                            aria-controls="choix-photo-{{ $signalement->id }}"
                                        >
                                            Choisir une photo
                                        </button>
                                        <span id="nom-photo-{{ $signalement->id }}" class="nom-photo" role="status" aria-live="polite">Aucune photo s&eacute;lectionn&eacute;e</span>
                                    </div>
                                    <label for="note-traitement-{{ $signalement->id }}">Note (facultative)</label>
                                    <textarea id="note-traitement-{{ $signalement->id }}" name="note_chauffeur" maxlength="500" rows="3"></textarea>
                                    <button type="submit" class="btn-vert" aria-label="Marquer le signalement {{ $signalement->id }} comme traité">
                                        Confirmer le traitement
                                    </button>
                                </form>
                                {{-- Le même champ photo peut recevoir une capture ou un fichier de la galerie. --}}
                                <dialog id="choix-photo-{{ $signalement->id }}" class="dialogue-choix-photo" aria-labelledby="titre-choix-photo-{{ $signalement->id }}">
                                    <h2 id="titre-choix-photo-{{ $signalement->id }}">Ajouter une photo</h2>
                                    <p>Choisissez comment ajouter la photo du traitement.</p>
                                    <div class="choix-photo-actions">
                                        <button type="button" data-photo-mode="camera" data-photo-input="photo-traitement-{{ $signalement->id }}">
                                            Prendre une photo
                                        </button>
                                        <button type="button" data-photo-mode="gallery" data-photo-input="photo-traitement-{{ $signalement->id }}">
                                            Choisir dans la galerie
                                        </button>
                                        <button type="button" class="btn-neutre" data-photo-dialog-close>Annuler</button>
                                    </div>
                                </dialog>
                            </details>
                            <details>
                                <summary>Signaler un problème</summary>
                                <form method="POST" action="{{ route('chauffeur.signalements.probleme', $signalement->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <label for="probleme-{{ $signalement->id }}">Pourquoi la mission n’a-t-elle pas pu être réalisée ?</label>
                                    <textarea id="probleme-{{ $signalement->id }}" name="note_chauffeur" maxlength="500" rows="3" required></textarea>
                                    <button type="submit" class="btn-neutre">Renvoyer à l’administration</button>
                                </form>
                            </details>
                        @else
                            <span class="vide">Aucune action</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="vide">
                        @if ($statutFiltre === 'en cours')
                            Aucune mission en cours.
                        @elseif ($statutFiltre === 'traité')
                            Aucune mission termin&eacute;e.
                        @else
                            Aucun signalement ne vous a &eacute;t&eacute; affect&eacute;.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        @if ($signalements->previousPageUrl())
            <a href="{{ $signalements->previousPageUrl() }}">&larr; Précédent</a>
        @endif
        <span>Page {{ $signalements->currentPage() }} / {{ $signalements->lastPage() }}</span>
        @if ($signalements->nextPageUrl())
            <a href="{{ $signalements->nextPageUrl() }}">Suivant &rarr;</a>
        @endif
    </div>
@endsection
