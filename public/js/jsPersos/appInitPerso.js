const appInitPerso = {
    /**
     * Méthode init
     */
    init: function() {
        console.log("init perso");

        changementStatusAjax.init();
        appCopyButton.init();
    }
};
// On veut exécuter la méthode init de l'objet app au chargement de la page
document.addEventListener('DOMContentLoaded', appInitPerso.init);