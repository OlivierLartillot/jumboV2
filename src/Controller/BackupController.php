<?php

namespace App\Controller;

use App\Entity\TransferArrival;
use App\Repository\TransferArrivalRepository;
use App\Services\DefineQueryDate;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackupController extends AbstractController
{
    #[Route('/backup/reservations', name: 'app_backup_reservation')]
    public function reservationsBackup(DefineQueryDate $defineQueryDate, Request $request, TransferArrivalRepository $transferArrivalRepository): Response
    {
        $day =  $defineQueryDate->returnDay($request);
        $date = new DateTimeImmutable($day . '00:00:00');

        // si on arrive sur la page on renvoie juste le template avec les arrivées crée ce jour
        $transferArrivals = $transferArrivalRepository->findAllByCreatedAt($date);
    

        
        // on met le show submit a true
        if (count($request->query) > 0) {    
            $empty = true;
            //on vérifie si on a envoyé au moins un élément de tri
            foreach ($request->query as $param) {
                if ($param != null) {
                    $empty = false;
                    break;
                }                
            }

            // si y a au moins un élément envoyé au tri
            if ($empty == false) {

            }
        }


        // si on a cliqué sur submit on a renvoyé sur la route validate

        return $this->render('backup/reservation.html.twig', [
            'date' => $date,
            'transferArrivals' => $transferArrivals,
        ]);
    }

    #[Route('/backup/validateReservationsBackup', name: 'app_backup_delete_reservation')]
    public function validateReservationsBackup(): Response
    {

        // si on a cliqué sur submit on a renvoyé sur la route validate

        // si le mdp est faux on renvoie l accueil

        // sinon on supprime et renvoie avec un flash message
        return $this->render('backup/reservation.html.twig', [
            'showSubmit' => $showSubmit,
        ]);
    }

}
