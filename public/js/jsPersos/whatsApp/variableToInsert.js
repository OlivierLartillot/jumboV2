
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
        console.log('on est bien ici' )
        console.log(event.currentTarget.id);

        let textArea = document.getElementById("myTextarea");
        let selected = textArea.value.slice(textArea.selectionStart, textArea.selectionEnd);
        textArea.setRangeText(`${selected}%${event.currentTarget.id}%`);



/*         let textArea = document.getElementById("myTextarea");
        let selected = textArea.value.slice(textArea.selectionStart, textArea.selectionEnd); */

/*         if (event.target.id == 'bold') {baliseStart = '<b>';baliseEnd = '</b>';
        textArea.setRangeText(`${baliseStart}${selected}${baliseEnd} `);
    }
        console.log(event.target.name)
        if (event.target.id == 'sourire') {
            textArea.setRangeText('[:)]');
        }
         */
    } 
}

document.addEventListener('DOMContentLoaded', appVariableToinsert.init);