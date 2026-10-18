var elements = document.querySelectorAll('.carte-chiffre, .etape');
var animationReduite = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function compter(element) {
    var valeur = parseInt(element.getAttribute('data-valeur'), 10);
    var actuel = 0;
    var pas = Math.max(1, Math.ceil(valeur / 60));

    var minuteur = setInterval(function () {
        actuel = actuel + pas;
        if (actuel >= valeur) {
            actuel = valeur;
            clearInterval(minuteur);
        }
        element.textContent = actuel;
    }, 25);
}

var observateur = new IntersectionObserver(function (entrees) {
    entrees.forEach(function (entree) {
        if (!entree.isIntersecting) return;

        var bloc = entree.target;
        var rang = Array.prototype.indexOf.call(bloc.parentElement.children, bloc);

        bloc.style.transitionDelay = (rang * 0.15) + 's';
        bloc.classList.remove('attente');

        var nombre = bloc.querySelector('.nombre');
        if (nombre) {
            setTimeout(function () { compter(nombre); }, rang * 150);
        }

        setTimeout(function () { bloc.style.transitionDelay = ''; }, 1500);
        observateur.unobserve(bloc);
    });
}, { threshold: 0.4 });

if (!animationReduite) {
    elements.forEach(function (bloc) {
        bloc.classList.add('attente');

        var nombre = bloc.querySelector('.nombre');
        if (nombre) {
            nombre.textContent = '0';
        }

        observateur.observe(bloc);
    });
}
