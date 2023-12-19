
// Notre variable qui contient le "module" app (un objet)
let bbCode = {

    // Méthode appelée au chargement de la page
    init: function() {
        console.log('on écoute le bbcode');
        
        const bbCodeButtons = document.getElementsByClassName('bbcode');
        bbCodeButtons.forEach(bbCodeButton => {
            bbCodeButton.addEventListener('click', bbCode.handleChange);
            
        });
        
    },

     handleChange: function(event) {
        event.preventDefault();

        console.log(event.target.id)
        let baliseStart = ""
        let baliseEnd = ""
        let textArea = document.getElementById("myTextarea");
        let selected = textArea.value.slice(textArea.selectionStart, textArea.selectionEnd);

        if (event.target.id == 'bold') {baliseStart = '<b>';baliseEnd = '</b>';
        textArea.setRangeText(`${baliseStart}${selected}${baliseEnd} `);
    }
        console.log(event.target.name)
        if (event.target.id == 'sourire') {
            textArea.setRangeText('[:)]');
        }
        
        
    } 
}

document.addEventListener('DOMContentLoaded', bbCode.init);