<?php

namespace App\Controller;

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
                $paxTab[$user->getUsername()]['adults'] = $customerCardRepository->staffPaxAdultsByDate($user, $arrivalDate, "adults");
                $paxTab[$user->getUsername()]['children'] = $customerCardRepository->staffPaxAdultsByDate($user, $arrivalDate, "children");
                $paxTab[$user->getUsername()]['babies'] = $customerCardRepository->staffPaxAdultsByDate($user, $arrivalDate, "babies");
            
                foreach ($regroupementsClients as $transferArrival) {
                    $agency = $transferArrival->getCustomerCard()->getAgency();
                    $hotel = $transferArrival->getToArrival();
                    
                        $paxRegroupAdults = $customerCardRepository->paxForRegroupementHotelAndAgencies($date,$hotel,$agency, $user, 'adults', $transferArrival->getflightNumber());
                        $paxRegroupChildren = $customerCardRepository->paxForRegroupementHotelAndAgencies($date,$hotel,$agency, $user, 'children', $transferArrival->getflightNumber());
                        $paxRegroupBabies = $customerCardRepository->paxForRegroupementHotelAndAgencies($date,$hotel,$agency, $user, 'babies', $transferArrival->getflightNumber());
                        
                        $paxPerHotelAgency[$user->getUsername().'_adults'][$agency->getId() . '_'.$hotel->getId() .'_'. $transferArrival->getflightNumber()] =  $paxRegroupAdults;
                        $paxPerHotelAgency[$user->getUsername().'_children'][$agency->getId() . '_'.$hotel->getId() .'_'. $transferArrival->getflightNumber()] =  $paxRegroupChildren;
                        $paxPerHotelAgency[$user->getUsername().'_babies'][$agency->getId() . '_'.$hotel->getId() .'_'. $transferArrival->getflightNumber()] =  $paxRegroupBabies;
                } 
            }
        
        return $this->render('rep/repList.html.twig', [
            'date' => $date,
            'user' => $user,
            'regroupementsClients' => $regroupementsClients,   
            'paxTab' => $paxTab,
            'paxPerHotelAgency' => $paxPerHotelAgency
        ]);
       
    } 


        // route qui affiche la fiche d un rep et ses assignations de clients pou un jour donné
    // la fiche doit permettre de changer la date du mmeting comme de rep
    #[Route('/rep/fiche/{user}/date/details', name: 'app_admin_rep_fiche_par_date_details',methods:["GET"])]
    public function ficheRepParDateDetails( User $user, 
                                            CustomerCardRepository $customerCardRepository, 
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

        // attraper la liste des objets correpsondants au representant et au jour 
        $attributionClientsByRepAndDate = $customerCardRepository->findByStaffAndMeetingDate($user, $date);
        //dd($attributionClientsByRepAndDate );

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

        $countPax = $paxTab['adults'] + ($paxTab['children']*0.5);

        return $this->render('team_manager/attributionMeetingsDetails.html.twig', [
            "date" => $date,
            "attributionClientsByRepAndDate" => $attributionClientsByRepAndDate,
            "meetingPoints" => $meetingPoints, 
            "user" => $user,
            "users" => $users,
            "paxTab" => $paxTab,
            "countPax" => $countPax
        ]);
    }


    #[Route('/rep/salidas', name: 'app_rep')]
    public function index(UserRepository $userRepository, TransferDepartureRepository $transferDepartureRepository): Response
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
