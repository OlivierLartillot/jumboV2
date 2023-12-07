<?php

namespace App\Controller;

use App\Entity\TransferArrival;
use App\Repository\CustomerCardRepository;
use App\Repository\TransferArrivalRepository;
use App\Services\DefineQueryDate;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
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
        $showSubmit = false;
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
            if ( ($empty == false) and (count($transferArrivals)>0) ) {
                $showSubmit = true;
            }
        }


        // si on a cliqué sur submit on a renvoyé sur la route validate

        return $this->render('backup/reservation.html.twig', [
            'date' => $date,
            'transferArrivals' => $transferArrivals,
            'showSubmit' => $showSubmit
        ]);
    }

    #[Route('/backup/validateReservationsBackup', name: 'app_backup_delete_reservation')]
    public function validateReservationsBackup(Request $request, 
                                                DefineQueryDate $defineQueryDate, 
                                                TransferArrivalRepository $transferArrivalRepository, 
                                                CustomerCardRepository $customerCardRepository,
                                                EntityManagerInterface $manager): Response
    {
        $day =  $defineQueryDate->returnDay($request);
        $date = new DateTimeImmutable($day . '00:00:00');

        if ($this->isCsrfTokenValid('delete', $request->request->get('_token'))) {

            $transferArrivals = $transferArrivalRepository->findAllByCreatedAt($date);


            $isClientinterHotelAssociated = false;
            $isClientDepartureAssociated = false;
            foreach ($transferArrivals as $transfer) {
                $customerCard = $transfer->getCustomerCard();
                // si ce client a un inter hotel associé tu passes a true   
                if (count($customerCard->getTransferInterHotels()) > 0) {
                    $isClientinterHotelAssociated = true;
                    $this->addFlash(
                        'warning',
                        $customerCard . ' has an associated inter Hotel. Please delete the associated Inter Hotel first'
                    );
                } 
                // si ce client a un depart asssocié tu passes a true
                if (count($customerCard->getTransferDeparture()) > 0) {
                    $isClientDepartureAssociated = true;
                    $this->addFlash(
                        'warning',
                        $customerCard . ' has an associated departure. Please delete the associated Departure first'
                    );
                } 
            } 
            // si true alors tu renvoie un flash "rien supprimé car il y a des inter et depart associés a certains clients"
           if ( ($isClientinterHotelAssociated) or ($isClientDepartureAssociated) ) {
               return $this->render('backup/reservation.html.twig', [
                'date' => $date,
                'transferArrivals' => $transferArrivals,
                'showSubmit' => false
               ]);

           }
            
            foreach ($transferArrivals as $transfer) { 
                $customerCard = $transfer->getCustomerCard();
                $transferArrivalRepository->remove($transfer);
                $customerCardRepository->remove($customerCard);
            }

        }

        $manager->flush();

        // si on a cliqué sur submit on a renvoyé sur la route validate


        $this->addFlash(
            'success',
            'Everything has been deleted'
        );

        // si le mdp est faux on renvoie l accueil

        // sinon on supprime et renvoie avec un flash message
        return $this->render('backup/reservation.html.twig', [
            'date' => $date,
            'transferArrivals' => $transferArrivals,
            'showSubmit' => false
        ]);
    }


    // Route pour passer tout en test
/*     #[Route('/fixtures', name: 'app_backup_delete_reservation')]
    public function fixtures(CustomerCardRepository $customerCardRepository, EntityManagerInterface $em): Response
    {
        $i = 0;
        foreach ($customerCardRepository->findAll() as $customer) {
            $customer->setHolder('client ' . $i);
            $customer->setReservationNumber('reserva' . $i);
            $customer->setJumboNumber('Jnumber' . $i);
            $i++;
        }

        $em->flush();

        return $this->redirectToRoute('home');
    } */



}
