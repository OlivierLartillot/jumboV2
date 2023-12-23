<?php

namespace App\Services\WhatsApp;

class TimeOfDay { 

    private array $morningTraductions = ['fr' => 'matin', 'en' => 'morning', 'es' => 'por la mañana', 'it' => 'mattina', 'po' => 'de manhã'];
    private array $afternoonTraductions = ['fr' => 'après-midi', 'en' => 'afternoon', 'es' => ' por la tarde', 'it' => 'pomeriggio', 'po' => 'à tarde'];


    public function timeOfDay($hour, $langue)
    {
        if ($hour < 12) {
            return $this->morningTraductions[$langue];
        } else {
            return $this->afternoonTraductions[$langue];
        }
    }


    public function getTextFr() {
        $text =       
"
J'en profite pour vous rappeler que *vous devez avoir libéré la chambre avant midi et faire le checkout a la réception*.
Confiez vos bagages aux bagagistes qui vont les surveiller et continuez a utiliser l'hôtel normalement jusqu'à l'heure de votre depart.
Bon voyage et au plaisir";
        return $text;
  
    }


    public function textInterHotelDeparture($langue, $pickup) {

        $getTextLangue = 'getText'.$langue;
        
        $hour = substr($pickup,0,2);
        $textToDisplay = ($hour < 12) ? true : false;
        
        
        if ($textToDisplay) {
            $text = $this->$getTextLangue();
           return $text;
        }
        return;
    }




}