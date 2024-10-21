<?php

namespace App\Controller\api;

use App\Repository\CustomerCardRepository;
use App\Repository\TransferArrivalRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BusVoucherController extends AbstractController
{

    // 1. Controlleurs de l'api Bus Voucher
    // 2. Les méthodes propres à cette classe


    //***********************************************************************************************/ 
    //************************************ 1 . Controlleurs *****************************************/
    //***********************************************************************************************/ 

    /**
     * Objectif: Route qui permet de récupérer tous les vols de ce jour pour l'aéroport favori du personnel connecté
     * Params: date
     */
    #[Route('/api/flights', name: 'app_api_public_flights_search', methods: ['GET'])]
    public function flights(Request $request, TransferArrivalRepository $transferArrivalRepository): Response
    {
        // l'utilisateur courant est un personnel d'aéroport, on va chercher son aéroport associé
        $currentUserAirport = $this->getUser()->getAirport();
        
        // recevoir la date en query string 
        $date = $request->query->get('date');
        
        // Gestion des ERREURS
        // si l'utilisateur n'a pas associé d'aéroport, tu renvoie une erreur lisible
        if ($currentUserAirport == null) { return $this->json("You must choose a favorite Airport in your settings",400,[],);}
        // si il n'y a pas de date fournie
        if (!isset($date)) {return $this->json("You must choose a date",400,[],);}
        $checkDate = $this->valideDate($date);
        if (!$checkDate) { return $this->json("Invalid date format",400,[],);}
        
        // rechercher tous les vols de ce jour pour cet aéroport
        $flightNumbers = $transferArrivalRepository->searchFlightsNumberForThisAeroportrepAndDay($currentUserAirport, $date);

        return $this->json(
            ['date' =>$date, 'flightNumbers' => $flightNumbers],
            200,
            [],
        );
    }


    /**
     * Objectif: Route qui permet de récupérer la liste des passagers en fonction des vols choisis et du jour pour ce personnel connecté
     * Params: date, flightNumberList
     */
    // 
    #[Route('/api/voucher', name: 'app_api_voucher_list', methods: ['GET'])]
    public function voucherPerDayAndFlight(Request $request,TransferArrivalRepository $transferArrivalRepository): Response
    {
    
        


        return $this->json('la répponse');
    }





    //***********************************************************************************************/ 
    //******************************  2. METHODES propres à l'objet *********************************/
    //***********************************************************************************************/ 

    /**
     * Vérifie la validité de la date, fixée au format Y-m-d
     * @param mixed $date
     * @param mixed $format
     * @return bool
     */
    function valideDate($date, $format = 'Y-m-d'):bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
