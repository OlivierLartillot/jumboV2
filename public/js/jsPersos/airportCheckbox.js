
// Notre variable qui contient le "module" changementStatusAjax (un objet)
let airportCheckbox = {

    // Méthode changementStatusAjaxelée au chargement de la page
    init: function() {
        console.log('on écoute le changement de checkbox');
        
        let buttons = document.getElementsByClassName('airport-flightNumber-btn');
        //console.log(buttons);

        buttons.forEach(element => {
            element.addEventListener('click',airportCheckbox.handleClic);
            
        });
    },

    butonState: function(bouton){
        let state = "active";
        if (bouton.classList.contains("btn-outline-primary") ) {
            state = "inactive";
        }
        return state;
    },

    switchButon: function(bouton){
        if (airportCheckbox.butonState(bouton) == "inactive") {
            bouton.classList.remove("btn-outline-primary")
            bouton.classList.add("btn-primary")
            bouton.classList.remove("bg-white")
            bouton.classList.remove("text-primary")
           } else {
            bouton.classList.remove("btn-primary")
            bouton.classList.add("btn-outline-primary")
            bouton.classList.add("bg-white")
            bouton.classList.add("text-primary")
           }
    },

    handleClic: function(event) {
        // cible le groupe de boutons sur lequel on a cliqué (ex: group-2)
        console.log(event.target.id);
        airportCheckbox.switchButon(event.target)
        let butonState = airportCheckbox.butonState(event.target)
        const group = document.querySelectorAll('[data-group=' + event.target.id + ' ]');
        console.log(group)
        group.forEach(element => {
            if (butonState == "inactive") {
                element.classList.add('d-none')
            } else {
                element.classList.remove('d-none')
            }
        });

    },

}

document.addEventListener('DOMContentLoaded', airportCheckbox.init);
