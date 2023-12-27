<?php

namespace App\Services\WhatsApp;

class TimeOfDay { 

    private array $morningTraductions = ['fr' => 'matin', 'en' => 'morning', 'es' => 'por la mañana', 'it' => 'mattina', 'po' => 'de manhã'];
    private array $afternoonTraductions = ['fr' => 'après-midi', 'en' => 'afternoon', 'es' => ' por la tarde', 'it' => 'pomeriggio', 'po' => 'à tarde'];

    /**
    *Texte qui s'affiche pour spécifier le moment de la journée ("matin" ou "apres-midi")
    */

    public function timeOfDay($hour, $langue)
    {
        if ($hour < 12) {
            return $this->morningTraductions[$langue];
        } else {
            return $this->afternoonTraductions[$langue];
        }
    }

    
    /**
     * Renvoie le texte à afficher si c est le matin pour 
     */
    public function textInterHotelDeparture($langue, $pickup,$natureTransfer, $toArrival, $isDefaultMessage=true) {

        $getTextBaseLangue = 'getTextBase'.$langue;
        $getTextLangue = 'getText'.$langue;
        
        $textBase = $this->$getTextBaseLangue($natureTransfer, $toArrival, $pickup);
        
        $hour = substr($pickup,0,2);
        $textToDisplay = ($hour < 12) ? true : false;
        
        if ($textToDisplay) {
            if ($isDefaultMessage) {
                return $textBase . ' ' .$this->$getTextLangue();
            } else {
                return $this->$getTextLangue();
            }
        } else {
            if ($isDefaultMessage) {
                return $textBase;
            }
            return;
        }

    }

    /******************** LES TRADUCTIONS *******************/

    // Texte de Base affiché au début du message 

    public function getTextBaseFr($natureTransfer, $toArrival, $pickup){
        $word = ($natureTransfer == 'interhotel') ? "l'hôtel": "l'aéroport" ; 
        $text = 
"Bonjour, votre transfert de demain pour $word ($toArrival) aura lieu à la réception de votre hôtel à : $pickup
";
        return $text;
    }

    public function getTextBaseEs($natureTransfer, $toArrival, $pickup){
        $word = ($natureTransfer == 'interhotel') ? "el hotel": "el aeropuerto" ; 
        $text = 
"Buenos días, su traslado de mañana hacia $word ($toArrival) tendrá lugar en la recepción de su hotel en: $pickup
";
        return $text;
    }

    public function getTextBaseEn($natureTransfer, $toArrival, $pickup){
        $word = ($natureTransfer == 'interhotel') ? "the hotel": "the airport" ; 
        $text = 
"Good morning, your transfer for tomorrow to $word ($toArrival) will take place at the reception of your hotel at: $pickup
";
        return $text;
    }
    public function getTextBaseIt($natureTransfer, $toArrival, $pickup){
        $word = ($natureTransfer == 'interhotel') ? "l'hotel": "l'aeroporto" ; 
        $text = 
"Buongiorno, il vostro trasferimento di domani per $word ($toArrival) avrà luogo presso la reception del vostro hotel a: $pickup
";
        return $text;
    }

    public function getTextBasePo($natureTransfer, $toArrival, $pickup){
        $word = ($natureTransfer == 'interhotel') ? "o hotel": "o aeroporto" ; 
        $text = 
"Bom dia, a sua transferência de amanhã para $word ($toArrival) ocorrerá na recepção do seu hotel em: $pickup
";
        return $text;
    }
                
    // texte qui s'affiche uniquement si c est le matin

    public function getTextFr() {
        $text =       
"
J'en profite pour vous rappeler que *vous devez avoir libéré la chambre avant midi et faire le checkout à la réception*.
Confiez vos bagages aux bagagistes qui vont les surveiller et continuez à utiliser l'hôtel normalement jusqu'à l'heure de votre depart.
Bon voyage et au plaisir";
    return $text;
    }

    public function getTextEs() {
        $text =       
"
Aprovecho para recordarle que *debe liberar la habitación antes del mediodía y hacer el check-out en la recepción*.
Confíe sus maletas a los botones, quienes las vigilarán, y continúe utilizando el hotel normalmente hasta la hora de su salida.
Buen viaje y hasta pronto";
        return $text;
    }
    public function getTextEn() {
        $text =       
"
I take this opportunity to remind you that *you must have vacated the room by noon and checked out at the reception*.
Hand over your luggage to the bellmen who will watch over them, and continue using the hotel normally until your departure time.
Safe travels and see you soon";
        return $text;
    }
    public function getTextIt() {
        $text =       
"
Ne approfitto per ricordarti che *devi liberare la camera entro mezzogiorno e fare il check-out alla reception*.
Affida i tuoi bagagli ai facchini che li sorveglieranno e continua a utilizzare l'hotel normalmente fino all'ora della tua partenza.
Buon viaggio e a presto";
    return $text;
    }
       
    public function getTextPo() {
        $text =       
"
Aproveito para lembrar que *você deve liberar o quarto antes do meio-dia e fazer o check-out na recepção*.
Confie suas malas aos carregadores que as vigiarão e continue usando o hotel normalmente até a hora da sua partida.
Boa viagem e até breve";
        return $text;
    }
    

}