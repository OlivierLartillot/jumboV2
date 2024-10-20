<?php

namespace App\Controller\api;

use App\Repository\CustomerCardRepository;
use App\Repository\TransferArrivalRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BusVoucherController extends AbstractController
{
    // !!! modification du LoginTimeSubscriber pour permettre à cette route ublique de ne pas être
    // renvoyée vers le login form
    #[Route('/api/flights', name: 'app_api_public_flights_search', methods: ['GET'])]
    public function bus(Request $request, UserRepository $userRepository, TransferArrivalRepository $transferArrivalRepository): Response
    {
        // l'utilisateur courant est un personnel d'aéroport
        $currentUserAirport = $this->getUser()->getAirport();
        // TODO:  si l'utilisateur n'a pas associé d'aéroport, tu renvoie une erreur lisible

        $date = $request->query->get("date");
        // TODO: rechercher tous les vols de ce jour pour cet aéroport
    
        return $this->json(
            $transferArrivalRepository->searchFlightsNumberForThisAeroportrepAndDay($currentUserAirport, $date),
            200,
            [],
            ['groups' => 'api_public_test']   
        );
    }
}
