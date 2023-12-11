
// Notre variable qui contient le "module" app (un objet)
let app = {

    // Méthode appelée au chargement de la page
    init: function() {
        console.log('on écoute le bouton copier');
        
        let copyButtons = document.getElementsByClassName('bontonCopier');
        //console.log(buttons);
        copyButtons.forEach(element => {
            element.addEventListener('click',app.handleClic);
            
        });
    },


    handleClic: function(event) {
        const buttonId = event.target.id;

        const textToCopy = document.getElementById("textToCopy-"+ buttonId);
        navigator.clipboard.writeText(textToCopy.textContent);

        
    }
}



// Quand la page est entièrement chargée, on exécute la méthode init située dans l'object app.
document.addEventListener('DOMContentLoaded', app.init)