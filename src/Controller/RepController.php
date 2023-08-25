<?php

namespace App\Controller;

use App\Entity\TransferDeparture;
use App\Repository\CustomerCardRepository;
use App\Repository\TransferDepartureRepository;
use App\Repository\UserRepository;
use App\Services\DefineQueryDate;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepController extends AbstractController
{

    #[Route('/rep/replist', name: 'app_admin_rep_replist',methods:["POST", "GET"])]
    public function repList(CustomerCardRepository $customerCardRepository, UserRepository $userRepository, Request $request,DefineQueryDate $defineQueryDate): Response 
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
        $regroupementsClients = $customerCardRepository->regroupmentByDayStaffAgencyAndHotel($date);

        // pour chaque staff on va définir les infos a récupérer
        
            if(in_array('ROLE_REP', $user->getRoles())){  
               

                // pour la recherche date == meetingDate on récupere les pax de date -1 pour avoir les arrivées
                $paxTab[$user->getUsername()]['adults'] = $customerCardRepository->staffPaxAdultsByDate($user, $arrivalDate, "adults");
                $paxTab[$user->getUsername()]['children'] = $customerCardRepository->staffPaxAdultsByDate($user, $arrivalDate, "children");
                $paxTab[$user->getUsername()]['babies'] = $customerCardRepository->staffPaxAdultsByDate($user, $arrivalDate, "babies");
            
                foreach ($regroupementsClients as $clients) {
                    $agency = $clients->getAgency();
                    $hotels = [];
                    foreach ($clients->getTransferArrivals() as $hotel) { $hotels[] = $hotel->getToArrival(); }
                        $paxRegroupAdults = $customerCardRepository->paxForRegroupementHotelAndAgencies($date,$hotels[0],$agency, $user, 'adults');
                        $paxRegroupChildren = $customerCardRepository->paxForRegroupementHotelAndAgencies($date,$hotels[0],$agency, $user, 'children');
                        $paxRegroupBabies = $customerCardRepository->paxForRegroupementHotelAndAgencies($date,$hotels[0],$agency, $user, 'babies');
                        
                        $paxPerHotelAgency[$user->getUsername().'_adults'][$agency->getId() . '_'.$hotels[0]->getId()] =  $paxRegroupAdults;
                        $paxPerHotelAgency[$user->getUsername().'_children'][$agency->getId() . '_'.$hotels[0]->getId()] =  $paxRegroupChildren;
                        $paxPerHotelAgency[$user->getUsername().'_babies'][$agency->getId() . '_'.$hotels[0]->getId()] =  $paxRegroupBabies;
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






    #[Route('/rep/salidas', name: 'app_rep')]
    public function index(UserRepository $userRepository, TransferDepartureRepository $transferDepartureRepository): Response
    {

        
        // si le formulaire a été envoyé


        if (( !empty($_GET['date']) )) {
  
        $date = $_GET['date'];

        // TODO : recupérer le rep actif
        $user= $userRepository->find(3);
        
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
