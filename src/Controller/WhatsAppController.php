<?php

namespace App\Controller;

use App\Repository\TransferArrivalRepository;
use App\Repository\TransferDepartureRepository;
use App\Repository\TransferInterHotelRepository;
use App\Repository\WhatsAppMessageRepository;
use App\Services\WhatsApp\TextManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin')]
class WhatsAppController extends AbstractController
{
    #[Route('/whatsapp/client/{natureTransfer}/{transferId}', name: 'app_whats_app_client')]
    public function whatsAppClient($natureTransfer, $transferId,
                                    TransferArrivalRepository $transferArrivalRepository, 
                                    TransferInterHotelRepository $transferInterHotelRepository, 
                                    TransferDepartureRepository $transferDepartureRepository,
                                    WhatsAppMessageRepository $whatsAppMessageRepository,    
                                    TextManager $textManager                           
                                    ): Response
    {
        /*arrival, interhotel, departure, */
        $arrival = false;
        switch ($natureTransfer) {
            case 'arrival':
                $transferRepo = $transferArrivalRepository;
                $arrival = true;
                $typeTransfer = 1;
                $transferObject = $transferRepo->find($transferId);
                $meetingAt = $transferObject->getMeetingAt()->format('H:i');
                $langue = $transferObject->getCustomerCard()->getAgency()->getLanguage();
                $whatsAppLang = 'getWhatsApp'.$langue;
                $meetingpointLang = 'get'.$langue;
                $meetingPoint = $transferObject->getMeetingPoint()->$whatsAppLang();
                if ($meetingPoint == null) {
                    $meetingPoint = $transferObject->getMeetingPoint()->$meetingpointLang();
                }
                break;
            case 'interhotel':
                $transferRepo = $transferInterHotelRepository;
                $typeTransfer = 2;
                $transferObject = $transferRepo->find($transferId);



                break;
            case 'departure':
                $transferRepo = $transferDepartureRepository;
                $typeTransfer = 3;
                $transferObject = $transferRepo->find($transferId);

                break;
        }
        $client = ucwords($transferObject->getCustomerCard()->getHolder());
        
        

        $whatsAppMessage = $whatsAppMessageRepository->findOneBy([
            'user' => $this->getUser(),
            'typeTransfer' => $typeTransfer,
            'language' => $langue
        ]);
        // si il y a un whats app message on le transforme
        if ($whatsAppMessage) {
            $text =  $whatsAppMessage->getMessage();

            $text = $textManager->replaceTags($text, 
                                          ["<br>","<b>","</b>", "[:)]","[-!-]",], 
                                        );   
            // si c est arrivée
            if ($typeTransfer == 1) {
                $text = $textManager->replaceTVariables($text, ["%client%" => $client, "%meetingHour%" => $meetingAt, "%meetingPoint%" => $meetingPoint]);
            } else if ($typeTransfer == 2) {// si c'est interhotel

            } else { // si c'est départ

            }

        } else {
            $text = false;
        }
        // sinon on lui donne false
//        dd($whatsAppMessage); 
     

        

        if ($arrival) {
            return $this->render('whats_app/client_arrival.html.twig', [
                'transferObject' =>  $transferObject,
                'text' => $text,
            ]);
        }

        return $this->render('whats_app/client_interHotel_departure.html.twig', [
            'transferObject' =>  $transferObject,
            'text' => $text,
        ]);
    }


}