let changementStatusSelectAjax = {

    // Méthode changementStatusAjaxelée au chargement de la page
    init: function() {
        console.log('on écoute l ajax');
        
        const currentStatus = document.getElementsByClassName('currentStatus');  
        currentStatus.forEach(element => {
            element.addEventListener('click',changementStatusSelectAjax.handleClicCurrentStatus);
            
        });
        const buttonsCancel = document.getElementsByClassName('button-cancel');  
        buttonsCancel.forEach(element => {
            element.addEventListener('click',changementStatusSelectAjax.handleClicButtonCancel);
            
        });




        /* const statusCancelButton = document.getElementById('statusCancelButton'); */
/*         currentStatus.addEventListener('click', changementStatus.handleChange);
        statusCancelButton.addEventListener('click', changementStatus.handleCancel); */



        //!!! on écoute le bouton submit du formulaire !!!
        let buttons = document.getElementsByClassName('validate-status-button');

        buttons.forEach(element => {
            element.addEventListener('click',changementStatusSelectAjax.handleClic);
            
        });

    },
    handleClicCurrentStatus: function(event){
        const targetGroup = event.currentTarget.dataset.group; 
        const currentStatus = event.currentTarget;
        const group = document.querySelectorAll('[data-group=' + targetGroup + ' ]');
        const changeArea = group[3];
        changeArea.classList.remove('d-none')  
        currentStatus.classList.add('d-none')  
    },
    handleClicButtonCancel: function(event){
        const targetGroup = event.currentTarget.dataset.group; 
        const group = document.querySelectorAll('[data-group=' + targetGroup + ' ]');
        console.log(group)
        const changeArea = group[3];
        const badge = group[1];
        changeArea.classList.add('d-none')  
        badge.classList.remove('d-none')  
    },
    handleClic: function(event) {
        // cible le groupe de boutons sur lequel on a cliqué (ex: group-2)
        event.preventDefault();    
        const targetGroup = event.currentTarget.dataset.group;   
        const group = document.querySelectorAll('[data-group=' + targetGroup + ' ]');

        console.log(group)

        const card = group[0];
        const divBadge = group[1];
        const spanBadge = group[2];
        const changeArea = group[3];
        const select = group[4];
        const validateButton= group[5];
        const transferArrival = group[7];
        console.log(select.value)
        console.log(group)


        // si event.target.id == no show
        if (select.value.toLowerCase() == "no show") {
            card.classList.remove('border-success')
            card.classList.add('border-danger')
         
        } else {
            card.classList.remove('border-danger')
            card.classList.add('border-success')
            
        }

        const transferArrivalId = transferArrival.textContent;
        console.log(transferArrivalId)


        let fetchOptions = {
            method: 'POST',
            mode:   'cors',
            cache:  'no-cache'
        };
        fetch('/transfer/arrival/maj/status/' + transferArrivalId + '/'+ select.value  , fetchOptions)
            .then(function (response) {
            if (response.ok) {
                return response.json();
            }
            return Promise.reject(response);
        }).then(function (data) {

            // tu mets le badge avec la nouvelle valeur
            spanBadge.textContent = "";
            spanBadge.textContent = select.value;
            // tu d-block le badge
            divBadge.classList.remove("d-none")

            if (select.value == 'No Show') {
                spanBadge.classList.remove('bg-success');
                spanBadge.classList.add('bg-danger');
            } else {
                spanBadge.classList.remove('bg-danger');
                spanBadge.classList.add('bg-success');
            }

            // tu d-none la zone select 
            changeArea.classList.add("d-none");
            
            // si dans la liste != no show => tu ajoutes btn-outline-success 
            //sinon btn-outline-danger

            
        }).catch(function (error) {
            console.log("le message d'erreur", error);
        });


    },

}

document.addEventListener('DOMContentLoaded', changementStatusSelectAjax.init);
