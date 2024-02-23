
// Notre variable qui contient le "module" app (un objet)
let appVariableToinsert = {

    // Méthode appelée au chargement de la page
    init: function() {
        console.log('on écoute la variable a insérer');
        

        const variableButtons = document.getElementsByClassName('variable');
        variableButtons.forEach(variableButton => {
            variableButton.addEventListener('click', appVariableToinsert.handleClick);
        })
    },

     handleClick: function(event) {
        event.preventDefault();

        let textArea = document.getElementById("myTextarea");
        let selected = textArea.value.slice(textArea.selectionStart, textArea.selectionEnd);
        textArea.setRangeText(`${selected}%${event.currentTarget.id}%`);

    } 
}

document.addEventListener('DOMContentLoaded', appVariableToinsert.init);