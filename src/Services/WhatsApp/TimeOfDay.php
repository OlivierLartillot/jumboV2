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

}