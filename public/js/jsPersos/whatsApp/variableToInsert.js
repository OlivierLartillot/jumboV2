
// Notre variable qui contient le "module" app (un objet)
let appVariableToinsert = {

    // Méthode appelée au chargement de la page
    init: function() {
        console.log('on écoute la variable a insérer');
        
        const select = document.getElementById('variable-select');
        select.addEventListener('change',appVariableToinsert.handleChange);

/*         let copyButtons = document.getElementsByClassName('bontonCopier');
        //console.log(buttons);
        copyButtons.forEach(element => {
            element.addEventListener('change',appVariableToinsert.handleChange);
            
        }); */
    },

     handleChange: function(event) {
        event.preventDefault();
        console.log(event.target.value);

        let textArea = document.getElementById("myTextarea");
        newText = textArea.value + "" +  event.target.value
        console.log(newText)

        textArea.value = newText
        
        document.getElementById('variable-select').value = "";
  

    
    } 
}

document.addEventListener('DOMContentLoaded', appVariableToinsert.init);