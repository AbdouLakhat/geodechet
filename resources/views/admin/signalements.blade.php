@extends('admin.layout')

@section('titre', 'Signalements')

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
    <h1>Signalements</h1>
    <p class="total">{{ $signalements->total() }} signalement(s) au total</p>

    @if (session('succes'))
        <p class="message-ok">{{ session('succes') }}</p>
    @endif
    @error('statut')
    <p class="message-erreur">{{ $message }}</p>
    @enderror

    <div class="defilement">
        <table>
            <thead>
            <tr>
                <th>N°</th>
                <th>Date</th>
                <th>Type</th>
                <th>Unité</th>
                <th>Statut</th>
                <th>Photo</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($signalements as $s)
                <tr class="ligne-cliquable" data-href="{{ url('/admin/signalements/' . $s->id) }}">
                    <td><a class="lien-numero" href="{{ url('/admin/signalements/' . $s->id) }}">{{ $s->id }}</a></td>
                    <td>{{ date('d/m/Y', strtotime($s->date_signalement)) }}</td>
                    <td>{{ $types[$s->type_dechet] ?? $s->type_dechet }}</td>
                    <td>{{ $s->unite ?? 'Hors des Unités' }}</td>
                    <td>
                        <form method="POST" action="{{ url('/admin/signalements/' . $s->id . '/statut') }}">
                            @csrf
                            @method('PATCH')
                            <select name="statut" class="liste-statut statut-{{ Str::slug($s->statut) }}" onchange="this.form.submit()" aria-label="Statut du signalement {{ $s->id }}">
                                @foreach (['en attente', 'en cours', 'traité'] as $option)
                                    <option value="{{ $option }}" @selected($s->statut === $option)>{{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                            <noscript><button type="submit">OK</button></noscript>
                        </form>
                    </td>
                    <td>
                        @if ($s->photo)
                            <a href="{{ asset('storage/' . $s->photo) }}" target="_blank" rel="noopener">
                                <img class="miniature" src="{{ asset('storage/' . $s->photo) }}" alt="Photo du signalement {{ $s->id }}">
                            </a>
                        @else
                            <span class="vide">–</span>
                        @endif
                    </td>
                    <td>{{ $s->description ? Str::limit($s->description, 60) : '–' }}</td>
                    <td>
                        <div class="actions-ligne">
                            <a class="btn-modifier" href="{{ url('/admin/signalements/' . $s->id . '/modifier') }}">Modifier</a>
                            <button type="button" class="btn-supprimer" data-id="{{ $s->id }}" data-action="{{ url('/admin/signalements/' . $s->id) }}">Supprimer</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="vide">Aucun signalement pour le moment.</td>
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

    <dialog id="dialogue-suppression">
        <form method="POST" id="form-suppression">
            @csrf
            @method('DELETE')
            <h2>Supprimer ce signalement ?</h2>
            <p id="texte-suppression"></p>
            <div class="dialogue-boutons">
                <button type="button" id="btn-annuler-suppression">Annuler</button>
                <button type="submit" class="btn-supprimer">Oui, supprimer</button>
            </div>
        </form>
    </dialog>

    <script src="{{ asset('js/admin.js') }}"></script>
@endsection
