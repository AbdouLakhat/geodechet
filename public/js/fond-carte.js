function ajouterChoixDeFond(carte, fondPlan) {
    var fondSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri — Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
        maxNativeZoom: 18,
        maxZoom: 19
    });

    var satelliteActif = false;
    var controle = L.control({ position: 'topleft' });

    controle.onAdd = function () {
        var conteneur = L.DomUtil.create('div', 'bouton-fond leaflet-bar');
        conteneur.innerHTML =
            '<a href="#" role="button" title="Passer en vue satellite" aria-label="Changer le fond de carte">'
            + '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round">'
            + '<polygon points="12 3 3 8 12 13 21 8"/>'
            + '<polyline points="3 12 12 17 21 12"/>'
            + '</svg></a>';

        var lien = conteneur.querySelector('a');
        L.DomEvent.disableClickPropagation(conteneur);

        lien.addEventListener('click', function (e) {
            e.preventDefault();

            if (satelliteActif) {
                carte.removeLayer(fondSatellite);
                fondPlan.addTo(carte);
                lien.title = 'Passer en vue satellite';
                lien.classList.remove('actif');
            } else {
                carte.removeLayer(fondPlan);
                fondSatellite.addTo(carte);
                lien.title = 'Passer en vue plan';
                lien.classList.add('actif');
            }

            satelliteActif = !satelliteActif;
        });

        return conteneur;
    };

    controle.addTo(carte);
}
