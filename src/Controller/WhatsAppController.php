<?php

namespace App\Controller;

use App\Repository\TransferArrivalRepository;
use App\Repository\TransferDepartureRepository;
use App\Repository\TransferInterHotelRepository;
use App\Repository\WhatsAppMessageRepository;
use App\Services\DaysConversions;
use App\Services\WhatsApp\TextManager;
use App\Services\WhatsApp\TimeOfDay;
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
                                    DaysConversions $daysConversions, TimeOfDay $timeOfDay                         
                                    ): Response
    {
        
        $textTimeOfDay = false;
        $meetingAtPoint = false;

        switch ($natureTransfer) {
            case 'arrival':
                $transferRepo = $transferArrivalRepository;
                $typeTransfer = 1;
                $transferObject = $transferRepo->find($transferId);
                $langue = $transferObject->getCustomerCard()->getAgency()->getLanguage();
                $meetingAt = $transferObject->getMeetingAt()->format('H:i');
                $textTimeOfDay = $timeOfDay->timeOfDay($transferObject->getMeetingAt()->format('H'), $langue) ;
                $whatsAppLang = 'getWhatsApp'.$langue;
                $meetingpointLang = 'get'.$langue;
                $meetingPointLower = strtolower($transferObject->getMeetingPoint()->$meetingpointLang());
                $meetingPointUcfirst = ucfirst($transferObject->getMeetingPoint()->$meetingpointLang());
                $meetingAtPoint = $transferObject->getMeetingPoint()->$whatsAppLang();
                if ($meetingAtPoint == null) {
                    $meetingAtPoint = $meetingPointLower;
                }
                $dayNumber =  $transferObject->getMeetingAt()->format('w');
                $dayInLetterLower = $daysConversions->getDays(strtoupper($langue), $dayNumber);
                $dayInLetterUcfirst = ucfirst($daysConversions->getDays(strtoupper($langue), $dayNumber));
                break;
            case 'interhotel':
                $transferRepo = $transferInterHotelRepository;
                $typeTransfer = 2;
                $transferObject = $transferRepo->find($transferId);
                $langue = $transferObject->getCustomerCard()->getAgency()->getLanguage();
                $pickupHour = $transferObject->getPickUp()->format('H:i');
                $hotel = $transferObject->getToArrival();
                break;
            case 'departure':
                $transferRepo = $transferDepartureRepository;
                $typeTransfer = 3;
                $transferObject = $transferRepo->find($transferId);
                $langue = $transferObject->getCustomerCard()->getAgency()->getLanguage();
                $pickupHour = $transferObject->getPickUp()->format('H:i');
                $airport = $transferObject->getToArrival();
                $flightHour = $transferObject->getHour()->format('H:i');
                $flightNumber = $transferObject->getFlightNumber();
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
                    "%timeOfDay%" => $textTimeOfDay,
                
                ]);
            } else if ($typeTransfer == 2) {// si c'est interhotel
                $text = $textManager->replaceVariables($text, [ 
                    "%client%" => $client,
                    "%pickupHour%" => $pickupHour,
                    "%toHotel%" => $hotel,
                ]);
            } else { // si c'est départ
                $text = $textManager->replaceVariables($text, [ 
                    "%client%" => $client,
                    "%pickupHour%" => $pickupHour,
                    "%flightHour%" => $flightHour,
                    "%flightNumber%" => $flightNumber,
                    "%toAirport%" => $airport,
                ]);
            }

        } else {
            $text = false;
        }
       
            return $this->render('whats_app/client_arrival.html.twig', [
                'transferObject' =>  $transferObject,
                'text' => $text,
                'natureTransfer' => $natureTransfer,
                "textTimeOfDay" => $textTimeOfDay,
                "meetingAtPoint" => $meetingAtPoint,
            ]);
        
    }


}