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
var choixOuvert = false;

var jeton = document.querySelector('meta[name="csrf-token"]').content;

var carte = L.map('carte').setView([14.7600, -17.4430], 15);

var fondPlan = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(carte);

ajouterChoixDeFond(carte, fondPlan);

carte.createPane('paneSignalements');
carte.getPane('paneSignalements').style.zIndex = 650;

var coucheSignalements = L.layerGroup().addTo(carte);

var styleNormal = { color: '#1565c0', weight: 2, fillColor: '#42a5f5', fillOpacity: 0.2, bubblingMouseEvents: true };
var styleSurvol = { weight: 3, fillOpacity: 0.4 };

// Convertit le texte en contenu sûr avant de l’insérer dans une fenêtre HTML.
function echapper(texte) {
    var div = document.createElement('div');
    div.textContent = texte;
    return div.innerHTML;
}

// Ouvre une fenêtre d’information à l’emplacement choisi sur la carte.
function ouvrirBulle(position, contenu) {
    L.popup().setLatLng(position).setContent(contenu).openOn(carte);
}

// Affiche un message à l’utilisateur et masque automatiquement les réussites.
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

// Récupère les limites géographiques et les affiche comme des zones sur la carte.
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

var DUREE_AFFICHAGE = 10 * 60 * 1000;

// Relit les signalements récents conservés temporairement dans l’onglet du navigateur.
function lireMesSignalements() {
    var liste = JSON.parse(sessionStorage.getItem('mesSignalements') || '[]');
    var maintenant = Date.now();
    return liste.filter(function (s) {
        return maintenant - s.heure < DUREE_AFFICHAGE;
    });
}

// Dessine sur la carte les signalements récents envoyés par le navigateur.
function afficherMesSignalements() {
    var liste = lireMesSignalements();
    sessionStorage.setItem('mesSignalements', JSON.stringify(liste));
    coucheSignalements.clearLayers();

    liste.forEach(function (s) {
        var type = TYPES[s.type] || TYPES.autre;
        var html = '<strong>Votre signalement</strong><br>' + echapper(type.nom)
            + '<br><small>Il disparaîtra de la carte dans quelques minutes.</small>';

        var point = L.circleMarker([s.lat, s.lng], {
            pane: 'paneSignalements',
            radius: 9,
            color: 'white',
            weight: 2,
            fillColor: type.couleur,
            fillOpacity: 0.9,
            bubblingMouseEvents: true
        });

        point.on('click', function (e) {
            if (modeSignalement) return;
            ouvrirBulle(e.latlng, html);
        });

        point.addTo(coucheSignalements);
    });
}

// Ajoute le signalement à la liste temporaire affichée sur la carte.
function ajouterMonSignalement(lat, lng, type) {
    var liste = lireMesSignalements();
    liste.push({ lat: lat, lng: lng, type: type, heure: Date.now() });
    sessionStorage.setItem('mesSignalements', JSON.stringify(liste));
    afficherMesSignalements();
}

/* ---------- Position du dépôt ---------- */

// Mémorise la position choisie et affiche ses coordonnées dans le formulaire.
function afficherPoint(point) {
    pointChoisi = point;
    document.getElementById('coords').textContent =
        point.lat.toFixed(5) + ', ' + point.lng.toFixed(5);
}

// Place un repère déplaçable pour permettre de corriger la position du dépôt.
function placerPoint(point) {
    if (marqueur) carte.removeLayer(marqueur);
    marqueur = L.marker(point, { draggable: true }).addTo(carte);
    marqueur.on('dragend', function () {
        afficherPoint(marqueur.getLatLng());
    });
    afficherPoint(point);
    document.getElementById('panneau').classList.remove('cache');
}

// Affiche le choix entre utiliser sa position et cliquer sur la carte.
function ouvrirChoix() {
    carte.closePopup();
    document.getElementById('choix-info').textContent = '';
    document.getElementById('choix').classList.remove('cache');
    document.getElementById('btn-signaler').textContent = 'Annuler';
    choixOuvert = true;
}

function fermerChoix() {
    document.getElementById('choix').classList.add('cache');
    choixOuvert = false;
}

