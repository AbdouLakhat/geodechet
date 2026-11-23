@extends('admin.layout')

@section('titre', 'Chauffeurs')

@section('contenu')
    <div class="entete-page">
        <div>
            <h1>Chauffeurs</h1>
            <p class="total">{{ $chauffeurs->total() }} chauffeur(s)</p>
        </div>
        <a href="{{ url('/admin/chauffeurs/creer') }}" class="btn-vert">+ Ajouter un chauffeur</a>
    </div>

    @if (session('succes'))
        <p class="message-ok">{{ session('succes') }}</p>
    @endif

    {{-- Liste les chauffeurs et permet de modifier, activer ou supprimer leur compte. --}}
    <div class="defilement">
        <table>
            <thead>
            <tr>
                <th>Nom et prénom</th>
                <th>Téléphone</th>
                <th>E-mail</th>
                <th>Permis</th>
                <th>Immatriculation</th>
                <th>Disponible</th>
                <th>Compte</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($chauffeurs as $c)
                <tr class="{{ $c->actif ? '' : 'ligne-inactive' }}">
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->telephone ?: '–' }}</td>
                    <td>{{ $c->email }}</td>
                    <td>{{ $c->numero_permis ?: '–' }}</td>
                    <td>{{ $c->immatriculation ?: '–' }}</td>
                    <td><span class="etiquette {{ $c->disponible ? 'badge-oui' : 'badge-non' }}">{{ $c->disponible ? 'Oui' : 'Non' }}</span></td>
                    <td><span class="etiquette {{ $c->actif ? 'badge-oui' : 'badge-non' }}">{{ $c->actif ? 'Actif' : 'Inactif' }}</span></td>
                    <td>
                        <div class="actions-ligne">
                            <a class="btn-modifier" href="{{ url('/admin/chauffeurs/' . $c->id . '/modifier') }}">Modifier</a>

                            <form method="POST" action="{{ url('/admin/chauffeurs/' . $c->id . '/activation') }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-neutre">{{ $c->actif ? 'Désactiver' : 'Activer' }}</button>
                            </form>

                            <button type="button" class="btn-supprimer"
                                    data-id="{{ $c->id }}"
                                    data-action="{{ url('/admin/chauffeurs/' . $c->id) }}"
                                    data-texte="Le compte de {{ $c->name }} sera supprimé définitivement.">Supprimer</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="vide">Aucun chauffeur pour le moment.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        @if ($chauffeurs->previousPageUrl())
            <a href="{{ $chauffeurs->previousPageUrl() }}">&larr; Précédent</a>
        @endif
        <span>Page {{ $chauffeurs->currentPage() }} / {{ $chauffeurs->lastPage() }}</span>
        @if ($chauffeurs->nextPageUrl())
            <a href="{{ $chauffeurs->nextPageUrl() }}">Suivant &rarr;</a>
        @endif
    </div>

    {{-- Demande une confirmation avant de supprimer un compte chauffeur. --}}
    <dialog id="dialogue-suppression">
        <form method="POST" id="form-suppression">
            @csrf
            @method('DELETE')
            <h2>Supprimer ce chauffeur ?</h2>
            <p id="texte-suppression"></p>
            <div class="dialogue-boutons">
                <button type="button" id="btn-annuler-suppression">Annuler</button>
                <button type="submit" class="btn-supprimer">Oui, supprimer</button>
            </div>
        </form>
    </dialog>

    <script src="{{ asset('js/admin.js') }}"></script>
@endsection
