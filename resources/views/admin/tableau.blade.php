@extends('admin.layout')

@section('titre', 'Tableau de bord')

@php
    $types = [
        'ordures_menageres' => 'Ordures ménagères',
        'encombrants' => 'Encombrants',
        'gravats' => 'Gravats',
        'dechets_verts' => 'Déchets verts',
        'autre' => 'Autre',
    ];
    $couleurs = [
    'ordures_menageres' => '#e65100',
    'encombrants' => '#6a1b9a',
    'gravats' => '#795548',
    'dechets_verts' => '#2e7d32',
    'autre' => '#616161',
];
    // Graphique par type
    $typeLabels = $parType->map(fn ($l) => $types[$l->type_dechet] ?? $l->type_dechet)->values();
    $typeData = $parType->pluck('nombre')->values();
    $typeCouleurs = $parType->map(fn ($l) => $couleurs[$l->type_dechet] ?? '#616161')->values();
    // Graphique par unité
    $uniteLabels = $parUnite->pluck('unite')->values();
    $uniteData = $parUnite->pluck('nombre')->values();

    // Évolution sur 6 mois (on remplit les mois vides avec 0)
    $moisLabels = [];
    $moisData = [];
    $nomsMois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
    for ($i = 5; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $cle = $date->format('Y-m');
        $moisLabels[] = $nomsMois[$date->month - 1] . ' ' . $date->format('y');
        $moisData[] = $parMois[$cle] ?? 0;
    }
@endphp

@section('contenu')
    <h1>Tableau de bord</h1>
    <p class="total">Vue d'ensemble des signalements de déchets</p>

    {{-- Cartes chiffrées --}}
    <div class="kpi-grille">
        <div class="kpi">
            <span class="kpi-nombre">{{ $total }}</span>
            <span class="kpi-titre">Signalements au total</span>
        </div>
        <div class="kpi kpi-attente">
            <span class="kpi-nombre">{{ $enAttente }}</span>
            <span class="kpi-titre">En attente</span>
        </div>
        <div class="kpi kpi-cours">
            <span class="kpi-nombre">{{ $enCours }}</span>
            <span class="kpi-titre">En cours</span>
        </div>
        <div class="kpi kpi-traite">
            <span class="kpi-nombre">{{ $traites }}</span>
            <span class="kpi-titre">Traités</span>
        </div>
        <div class="kpi kpi-unites">
            <span class="kpi-nombre">{{ $unitesTouchees }}</span>
            <span class="kpi-titre">Unités touchées</span>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="graphiques">
        <div class="carte-graphique">
            <h2>Signalements par type de déchet</h2>
            @if ($parType->isEmpty())
                <p class="vide">Aucune donnée pour le moment.</p>
            @else
                <canvas id="graphique-type"></canvas>
            @endif
        </div>

        <div class="carte-graphique">
            <h2>Évolution sur 6 mois</h2>
            <canvas id="graphique-mois"></canvas>
        </div>

        <div class="carte-graphique carte-large">
            <h2>Signalements par unité</h2>
            @if ($parUnite->isEmpty())
                <p class="vide">Aucune donnée pour le moment.</p>
            @else
                <canvas id="graphique-unite"></canvas>
            @endif
        </div>
    </div>

    {{-- Derniers signalements --}}
    <div class="carte-graphique">
        <h2>Derniers signalements</h2>
        <div class="defilement">
            <table>
                <thead>
                <tr>
                    <th>N°</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Unité</th>
                    <th>Statut</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($derniers as $s)
                    <tr>
                        <td>{{ $s->id }}</td>
                        <td>{{ date('d/m/Y', strtotime($s->date_signalement)) }}</td>
                        <td>{{ $types[$s->type_dechet] ?? $s->type_dechet }}</td>
                        <td>{{ $s->unite ?? 'Hors des Unités' }}</td>
                        <td><span class="liste-statut statut-{{ Str::slug($s->statut) }}">{{ ucfirst($s->statut) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="vide">Aucun signalement pour le moment.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p style="margin: 14px 0 0 0;"><a href="{{ url('/admin/signalements') }}" class="lien-tout">Voir tous les signalements &rarr;</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const couleurVerte = '#1b5e20';
        Chart.defaults.font.family = 'Arial, sans-serif';
        Chart.defaults.color = '#455a64';

        @if ($parType->isNotEmpty())
        new Chart(document.getElementById('graphique-type'), {
            type: 'doughnut',
            data: {
                labels: @json($typeLabels),
                datasets: [{ data: @json($typeData), backgroundColor: @json($typeCouleurs) }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
        @endif

        new Chart(document.getElementById('graphique-mois'), {
            type: 'line',
            data: {
                labels: @json($moisLabels),
                datasets: [{
                    label: 'Signalements',
                    data: @json($moisData),
                    borderColor: couleurVerte,
                    backgroundColor: 'rgba(27, 94, 32, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        @if ($parUnite->isNotEmpty())
        new Chart(document.getElementById('graphique-unite'), {
            type: 'bar',
            data: {
                labels: @json($uniteLabels),
                datasets: [{ label: 'Signalements', data: @json($uniteData), backgroundColor: couleurVerte }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
        @endif
    </script>
@endsection
