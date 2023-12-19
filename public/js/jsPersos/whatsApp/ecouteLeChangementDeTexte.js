
// Notre variable qui contient le "module" app (un objet)
let changementTexte = {

    // Méthode appelée au chargement de la page
    init: function() {
        console.log('on écoute le changement de texte');
        
        let textArea = document.getElementById("myTextarea");
        textArea.addEventListener('keyup',changementTexte.handleChange);

/*         let copyButtons = document.getElementsByClassName('bontonCopier');
        //console.log(buttons);
        copyButtons.forEach(element => {
            element.addEventListener('change',appVariableToinsert.handleChange);
            
        }); */
    },

     handleChange: function(event) {
        console.log('on a pressé une touche')
        console.log(event.target.value);
        const toucheEntree = 13;
        console.log(event.keyCode === toucheEntree)

        
        // on attrape la div de texte , on la vide et on remet le nouveau contenu
        let divText = document.getElementById('presentationtext');
        
            if (event.key == "Backspace") {

                divText.innerHTML = "" ;
                let newText =  event.target.value.replaceAll('\\n','<br>')
                divText.innerHTML = newText;

            } else {
                if (event.keyCode == toucheEntree) {
                    divText.innerHTML += '<br>' ;
                } else if ((event.which>= 65) && ((event.which <= 90)) || (event.which == 32 )) {
                    divText.innerHTML += event.key ;
                }
            }

    

    
    } 
}

document.addEventListener('DOMContentLoaded', changementTexte.init);