// Demande au navigateur la position GPS du téléphone ou de l’ordinateur.
function utiliserMaPosition() {
    var info = document.getElementById('choix-info');

    if (!navigator.geolocation) {
        info.textContent = 'Votre appareil ne permet pas la géolocalisation. Choisissez sur la carte.';
        return;
    }

    info.textContent = 'Recherche de votre position...';

    navigator.geolocation.getCurrentPosition(
        function (position) {
            var point = L.latLng(position.coords.latitude, position.coords.longitude);
            fermerChoix();
            placerPoint(point);
            carte.setView(point, 18);
        },
        function (erreur) {
            if (erreur.code === 1) {
                info.textContent = 'Accès à la position refusé. Autorisez-le dans le navigateur, ou choisissez sur la carte.';
            } else {
                info.textContent = 'Position introuvable. Réessayez, ou choisissez sur la carte.';
            }
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

/* ---------- Erreurs du formulaire ---------- */

// Affiche chaque erreur près du champ concerné et place le curseur sur le premier.
function afficherErreurs(erreurs) {
    var premier = null;

    erreurs.forEach(function (e) {
        var champ = e[0] ? document.getElementById(e[0]) : null;

        if (!champ) {
            document.getElementById('erreur-form').textContent = e[1];
            return;
        }

        champ.classList.add('invalide');

        var cible = champ;
        if (champ.type === 'checkbox') {
            cible = champ.parentElement;
            cible.classList.add('invalide');
        }

        var message = document.createElement('div');
        message.className = 'message-champ';
        message.textContent = e[1];
        cible.insertAdjacentElement('afterend', message);

        if (!premier) {
            premier = champ;
        }
    });

    if (premier) {
        premier.focus();
    }
}

// Retire les erreurs affichées lorsque l’utilisateur recommence sa saisie.
function effacerErreurs() {
    document.querySelectorAll('#panneau .invalide').forEach(function (c) {
        c.classList.remove('invalide');
    });
    document.querySelectorAll('#panneau .message-champ').forEach(function (m) {
        m.remove();
    });
    document.getElementById('erreur-form').textContent = '';
}

/* ---------- Mode signalement ---------- */

// Active le mode qui permet de choisir le point du dépôt sur la carte.
function activerSignalement() {
    modeSignalement = true;
    carte.closePopup();
    document.getElementById('carte').classList.add('mode-signalement');
    document.getElementById('btn-signaler').textContent = 'Annuler';
    afficherMessage('Clique sur la carte à l\'endroit du dépôt.', 'info');
}

// Réinitialise le formulaire, le repère et les messages du signalement en cours.
function annulerSignalement() {
    fermerChoix();
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
    document.getElementById('nom').value = '';
    document.getElementById('prenom').value = '';
    document.getElementById('telephone').value = '';
    document.getElementById('email').value = '';
    document.getElementById('consentement').checked = false;
    effacerErreurs();
    document.getElementById('panneau').classList.add('cache');
    document.getElementById('message').style.display = 'none';
}

// Vérifie les champs puis envoie le signalement au serveur avec sa photo éventuelle.
function envoyerSignalement() {
    effacerErreurs();

    if (!pointChoisi) {
        afficherErreurs([[null, 'Choisis d\'abord la position du dépôt.']]);
        return;
    }

    var erreurs = [];

    var type = document.getElementById('type_dechet').value;
    if (type === '') {
        erreurs.push(['type_dechet', 'Choisis un type de déchet.']);
    }

    var description = document.getElementById('description').value.trim();
    if (description.length > 500) {
        erreurs.push(['description', 'La description ne doit pas dépasser 500 caractères.']);
    }

    var nom = document.getElementById('nom').value.trim();
    var prenom = document.getElementById('prenom').value.trim();
    var telephone = document.getElementById('telephone').value.trim();
    var email = document.getElementById('email').value.trim();
    var consentement = document.getElementById('consentement').checked;

    var motifNom = /^[\p{L}][\p{L}\s'’-]*$/u;
    if (nom === '' || !motifNom.test(nom)) {
        erreurs.push(['nom', 'Entrez votre nom (lettres uniquement).']);
    }
    if (prenom === '' || !motifNom.test(prenom)) {
        erreurs.push(['prenom', 'Entrez votre prénom (lettres uniquement).']);
    }

    var numero = telephone.replace(/[\s.\-]/g, '');
    if (!/^(\+221|00221|221)?[0-9]{9}$/.test(numero)) {
        erreurs.push(['telephone', 'Entrez un numéro valide : 9 chiffres, par exemple 77 123 45 67.']);
    }

    if (email === '' || !document.getElementById('email').checkValidity()) {
        erreurs.push(['email', 'Entrez une adresse e-mail valide.']);
    }

    if (!consentement) {
        erreurs.push(['consentement', 'Vous devez accepter l\'utilisation de vos informations.']);
    }

    var fichier = document.getElementById('photo').files[0];
    if (fichier) {
        var formatsOk = ['image/jpeg', 'image/png', 'image/webp'];
        if (formatsOk.indexOf(fichier.type) === -1) {
            erreurs.push(['photo', 'La photo doit être au format JPG, PNG ou WEBP.']);
        } else if (fichier.size > 5 * 1024 * 1024) {
            erreurs.push(['photo', 'La photo ne doit pas dépasser 5 Mo.']);
        }
    }

    if (erreurs.length > 0) {
        afficherErreurs(erreurs);
        return;
    }

    var donnees = new FormData();
    donnees.append('type_dechet', type);
    donnees.append('description', description);
    donnees.append('latitude', pointChoisi.lat);
    donnees.append('longitude', pointChoisi.lng);
    donnees.append('nom', nom);
    donnees.append('prenom', prenom);
    donnees.append('telephone', numero);
    donnees.append('email', email);
    donnees.append('consentement', '1');
    if (fichier) {
        donnees.append('photo', fichier);
    }

    var bouton = document.getElementById('btn-envoyer');
    bouton.disabled = true;
    bouton.textContent = 'Envoi...';
    var envoiReussi = false;
    var lat = pointChoisi.lat;
    var lng = pointChoisi.lng;

    // Envoie le jeton CSRF attendu par Laravel avec les données du formulaire.
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
                ajouterMonSignalement(lat, lng, type);
            } else if (resultat.errors) {
                var liste = Object.keys(resultat.errors).map(function (cle) {
                    return [document.getElementById(cle) ? cle : null, resultat.errors[cle][0]];
                });
                afficherErreurs(liste);
            } else {
                afficherErreurs([[null, 'Une erreur est survenue. Réessaie.']]);
            }
        })
        .catch(function () {
            bouton.disabled = false;
            bouton.textContent = 'Envoyer';
            afficherErreurs([[null, 'Impossible d’envoyer le signalement. Vérifie ta connexion puis réessaie.']]);
        });
}

/* ---------- Événements ---------- */

document.getElementById('btn-signaler').addEventListener('click', function () {
    var panneauOuvert = !document.getElementById('panneau').classList.contains('cache');

    if (modeSignalement || choixOuvert || panneauOuvert) {
        annulerSignalement();
    } else {
        ouvrirChoix();
    }
});

document.getElementById('btn-choix-position').addEventListener('click', utiliserMaPosition);

document.getElementById('btn-choix-carte').addEventListener('click', function () {
    fermerChoix();
    activerSignalement();
});

document.getElementById('btn-envoyer').addEventListener('click', envoyerSignalement);
document.getElementById('btn-annuler').addEventListener('click', annulerSignalement);

carte.on('click', function (e) {
    if (!modeSignalement) return;
    placerPoint(e.latlng);
});

// Affiche un aperçu de la photo sélectionnée avant l’envoi.
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

document.querySelectorAll('#panneau input, #panneau select, #panneau textarea').forEach(function (champ) {
    function retirer() {
        champ.classList.remove('invalide');
        champ.parentElement.classList.remove('invalide');

        var cible = champ.type === 'checkbox' ? champ.parentElement : champ;
        var suivant = cible.nextElementSibling;
        if (suivant && suivant.classList.contains('message-champ')) {
            suivant.remove();
        }
    }
    champ.addEventListener('input', retirer);
    champ.addEventListener('change', retirer);
});

var params = new URLSearchParams(window.location.search);
if (params.get('signaler') === '1') {
    ouvrirChoix();
}

chargerUnites();
afficherMesSignalements();
setInterval(afficherMesSignalements, 30000);
