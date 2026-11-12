var dialogue = document.getElementById('dialogue-suppression');
var formulaire = document.getElementById('form-suppression');
var texte = document.getElementById('texte-suppression');

document.querySelectorAll('.btn-supprimer[data-id]').forEach(function (bouton) {
    bouton.addEventListener('click', function () {
        formulaire.action = bouton.getAttribute('data-action');
        texte.textContent = bouton.getAttribute('data-texte')
            || ('Le signalement n° ' + bouton.getAttribute('data-id') + ' sera supprimé définitivement, avec sa photo.');
        dialogue.showModal();
    });
});

document.getElementById('btn-annuler-suppression').addEventListener('click', function () {
    dialogue.close();
});

dialogue.addEventListener('click', function (e) {
    if (e.target === dialogue) {
        dialogue.close();
    }
});

document.querySelectorAll('.message-ok').forEach(function (message) {
    setTimeout(function () { message.classList.add('disparait'); }, 4000);
    setTimeout(function () { message.remove(); }, 4600);
});
document.querySelectorAll('tr.ligne-cliquable').forEach(function (ligne) {
    ligne.addEventListener('click', function (e) {
        if (e.target.closest('a, button, select, option, form, input')) {
            return;
        }
        window.location.href = ligne.getAttribute('data-href');
    });
});
