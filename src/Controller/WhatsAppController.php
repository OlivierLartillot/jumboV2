<?php

namespace App\Controller;

use App\Repository\TransferArrivalRepository;
use App\Repository\TransferDepartureRepository;
use App\Repository\TransferInterHotelRepository;
use App\Repository\WhatsAppMessageRepository;
use App\Services\DaysConversions;
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
                                    TextManager $textManager,
                                    DaysConversions $daysConversions                           
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
                $meetingPointLower = strtolower($transferObject->getMeetingPoint()->$meetingpointLang());
                $meetingPointUcfirst = ucfirst($transferObject->getMeetingPoint()->$meetingpointLang());
                $meetingAtPoint = $transferObject->getMeetingPoint()->$whatsAppLang();
                if ($meetingAtPoint == null) {
                    $meetingAtPoint = $meetingPointLower;
                }

                $dayNumber =  $transferObject->getMeetingAt()->format('w');
                $getDaysLang = 'getDays' . ucfirst($langue);
                $dayInLetterLower = $daysConversions->getDays(strtoupper($langue), $dayNumber);
                $dayInLetterUcfirst = ucfirst($daysConversions->getDays(strtoupper($langue), $dayNumber));
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
            $text = $textManager->replaceTags($text, $textManager->getConvertToWhatsApp());   
            $text = $textManager->replaceTags($text, $textManager->getConvertSmileys());   
            // si c est arrivée
            if ($typeTransfer == 1) {
                $text = $textManager->replaceVariables($text, [
                    "%client%" => $client, 
                    "%meetingHour%" => $meetingAt, 
                    "%meetingPoint%" => $meetingPointLower, 
                    "%MeetingPoint%" => $meetingPointUcfirst, 
                    "%meetingAtPoint%" => $meetingAtPoint, 
                    "%dayInLetter%" => $dayInLetterLower,
                    "%DayInLetter%" => $dayInLetterUcfirst,
                ]);
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