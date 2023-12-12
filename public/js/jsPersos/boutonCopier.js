
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
        event.preventDefault();
        const buttonId = event.target.id;

        const textToCopy = document.getElementById("textToCopy-"+ buttonId).textContent;
        navigator.clipboard.writeText(textToCopy);

        // tous les éléments de pouce vers le haut 👍
        const thumbs = document.getElementsByClassName('thumb');
        const thumb = document.getElementById('thumb-' + buttonId);

        console.log(thumb)
        thumbs.forEach(element => {
            (element.id == thumb.id ) ? element.classList.remove("d-none") : element.classList.add("d-none")

            
        });
        
    }
}



// Quand la page est entièrement chargée, on exécute la méthode init située dans l'object app.
document.addEventListener('DOMContentLoaded', app.init)