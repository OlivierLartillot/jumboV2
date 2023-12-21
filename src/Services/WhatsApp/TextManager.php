<?php

namespace App\Services\WhatsApp;

class TextManager { 

    // conversion de récupération base => whatsApp
    private array $convertToWhatsApp = [
        "<br>" => "\r\n",
        "<b>"=>"*", 
        "</b>" => "*",
        "<script>" => "forbidden tag",
        "</script>" => "forbidden tag",
        "<input" => "forbidden tag",
    ];

    // conversion de textArea => base de donnée
    private array $convertToPhp = [
        "\r\n" => "<br>", 
        "<script>" => "forbidden tag", 
        "</script>" => "forbidden tag", 
        "<input" => "forbidden tag", 
    ];

    // conversion de base de donnée => zone de deescription en php
    private array $exampleVariables = [
        "%client%" => "John Do" ,
        "%meetingHour%" => "10:00",
        "%pickupHour%" => "12:00", 
        "%pickupNumber%" => "????",  
        "%meetingPoint%" => "theatre",
        "%MeetingPoint%" => "Theatre",
        "%meetingAtPoint%" => "at theatre" ,
        "%flightNumber%" => "CM109", 
        "%flyHour%" => "14:00",
        "%toHotel%" => "Hotel Riu Bambu", 
        "%toAirport%" => "Punta Cana International (PUJ)",
        "%dayInLetter%" => "monday",
        "%DayInLetter%" => "Monday",
    ];

    private array $convertSmileys = [
        "[:)]"=>"&#x1F600;",
        "[-!-]"=>"&#x1F334;",
        "[00]" => "&#x1F60E;",
    ];

    public function getConvertToWhatsApp():array
    {
        return $this->convertToWhatsApp;
    }
    public function getConvertToPhp():array
    {
        return $this->convertToPhp;
    }
    public function getExampleVariables():array
    {
        return $this->exampleVariables;
    }
    public function getConvertSmileys():array
    {
        return $this->convertSmileys;
    }

    private function getKeys($array): array
    {
        $keyTab = [];
        foreach ($array as $key => $value) {
            $keyTab[] = $key;
        }
        return $keyTab;
    }

    /** 
    * Cette fonction prend la liste des tags potentiels pour les remplacer à la volée par les symboles
    * définit dans le tableau $arrayToCheck (si il y a une correspondance, sinon elle est ignorée)
    *  @return string 
    */
    public function replaceTags($text, $arrayToCheck ):string
    {   
        //renvoie les clés du tableau pour check la valeur
        $keyTags = $this->getKeys($arrayToCheck);
        foreach ($keyTags as $value){
            if (array_key_exists($value, $arrayToCheck)) {
                $text = str_replace( $value , $arrayToCheck[$value], $text);
            }
        }
        return $text;
    }

    /** 
    * Cette fonction prend en entrée le texte de recherche de replace
    * et un tableau key => value, key correspondant aux variables à remplacer par value
    * ex: %client% => $customerCard->getHolder()
    *  @return string 
    */
    public function replaceVariables($text, $variables = [] ):string
    {
        foreach ($variables as $oldValue => $newValue){
            $text = str_replace( $oldValue , $newValue, $text);
        }
        return $text;
    }

}