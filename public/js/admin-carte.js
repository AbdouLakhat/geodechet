var TYPES = {
    ordures_menageres: { nom: 'Ordures ménagères', couleur: '#e65100' },
    encombrants: { nom: 'Encombrants', couleur: '#6a1b9a' },
    gravats: { nom: 'Gravats', couleur: '#795548' },
    dechets_verts: { nom: 'Déchets verts', couleur: '#2e7d32' },
    autre: { nom: 'Autre', couleur: '#616161' }
};

var tousLesSignalements = [];

var carte = L.map('carte-admin').setView([14.7600, -17.4430], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(carte);

carte.createPane('paneSignalements');
carte.getPane('paneSignalements').style.zIndex = 650;

var coucheSignalements = L.layerGroup().addTo(carte);

var styleNormal = { color: '#1565c0', weight: 2, fillColor: '#42a5f5', fillOpacity: 0.2 };
var styleSurvol = { weight: 3, fillOpacity: 0.4 };

function echapper(texte) {
    var div = document.createElement('div');
    div.textContent = texte;
    return div.innerHTML;
}

/* ---------- Légende ---------- */

var legende = L.control({ position: 'bottomleft' });

legende.onAdd = function () {
    var div = L.DomUtil.create('div', 'legende');
    var html = '';
    Object.keys(TYPES).forEach(function (cle) {
        html += '<div><i style="background:' + TYPES[cle].couleur + '"></i>' + TYPES[cle].nom + '</div>';
    });
    div.innerHTML = html;
    return div;
};

legende.addTo(carte);

/* ---------- Unités ---------- */

function chargerUnites() {
    fetch('/unites')
        .then(function (reponse) { return reponse.json(); })
        .then(function (donnees) {
            L.geoJSON(donnees, {
                style: styleNormal,
                onEachFeature: function (feature, couche) {
                    couche.bindPopup('<strong>' + echapper(feature.properties.nom) + '</strong>');
                    couche.on('mouseover', function () { couche.setStyle(styleSurvol); });
                    couche.on('mouseout', function () { couche.setStyle(styleNormal); });
                }
            }).addTo(carte);
        });
}

/* ---------- Signalements ---------- */

function chargerSignalements() {
    fetch('/signalements', { headers: { 'Accept': 'application/json' } })
        .then(function (reponse) { return reponse.json(); })
        .then(function (donnees) {
            tousLesSignalements = donnees.features;
            afficherSignalements();
        });
}

function afficherSignalements() {
    var typeChoisi = document.getElementById('filtre-type').value;
    var statutChoisi = document.getElementById('filtre-statut').value;

    var gardes = tousLesSignalements.filter(function (feature) {
        var p = feature.properties;
        if (typeChoisi !== '' && p.type_dechet !== typeChoisi) {
            return false;
        }
        if (statutChoisi !== '' && p.statut !== statutChoisi) {
            return false;
        }
        return true;
    });

    coucheSignalements.clearLayers();

    L.geoJSON({ type: 'FeatureCollection', features: gardes }, {
        pointToLayer: function (feature, position) {
            var type = TYPES[feature.properties.type_dechet] || TYPES.autre;
            return L.circleMarker(position, {
                pane: 'paneSignalements',
                radius: 8,
                color: 'white',
                weight: 2,
                fillColor: type.couleur,
                fillOpacity: 0.9
            });
        },
        onEachFeature: function (feature, couche) {
            var p = feature.properties;
            var type = TYPES[p.type_dechet] || TYPES.autre;
            var html = '<strong>' + echapper(type.nom) + '</strong><br>'
                + 'Signalement n° ' + echapper(String(p.id)) + '<br>'
                + 'Unité : ' + echapper(p.unite || 'hors des Unités') + '<br>'
                + 'Statut : ' + echapper(p.statut) + '<br>'
                + 'Date : ' + echapper(p.date_signalement);
            if (p.description) {
                html += '<br><em>' + echapper(p.description) + '</em>';
            }
            if (p.photo) {
                html += '<br><img src="' + encodeURI(p.photo) + '" style="width:200px; margin-top:6px; border-radius:6px;">';
            }
            couche.bindPopup(html);
        }
    }).addTo(coucheSignalements);

    document.getElementById('compteur').textContent =
        gardes.length + ' affiché(s) sur ' + tousLesSignalements.length;
}

/* ---------- Filtres ---------- */

document.getElementById('filtre-type').addEventListener('change', afficherSignalements);
document.getElementById('filtre-statut').addEventListener('change', afficherSignalements);

document.getElementById('btn-reinitialiser').addEventListener('click', function () {
    document.getElementById('filtre-type').value = '';
    document.getElementById('filtre-statut').value = '';
    afficherSignalements();
});

chargerUnites();
chargerSignalements();
