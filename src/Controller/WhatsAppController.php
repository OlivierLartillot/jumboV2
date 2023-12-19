<?php

namespace App\Controller;

use App\Repository\TransferArrivalRepository;
use App\Repository\TransferDepartureRepository;
use App\Repository\TransferInterHotelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin')]
class WhatsAppController extends AbstractController
{
    #[Route('/whatsapp/client/{natureTransfer}/{transferId}', name: 'app_whats_app_client')]
    public function whatsAppClient($natureTransfer, $transferId,TransferArrivalRepository $transferArrivalRepository, TransferInterHotelRepository $transferInterHotelRepository, TransferDepartureRepository $transferDepartureRepository): Response
    {
        /*arrival, interhotel, departure, */
        $arrival = false;
        switch ($natureTransfer) {
            case 'arrival':
                $transferRepo = $transferArrivalRepository;
                $arrival = true;
                break;
            case 'interhotel':
                $transferRepo = $transferInterHotelRepository;
                break;
            case 'departure':
                $transferRepo = $transferDepartureRepository;
                break;
        }

        $transferObject = $transferRepo->find($transferId);

        if ($arrival) {
            return $this->render('whats_app/client_arrival.html.twig', [
                'transferObject' =>  $transferObject
            ]);
        }

        return $this->render('whats_app/client_interHotel_departure.html.twig', [
            'transferObject' =>  $transferObject
        ]);
    }


}