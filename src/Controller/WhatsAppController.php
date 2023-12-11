<?php

namespace App\Controller;

use App\Entity\CustomerCard;
use App\Repository\TransferDepartureRepository;
use App\Repository\TransferInterHotelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/')]
class WhatsAppController extends AbstractController
{
    #[Route('/whatsapp/client/{natureTransfer}/{transferId}', name: 'app_whats_app_client')]
    public function whatsAppClient($natureTransfer, $transferId, TransferInterHotelRepository $transferInterHotelRepository, TransferDepartureRepository $transferDepartureRepository): Response
    {

        $transferObject = ($natureTransfer == 'interhotel') ? $transferInterHotelRepository->find($transferId): $transferDepartureRepository->find($transferId);

        /* dd('on est dans l envoi du whatsapp'); */
/*         $client->setEnvoiClient('true');
        $infosClientRepository->save($client, true); */

        return $this->render('whats_app/client_wa.html.twig', [
            'transferObject' =>  $transferObject
        ]);
    }


}