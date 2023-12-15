let changementStatus = {

    // Méthode changementStatus au chargement de la page
    init: function() {
        console.log('on écoute le changement de status');
        const currentStatus = document.getElementById('currentStatus');  
        const statusCancelButton = document.getElementById('statusCancelButton');
        currentStatus.addEventListener('click', changementStatus.handleChange);
        statusCancelButton.addEventListener('click', changementStatus.handleCancel);

    },
    handleChange: function(event) {
        document.getElementById('changeArea').classList.remove('d-none')  
        document.getElementById('currentStatus').classList.add('d-none') 
    },
    handleCancel: function(event){
        document.getElementById('changeArea').classList.add('d-none')  
        document.getElementById('currentStatus').classList.remove('d-none') 
    }
}

document.addEventListener('DOMContentLoaded', changementStatus.init);
