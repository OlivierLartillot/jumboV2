<?php

namespace App\Services;

use DateTime;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

class DefineQueryDate { 



    public function returnDay(Request $request) 
    {
        $session = $request->getSession();
            
        $queryDate = $request->query->get('date');
        
    
        // quand on arrive, pas de query et pas de session
        if (($queryDate == NULL) and ($session->get('date') == null)) {
            $date = new DateTime('now');
            $date->modify('+1 day');
            $date = $date->format('Y-m-d');
            $date = new DateTimeImmutable($date);
            $day = $date->format('Y-m-d');
        }
        // si on choisi une date 
        else if ($queryDate != null) {
            $session->set('date', $queryDate);
            $dateEnSession = $session->get('date');
            $date = $dateEnSession;
            $date = new DateTimeImmutable($date);
            $day = $date->format('Y-m-d');
        }
        else if (($queryDate == NULL) and ($session->get('date') != null)) {
            $dateEnSession = $session->get('date');
            $date = $dateEnSession;
            $date = new DateTimeImmutable($date);
            $day = $date->format('Y-m-d');
        }

        return $day;

    }






}