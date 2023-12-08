
// Notre variable qui contient le "module" app (un objet)
let app = {

    // Méthode appelée au chargement de la page
    init: function() {
        console.log('on écoute l ajax');
        
        let buttons = document.getElementsByClassName('btn-status');
        //console.log(buttons);
        buttons.forEach(element => {
            element.addEventListener('click',app.handleClic);
            
        });
    },

    handleClic: function(event) {
        // cible le groupe de boutons sur lequel on a cliqué (ex: group-2)
        const targetGroup = event.target.dataset.group;

        const group = document.querySelectorAll('[data-group=' + targetGroup + ' ]');
        buttonsGroup = [];
        group.forEach(element => {
            if (element.type == 'button') {
                buttonsGroup.push(element);
            }
        });

        // dans la nodeList on a ces valeurs:
        const card = group[0];
        const alert = group[1];
        // si event.target.id == no show
        if (event.target.id == "no show") {
            card.classList.remove('border-success')
            card.classList.add('border-danger')
            alert.classList.remove('d-none')
        } else {
            card.classList.remove('border-danger')
            card.classList.add('border-success')
            alert.classList.remove('d-none')
        }

        // Boite d'alerte, nouvelle valeure
        alert.getElementsByTagName('span')[1].textContent = event.target.textContent;

        const transferArrivalId = group[6].textContent;

        let fetchOptions = {
            method: 'POST',
            mode:   'cors',
            cache:  'no-cache'
        };
        fetch('/transfer/arrival/maj/status/' + transferArrivalId + '/'+ event.target.id  , fetchOptions)
            .then(function (response) {
            if (response.ok) {
                return response.json();
            }
            return Promise.reject(response);
        }).then(function (data) {


            // si l'id != data
            // => tu vires tous les btn-success ou btn-danger
            buttonsGroup.forEach(button => {           
                if (button.id.toLowerCase() != data.toLowerCase()) {
                    button.classList.replace("btn-success", "btn-outline-success");
                    button.classList.replace("btn-danger", "btn-outline-danger");
                }
                //sinon tu vires  le btn-outline-danger or btn-outline-success
                else {
                    button.classList.remove("btn-outline-success");
                    button.classList.add("btn-success");
                    button.classList.replace("btn-outline-danger", "btn-danger");
                }

            });



            // si dans la liste != no show => tu ajoutes btn-outline-success 
            //sinon btn-outline-danger

            
        }).catch(function (error) {
            console.log("le message d'erreur", error);
        });








    },

}

// Quand la page est entièrement chargée, on exécute la méthode init située dans l'object app.
document.addEventListener('DOMContentLoaded', app.init)