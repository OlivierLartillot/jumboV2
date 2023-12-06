<?php

namespace App\Controller;

use App\Entity\CustomerCard;
use App\Entity\User;
use App\Repository\CustomerCardRepository;
use App\Repository\MeetingPointRepository;
use App\Repository\TransferArrivalRepository;
use App\Repository\TransferDepartureRepository;
use App\Repository\UserRepository;
use App\Services\DefineQueryDate;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepController extends AbstractController
{

    #[Route('/rep/replist', name: 'app_admin_rep_replist',methods:["GET"])]
    public function repList(CustomerCardRepository $customerCardRepository, UserRepository $userRepository,TransferArrivalRepository $transferArrivalRepository, Request $request,DefineQueryDate $defineQueryDate): Response 
    {
        // utilisation du service qui définit si on utilise la query ou la session
        $day =  $defineQueryDate->returnDay($request);

        // on fixe la date que l'on va utiliser dans le filtre
        $date = new DateTimeImmutable($day . '00:01:00');
        $arrivalDate = $date->modify('-1 day');
        
        // récupération de tous les utilisateurs du site (pas nombreux a ne pas etre rep donc on checkera apres)
        $user = $userRepository->find($this->getUser());

        // nombre de clients sans attributions
        $paxPerHotelAgency = [];
        $paxTab = []; // on va récupérer les pax globaux pour chaque rep
        //$regroupementsClients = $customerCardRepository->regroupmentByDayStaffAgencyAndHotel($date);
        $regroupementsClients = $transferArrivalRepository->meetingRegroupmentByDayStaffAgencyAndHotel($date, $user);
        //dd($regroupementsClients);
        // pour chaque staff on va définir les infos a récupérer
        
            if(in_array('ROLE_REP', $user->getRoles())){  
               

                // pour la recherche date == meetingDate on récupere les pax de date -1 pour avoir les arrivées
                $paxTab[$user->getUsername()]['adults'] = $transferArrivalRepository->staffPaxByDate($user, $date, "adults");
                $paxTab[$user->getUsername()]['children'] = $transferArrivalRepository->staffPaxByDate($user, $date, "children");
                $paxTab[$user->getUsername()]['babies'] = $transferArrivalRepository->staffPaxByDate($user, $date, "babies");
            
                foreach ($regroupementsClients as $transferArrival) {
                    $agency = $transferArrival->getCustomerCard()->getAgency();
                    $hotel = $transferArrival->getToArrival();
                    
                        $paxRegroupAdults = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($hotel,$agency, $user, 'adults', $transferArrival->getMeetingAt(), $transferArrival->getMeetingPoint());
                        $paxRegroupChildren = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($hotel,$agency, $user, 'children', $transferArrival->getMeetingAt(), $transferArrival->getMeetingPoint());
                        $paxRegroupBabies = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($hotel,$agency, $user, 'babies', $transferArrival->getMeetingAt(), $transferArrival->getMeetingPoint());

                        $paxPerHotelAgency[$user->getUsername().'_adults'][$agency->getId() . '_'.$hotel->getId() .'_'. $transferArrival->getMeetingAt()->format('H:i') . '_'. $transferArrival->getMeetingPoint()] =  $paxRegroupAdults;
                        $paxPerHotelAgency[$user->getUsername().'_children'][$agency->getId() . '_'.$hotel->getId() .'_'. $transferArrival->getMeetingAt()->format('H:i') . '_'. $transferArrival->getMeetingPoint()] =  $paxRegroupChildren;
                        $paxPerHotelAgency[$user->getUsername().'_babies'][$agency->getId() . '_'.$hotel->getId() .'_'. $transferArrival->getMeetingAt()->format('H:i') . '_'. $transferArrival->getMeetingPoint()] =  $paxRegroupBabies;
                } 
            }
        
        return $this->render('rep/repList.html.twig', [
            'date' => $date,
            'user' => $user,
            'regroupementsClients' => $regroupementsClients,   
            'paxTab' => $paxTab,
            'paxPerHotelAgency' => $paxPerHotelAgency,
            'briefingsMenu' => true
        ]);
       
    } 

    // route qui affiche la fiche d un rep et ses assignations de clients pou un jour donné
    // la fiche doit permettre de changer la date du mmeting comme de rep
    #[Route('/rep/fiche/{user}/date/details/{hour}', name: 'app_admin_rep_fiche_par_date_details',methods:["GET"])]
    public function ficheRepParDateDetails( User $user, 
                                            $hour,
                                            TransferArrivalRepository $transferArrivalRepository, 
                                            MeetingPointRepository $meetingPointRepository,  
                                            UserRepository $userRepository,
                                            Request $request,
                                            DefineQueryDate $defineQueryDate
                                          ): Response 
    {
    
        // si le user de l'url n'est pas le user courant, tu n'as pas les droits
        if ($user != $this->getUser()) {
            return throw $this->createAccessDeniedException();
        }

        $day =  $defineQueryDate->returnDay($request);
        $date = new DateTimeImmutable($day . '00:01:00');

        // parfois il y a plusieurs hotels pour un meme meeting
        $hotels = [];
        // attraper la liste des objets correpsondants au representant et au jour 
        $attributionClientsByRepAndDate = $transferArrivalRepository->findByStaffAndMeetingDate($user, $date, $hour);
       

        foreach ($attributionClientsByRepAndDate as $arrival) {
            if (!in_array($arrival->getToArrival(), $hotels)){
                $hotels[] = $arrival->getToArrival();
            }
        }
     
        $hotel = $attributionClientsByRepAndDate[0]->getToArrival();

        $meetingPoints = $meetingPointRepository->findAll();
        $users = $userRepository->findAll();
        
        $paxTab = [];
        $paxTab['adults'] = 0;
        $paxTab['children']= 0;
        $paxTab['babies']= 0;
        foreach ($attributionClientsByRepAndDate as $client) {
            $paxTab['adults'] += $client->getAdultsNumber();
            $paxTab['children'] += $client->getChildrenNumber();
            $paxTab['babies'] += $client->getBabiesNumber();
        }

        // Compte le nombre Total de Pax
        $countPax = $paxTab['adults'] + ($paxTab['children']*0.5);

        return $this->render('rep/myBriefings.html.twig', [
            "date" => $date,
            "hotel" => $hotel,
            "hotels" => $hotels,
            "hour" => $hour,
            "attributionClientsByRepAndDate" => $attributionClientsByRepAndDate,
            "meetingPoints" => $meetingPoints, 
            "user" => $user,
            "users" => $users,
            "paxTab" => $paxTab,
            "countPax" => $countPax,
            'briefingsMenu' => true

        ]);
    }

    // la route qui affiche la carte client pour les reps
    #[Route('/rep/fiche/{customerCard}', name: 'app_admin_rep_fiche_client',methods:["GET"])]
    public function clientCard(CustomerCard $customerCard): Response
    {
/* 
        $transferArrivals = $customerCard->getTransferArrivals();
        $transferAInterHotels = $customerCard->getTransferInterHotels();
        $transferDepartures = $customerCard->getTransferDeparture();
 */
        return $this->render('rep/clientCard.html.twig', [
            'customerCard' => $customerCard,
            'briefingsMenu' => true
/*             'transferArrivals' => $transferArrivals,
            'transferAInterHotels' => $transferAInterHotels,
            'transferDepartures' => $transferDepartures, */
        ]);

    }


    #[Route('/rep/salidas', name: 'app_rep')]
    public function salidas(UserRepository $userRepository, TransferDepartureRepository $transferDepartureRepository): Response
    {

        // si le formulaire a été envoyé
        if (( !empty($_GET['date']) )) {
  
        $date = $_GET['date'];

        // TODO : recupérer le rep actif
        $user= $userRepository->find($this->getUser());
        
        //recupérer tous les  clients de ce rep pour departure a cette date
        $allCustomersDeparture = $transferDepartureRepository->findByUserAndDate($user, $date);
        return $this->render('rep/index.html.twig', [
            'allCustomersDeparture' => $allCustomersDeparture
        ]);
        }
        return $this->render('rep/index.html.twig', [
            
        ]);
    }
}
