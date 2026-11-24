var champMotDePasse = document.getElementById('password');
var boutonVisibilite = document.getElementById('basculer-mot-de-passe');

if (champMotDePasse && boutonVisibilite) {
    boutonVisibilite.hidden = false;

    boutonVisibilite.addEventListener('click', function () {
        var afficherMotDePasse = champMotDePasse.type === 'password';

        champMotDePasse.type = afficherMotDePasse ? 'text' : 'password';
        boutonVisibilite.setAttribute('aria-pressed', String(afficherMotDePasse));
        boutonVisibilite.setAttribute(
            'aria-label',
            afficherMotDePasse ? 'Masquer le mot de passe' : 'Afficher le mot de passe'
        );
    });
}
