// Gère le choix entre prendre une photo avec l’appareil et en choisir une dans la galerie.
document.querySelectorAll('[data-photo-dialog-open]').forEach(function (openButton) {
    var dialog = document.getElementById(openButton.getAttribute('data-photo-dialog-open'));

    if (!dialog || typeof dialog.showModal !== 'function') {
        return;
    }

    openButton.addEventListener('click', function () {
        dialog.showModal();
    });

    dialog.querySelectorAll('[data-photo-mode]').forEach(function (choiceButton) {
        choiceButton.addEventListener('click', function () {
            var input = document.getElementById(choiceButton.getAttribute('data-photo-input'));

            if (!input) {
                return;
            }

            if (choiceButton.getAttribute('data-photo-mode') === 'camera') {
                // L’attribut capture demande au téléphone d’ouvrir son appareil photo.
                input.setAttribute('capture', 'environment');
            } else {
                input.removeAttribute('capture');
            }

            dialog.close();
            input.click();
        });
    });

    dialog.querySelector('[data-photo-dialog-close]').addEventListener('click', function () {
        dialog.close();
    });

    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) {
            dialog.close();
        }
    });
});

// Affiche le nom du fichier choisi à côté du champ photo.
document.querySelectorAll('input[data-photo-status]').forEach(function (input) {
    input.addEventListener('change', function () {
        var status = document.getElementById(input.getAttribute('data-photo-status'));

        if (!status) {
            return;
        }

        status.textContent = input.files && input.files.length > 0
            ? input.files[0].name
            : 'Aucune photo s\u00e9lectionn\u00e9e';
    });
});
