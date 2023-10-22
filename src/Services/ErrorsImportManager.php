<?php

namespace App\Services;



class ErrorsImportManager { 


    private array $errors = [];

    public function getErrors(): array 
    {
        return $this->errors;
    }

    public function addErrors($error): array 
    {
        $this->errors[]= $error;
        return $this->errors;
    }

    /**
     * Retourne 'ok' ou fait grandir le tableau des erreurs
     * 
     * 
     */
    public function checkCell($cell, $cellName, $expectedResult, $reservationNumber = null){

       // si la cellulle est nulle 
       switch ($cell) {
           case null:
                return $this->addErrors($cellName .' for ' . $reservationNumber . ' is null but should not be null');
            // si le résultat attendu n'est pas a l a hauteur !
           case gettype($cell) != $expectedResult:
                return $this->addErrors($cellName .' for ' . $reservationNumber . ' does not have the correct format');
       }

       return 'ok';
    }
    

       public function checkObject($object) {}
       

}