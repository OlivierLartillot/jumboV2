
// Notre variable qui contient le "module" changementStatusAjax (un objet)
let repCheckbox = {

    // Méthode changementStatusAjaxelée au chargement de la page
    init: function() {
        console.log('on écoute le changement de checkbox ou de is Checked !');
        
        let isCheckedButtons = document.getElementsByClassName('rep-isChecked-btn');
        isCheckedButtons.forEach(element => {
            element.addEventListener('click', repCheckbox.handleClicisChecked);
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
        fetch('/client/isChecked/'+ event.target.id  , fetchOptions)
            .then(function (response) {
            if (response.ok) {
                return response.json();
            }
            return Promise.reject(response);
        }).then(function (data) { 
            console.log(data);
            // on récupère la div avec l'id body === event.target.id
            const cardBody = document.getElementById(`card-body-${data.id}`);
            console.log(cardBody);
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
    },
}

document.addEventListener('DOMContentLoaded', repCheckbox.init);
