var TYPES = {
    ordures_menageres: { nom: 'Ordures ménagères', couleur: '#e65100' },
    encombrants: { nom: 'Encombrants', couleur: '#6a1b9a' },
    gravats: { nom: 'Gravats', couleur: '#795548' },
    dechets_verts: { nom: 'Déchets verts', couleur: '#2e7d32' },
    autre: { nom: 'Autre', couleur: '#616161' }
};

var modeSignalement = false;
var pointChoisi = null;
var marqueur = null;

var jeton = document.querySelector('meta[name="csrf-token"]').content;

var carte = L.map('carte').setView([14.7600, -17.4430], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(carte);

carte.createPane('paneSignalements');
carte.getPane('paneSignalements').style.zIndex = 650;

var coucheSignalements = L.layerGroup().addTo(carte);

var styleNormal = { color: '#1565c0', weight: 2, fillColor: '#42a5f5', fillOpacity: 0.2, bubblingMouseEvents: true };
var styleSurvol = { weight: 3, fillOpacity: 0.4 };

function echapper(texte) {
    var div = document.createElement('div');
    div.textContent = texte;
    return div.innerHTML;
}

function ouvrirBulle(position, contenu) {
    L.popup().setLatLng(position).setContent(contenu).openOn(carte);
}

function afficherMessage(texte, type) {
    var zone = document.getElementById('message');
    zone.textContent = texte;
    zone.className = type;
    zone.style.display = 'block';
    if (type === 'ok') {
        setTimeout(function () { zone.style.display = 'none'; }, 4000);
    }
}

/* ---------- Chargement des données ---------- */

function chargerUnites() {
    fetch('/unites')
        .then(function (reponse) { return reponse.json(); })
        .then(function (donnees) {
            L.geoJSON(donnees, {
                style: styleNormal,
                onEachFeature: function (feature, couche) {
                    couche.on('mouseover', function () { couche.setStyle(styleSurvol); });
                    couche.on('mouseout', function () { couche.setStyle(styleNormal); });
                    couche.on('click', function (e) {
                        if (modeSignalement) return;
                        ouvrirBulle(e.latlng, '<strong>' + echapper(feature.properties.nom) + '</strong>');
                    });
                }
            }).addTo(carte);
        });
}

function chargerSignalements() {
    fetch('/signalements', { headers: { 'Accept': 'application/json' } })
        .then(function (reponse) { return reponse.json(); })
        .then(function (donnees) {
            coucheSignalements.clearLayers();

            L.geoJSON(donnees, {
                pointToLayer: function (feature, position) {
                    var type = TYPES[feature.properties.type_dechet] || TYPES.autre;
                    return L.circleMarker(position, {
                        pane: 'paneSignalements',
                        radius: 8,
                        color: 'white',
                        weight: 2,
                        fillColor: type.couleur,
                        fillOpacity: 0.9,
                        bubblingMouseEvents: true
                    });
                },
                onEachFeature: function (feature, couche) {
                    couche.on('click', function (e) {
                        if (modeSignalement) return;
                        var p = feature.properties;
                        var type = TYPES[p.type_dechet] || TYPES.autre;
                        var html = '<strong>' + echapper(type.nom) + '</strong><br>'
                            + 'Unité : ' + echapper(p.unite || 'hors des Unités') + '<br>'
                            + 'Statut : ' + echapper(p.statut) + '<br>'
                            + 'Date : ' + echapper(p.date_signalement);
                        if (p.description) {
                            html += '<br><em>' + echapper(p.description) + '</em>';
                        }
                        if (p.photo) {
                            html += '<br><img src="' + encodeURI(p.photo) + '" style="width:200px; margin-top:6px; border-radius:6px;">';
                        }
                        ouvrirBulle(e.latlng, html);
                    });
                }
            }).addTo(coucheSignalements);

            document.getElementById('compteur').textContent = donnees.features.length + ' signalement(s)';
        });
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

/* ---------- Mode signalement ---------- */

function activerSignalement() {
    modeSignalement = true;
    carte.closePopup();
    document.getElementById('carte').classList.add('mode-signalement');
    document.getElementById('btn-signaler').textContent = 'Annuler';
    afficherMessage('Clique sur la carte à l\'endroit du dépôt.', 'info');
}

function annulerSignalement() {
    modeSignalement = false;
    pointChoisi = null;
    if (marqueur) carte.removeLayer(marqueur);
    marqueur = null;
    document.getElementById('carte').classList.remove('mode-signalement');
    document.getElementById('btn-signaler').textContent = 'Signaler un dépôt';
    document.getElementById('type_dechet').value = '';
    document.getElementById('description').value = '';
    document.getElementById('photo').value = '';
    document.getElementById('apercu').style.display = 'none';
    document.getElementById('erreur-form').textContent = '';
    document.getElementById('panneau').classList.add('cache');
    document.getElementById('message').style.display = 'none';
}
function envoyerSignalement(){
    var erreur = document.getElementById('erreur-form');
    erreur.textContent = '';

    if (!pointChoisi) {
        erreur.textContent = 'Clique sur la carte pour choisir la position.';
        return;
    }

    var type = document.getElementById('type_dechet').value;
    if (type === '') {
        erreur.textContent = 'Choisis un type de déchet.';
        return;
    }

    var description = document.getElementById('description').value.trim();
    if (description.length > 500) {
        erreur.textContent = 'La description ne doit pas dépasser 500 caractères.';
        return;
    }

    var fichier = document.getElementById('photo').files[0];
    if (fichier) {
        var formatsOk = ['image/jpeg', 'image/png', 'image/webp'];
        if (formatsOk.indexOf(fichier.type) === -1) {
            erreur.textContent = 'La photo doit être au format JPG, PNG ou WEBP.';
            return;
        }
        if (fichier.size > 5 * 1024 * 1024) {
            erreur.textContent = 'La photo ne doit pas dépasser 5 Mo.';
            return;
        }
    }

    var donnees = new FormData();
    donnees.append('type_dechet', type);
    donnees.append('description', description);
    donnees.append('latitude', pointChoisi.lat);
    donnees.append('longitude', pointChoisi.lng);
    if (fichier) {
        donnees.append('photo', fichier);
    }

    var bouton = document.getElementById('btn-envoyer');
    bouton.disabled = true;
    bouton.textContent = 'Envoi...';
    var envoiReussi = false;

    fetch('/signalements', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': jeton
        },
        body: donnees
    })
        .then(function (reponse) {
            envoiReussi = reponse.ok;
            return reponse.json();
        })
        .then(function (resultat) {
            bouton.disabled = false;
            bouton.textContent = 'Envoyer';

            if (envoiReussi) {
                annulerSignalement();
                afficherMessage(resultat.message, 'ok');
                chargerSignalements();
            } else if (resultat.errors) {
                erreur.textContent = Object.values(resultat.errors)[0][0];
            } else {
                erreur.textContent = 'Une erreur est survenue. Réessaie.';
            }
        });
}

/* ---------- Événements ---------- */

document.getElementById('btn-signaler').addEventListener('click', function () {
    if (modeSignalement) {
        annulerSignalement();
    } else {
        activerSignalement();
    }
});

document.getElementById('btn-envoyer').addEventListener('click', envoyerSignalement);
document.getElementById('btn-annuler').addEventListener('click', annulerSignalement);

carte.on('click', function (e) {
    if (!modeSignalement) return;
    pointChoisi = e.latlng;
    if (marqueur) carte.removeLayer(marqueur);
    marqueur = L.marker(e.latlng).addTo(carte);
    document.getElementById('coords').textContent =
        e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5);
    document.getElementById('panneau').classList.remove('cache');
});
document.getElementById('photo').addEventListener('change', function () {
    var apercu = document.getElementById('apercu');
    var fichier = this.files[0];
    if (fichier) {
        apercu.src = URL.createObjectURL(fichier);
        apercu.style.display = 'block';
    } else {
        apercu.style.display = 'none';
    }
});
chargerUnites();
chargerSignalements();
