
// Notre variable qui contient le "module" filterCustomerCard (un objet)
let filterCustomerCard = {

    // Méthode appelée au chargement de la page
    init: function() {

        let customerPresence = document.querySelector('#customerPresence');
        customerPresence.addEventListener("change", filterCustomerCard.handleChange)     
        let natureTransfer = document.getElementById(('natureTransfer'));
        natureTransfer.addEventListener("change", filterCustomerCard.handleChangeNatureTransfer )
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

    handleChangeNatureTransfer: function(event){
        console.log('natureTransfer is chnaging')
        choosingOption = event.target.value;
        let divFlightNumber = document.querySelector('#divFlightNumber')
        // si interHotel, on enleve la case flight number, Sinon on la remet
        if (choosingOption == 2) {
            divFlightNumber.classList.add('d-none')
        } else {
            divFlightNumber.classList.remove('d-none')
        }
    }

}

// Quand la page est entièrement chargée, on exécute la méthode init située dans l'object filterCustomerCard.
document.addEventListener('DOMContentLoaded', filterCustomerCard.init)