<?php

namespace App\Services\WhatsApp;

class TextManager { 

    private array $tableauDesValeurs = [
        "<br>" => "\r\n",
        "<b>"=>"*", 
        "</b>" => "*",
        "[:)]"=>"&#x1F600;",
        "[-!-]"=>"&#x1F334;",
        "%client%" => 'client'
    ];

    /** 
    * Cette fonction prend la liste des tags potentiels pour les remplacer à la volée par les symboles
    * définit dans le tableau $tableauDesValeurs (si il y a une correspondance, sinon elle est ignorée)
    *  @return string 
    */
    public function replaceTags($text, $values= [] ):string
    {
        foreach ($values as $value){
            if (array_key_exists($value, $this->tableauDesValeurs)) {
                $text = str_replace( $value , $this->tableauDesValeurs[$value], $text);
            }
        }
        return $text;
    }

    /** 
    * Cette fonction prend en entrée le texte de recherche de replace
    * et un tableau key => value correspondant aux variables
    * ex: %client% => $customerCard->getHolder()
    *  @return string 
    */
    public function replaceTVariables($text, $variables = [] ):string
    {
        foreach ($variables as $oldValue => $newValue){
            $text = str_replace( $oldValue , $newValue, $text);
        }
        return $text;
    }


}