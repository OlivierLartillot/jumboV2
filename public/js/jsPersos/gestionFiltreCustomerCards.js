
// Notre variable qui contient le "module" app (un objet)
let app = {

    // Méthode appelée au chargement de la page
    init: function() {

        let customerPresence = document.querySelector('#customerPresence');
        customerPresence.addEventListener("change", app.handleChange)        
    },

    handleChange: function(event) {
        // Récupere les 2 input a cacher/montrer
        console.log(customerPresence.value)
        let divNatureTransfer = document.querySelector('#divNatureTransfer')
        let natureTransfer = document.querySelector('#natureTransfer')
        let divFlightNumber = document.querySelector('#divFlightNumber')
        let flightNumber = document.querySelector('#flightNumber')
        

        if (customerPresence.value == 1) {
            divNatureTransfer.classList.add('d-none')
            natureTransfer.value = "all"
            divFlightNumber.classList.add('d-none')
            flightNumber.value = ""

        } else {
            divNatureTransfer.classList.remove('d-none')
            divFlightNumber.classList.remove('d-none')

        }

    },

}

// Quand la page est entièrement chargée, on exécute la méthode init située dans l'object app.
document.addEventListener('DOMContentLoaded', app.init)