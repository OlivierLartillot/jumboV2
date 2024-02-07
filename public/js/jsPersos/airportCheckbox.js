
// Notre variable qui contient le "module" changementStatusAjax (un objet)
let airportCheckbox = {

    // Méthode changementStatusAjaxelée au chargement de la page
    init: function() {
        console.log('on écoute le changement de checkbox ou de is Checked !');
        
        let buttons = document.getElementsByClassName('airport-flightNumber-btn');
        //console.log(buttons);
        buttons.forEach(element => {
            element.addEventListener('click',airportCheckbox.handleClic);
            
        });
        let isCheckedButtons = document.getElementsByClassName('airport-isChecked-btn');
        isCheckedButtons.forEach(element => {
            element.addEventListener('click',airportCheckbox.handleClicisChecked);
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
        console.log(event.target);
        airportCheckbox.switchButon(event.target)
        let butonState = airportCheckbox.butonState(event.target)
        const group = document.querySelectorAll('[data-group="' + event.target.id + '" ]');
        console.log('ici le groupe de boutons')
        console.log(group)
        group.forEach(element => {
            if (butonState == "inactive") {
                element.classList.add('d-none')
            } else {
                element.classList.remove('d-none')
            }
        });

    },


    handleClicisChecked: function(event) {
        // cible le groupe de boutons sur lequel on a cliqué (ex: group-2)
        console.log(`au départ l id est ${event.target.id}`);

        let fetchOptions = {
            method: 'POST',
            mode:   'cors',
            cache:  'no-cache'
        };
        fetch('/transfer/vehicle/arrival/airport/isChecked/'+ event.target.id  , fetchOptions)
            .then(function (response) {
            if (response.ok) {
                return response.json();
            }
            return Promise.reject(response);
        }).then(function (data) { 
            console.log(data);
            console.log(`le data est ${data}`);
            // on récupère la div avec l'id body === event.target.id
            const cardBody = document.getElementById(`card-body-${data.id}`);
            // on récupère l'input id === event.target.id
            const inputIsChecked = document.getElementById(data.id);
            console.log(inputIsChecked);
            const isChecked = data.isChecked;
            // sinon c est true il faut implémenter les classes
            if (isChecked) {
                cardBody.classList.remove("airport-background-no-checked");
                inputIsChecked.classList.add("bg-success", "border", "border-success");
            } else {
                cardBody.classList.add("airport-background-no-checked");
                inputIsChecked.classList.remove("bg-success", "border", "border-success");
            }
            // si c est false il faut virer les classes


        });


/*         airportCheckbox.switchButon(event.target)
        let butonState = airportCheckbox.butonState(event.target)
        const group = document.querySelectorAll('[data-group="' + event.target.id + '" ]');
        console.log(group)
        group.forEach(element => {
            if (butonState == "inactive") {
                element.classList.add('d-none')
            } else {
                element.classList.remove('d-none')
            }
        }); */

    },




}

document.addEventListener('DOMContentLoaded', airportCheckbox.init);
