
// Notre variable qui contient le "module" app (un objet)
let appCopyButton = {

    // Méthode appelée au chargement de la page
    init: function() {
        console.log('on écoute le bouton copier');
        
        let copyButtons = document.getElementsByClassName('bontonCopier');
        //console.log(buttons);
        copyButtons.forEach(element => {
            element.addEventListener('click',appCopyButton.handleClic);
            
        });
    },


    handleClic: function(event) {
        event.preventDefault();
        const buttonId = event.target.id;

        const textToCopy = document.getElementById("textToCopy-"+ buttonId).textContent.toUpperCase();

        navigator.clipboard.writeText(textToCopy);

        // tous les éléments de pouce vers le haut 👍
        const thumbs = document.getElementsByClassName('thumb');
        const thumb = document.getElementById('thumb-' + buttonId);

        thumbs.forEach(element => {
            (element.id == thumb.id ) ? element.classList.remove("d-none") : element.classList.add("d-none") 
        });
        
    }
}

document.addEventListener('DOMContentLoaded', appCopyButton.init);