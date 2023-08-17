<?php

namespace App\Controller;

use App\Entity\Agency;
use App\Entity\User;
use App\Form\AgenciesActivationType;
use App\Form\RepAttributionType;
use App\Repository\AgencyRepository;
use App\Repository\CustomerCardRepository;
use App\Repository\MeetingPointRepository;
use App\Repository\UserRepository;
use App\Services\DefineQueryDate;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamManagerController extends AbstractController
{
    
    // route qui affiche tous les rep a attribuer en fonction de la date
    #[Route('/team-manager/attribution', name: 'app_admin_team_manager',methods:["POST", "GET"])]
    public function index(Request $request, CustomerCardRepository $customerCardRepository,  DefineQueryDate $defineQueryDate): Response
    {


        // utilisation du service qui définit si on utilise la query ou la session
        $day =  $defineQueryDate->returnDay($request);

        $date = new DateTimeImmutable($day . '00:01:00');
        $meetingDate = $date->modify('+1 day');

        // Récupérer tous les customerCards qui n'ont pas de staff id et qui colle avec la date
        $firstClient = $customerCardRepository->findOneBy(
            [
                'staff' => NULL,
                'meetingAt' => $meetingDate
            ]);
        $countNonAttributedClients = count($customerCardRepository->findBy(
            [
                'staff' => NULL,
                'meetingAt' => $meetingDate
            ]
        ));

        $daterestantes = $customerCardRepository->datesForCustomersWithoutRep();

        // si il y a encore des clients (firstclient)
        if ($firstClient != null) {
            //On récupère l'hotel d'arrivé
            $hotels = [];
            foreach ($firstClient->getTransferArrivals() as $arrival) {
                $hotels[] = $arrival->getToArrival();
            }
            $agency = $firstClient->getAgency();
            $hotel = $hotels[0];
            $paxAdults = $customerCardRepository->countPaxAdultsAttribbutionRep($meetingDate, $hotel, $agency);
            $paxChildren = $customerCardRepository->countPaxChildrenAttribbutionRep($meetingDate, $hotel, $agency);
            $paxBabies = $customerCardRepository->countPaxBabiesAttribbutionRep($meetingDate, $hotel, $agency);
        } else {
            $paxAdults = null;
            $paxChildren = null;
            $paxBabies = null;
        }
        
        if ($firstClient != NULL) {
            
            $form = $this->createForm(RepAttributionType::class, $firstClient);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                
                //Attribuer le représentant a toutes les personnes sans représentants avec la date 00:01 et qui ont le meme couple hotels-agence
                $staff = $firstClient->getStaff();
                
                // récupérer tous les clients qui n ont pas de staff et meeting = $date et qui ont le meme couple hotels-agence
                $customersWithoutRep = $customerCardRepository->findByForAttribbutionRep($meetingDate, $hotel, $agency);
                
                
                // pour chacun de ces objets, leur attribuer le staff correpondant
                foreach ($customersWithoutRep as $customer) {
                    $customer->setStaff($staff);
                }
                
                $customerCardRepository->save($customer, true);   
                            
                return $this->redirect($this->generateUrl('app_admin_team_manager'));

            }
            
            return $this->render('team_manager/attributionRepresentants.html.twig', [
                'firstClient' => $firstClient,
                'form' => $form,
                'controller_name' => 'team_managerController',
                'countNonAttributedClients' => $countNonAttributedClients,
                'date' => $date,
                'paxAdults' => $paxAdults,
                'paxChildren' => $paxChildren,
                'paxBabies' => $paxBabies,
                'daterestantes' => $daterestantes
            ]);
        }  

        else {
            return $this->render('team_manager/attributionRepresentants.html.twig', [
                'notClient' => true,
                'controller_name' => 'team_managerController',
                'date' => $date,
                'paxAdults' => $paxAdults,
                'paxChildren' => $paxChildren,
                'paxBabies' => $paxBabies,
                'daterestantes' =>  $daterestantes
            ]); 
        } 


    }

    // route qui affiche la liste des rep - client en fonction de la date
    // la liste doit comporter le nombre de client par rep 
    #[Route('/team-manager/replist', name: 'app_admin_team_manager_replist',methods:["POST", "GET"])]
    public function repList(CustomerCardRepository $customerCardRepository, UserRepository $userRepository, Request $request,DefineQueryDate $defineQueryDate): Response 
    {
        // utilisation du service qui définit si on utilise la query ou la session
        $day =  $defineQueryDate->returnDay($request);

        // on fixe la date que l'on va utiliser dans le filtre
        $date = new DateTimeImmutable($day . '00:01:00');
        $arrivalDate = $date->modify('-1 day');
        
        // récupération de tous les utilisateurs du site (pas nombreux a ne pas etre rep donc on checkera apres)
        $repUsers = $userRepository->findAll();

        // nombre de clients sans attributions
        $countNonAssignedClient = $customerCardRepository->countNumberNonAttributedMeetingsByDate($date);
        $users = [];
        $paxTab = []; // on va récupérer les paw globaux pour chaque rep
        $paxPerHotelAgency = []; // on va récupérer les pax pour chaque rep et par agence et hotels 
        $regroupementsClients = $customerCardRepository->regroupmentByDayStaffAgencyAndHotel($date);

        // pour chaque staff on va définir les infos a récupérer
        foreach($repUsers as $user) {
            if(in_array('ROLE_REP', $user->getRoles())){  
                $users[] = $user; 

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
        }

        return $this->render('team_manager/repList.html.twig', [
            'date' => $date,
            'users' => $users,
            'regroupementsClients' => $regroupementsClients,
            /* 'clientsListByRepAndDate' => $clientsListByRepAndDate,  */
            'countNonAssignedClient' => $countNonAssignedClient,
            'paxTab' => $paxTab,
            'paxPerHotelAgency' => $paxPerHotelAgency
        ]);

    } 

    // route qui affiche la fiche d un rep et ses assignations de clients pou un jour donné
    // la fiche doit permettre de changer la date du mmeting comme de rep
    #[Route('/team_manager/fiche/{user}/date', name: 'app_admin_team_manager_fiche_par_date',methods:["POST", "GET"])]
    public function ficheRepParDate(User $user, CustomerCardRepository $customerCardRepository, 
                                                MeetingPointRepository $meetingPointRepository,  
                                                UserRepository $userRepository,
                                                EntityManagerInterface $manager, Request $request,DefineQueryDate $defineQueryDate): Response 
    {

        $day =  $defineQueryDate->returnDay($request);
        $date = new DateTimeImmutable($day . '00:01:00');

        // attraper la liste des objets correpsondants au representant et au jour 
        $attributionClientsByRepAndDate = $customerCardRepository->findByStaffAndMeetingDate($user, $date);
        $meetingPoints = $meetingPointRepository->findAll();
        $users = $userRepository->findAll();


        $customersGrouping = $customerCardRepository->meetingRegroupmentByDayStaffAgencyAndHotel($date, $user);

        if (!empty($_POST) and $request->getMethod() == "POST") { 

            // récupérer toutes les personnes avec ce couple ce jour et staff 
            
            // pour chacun de ces objets, mettre a jour time, rep et place
            
            $testCustomersId = [];
            foreach ($request->request as $key => $currentRequest) {
                
                
                // convertir la clé en tableau
                $keyTab = explode("_", $key);

                $firstClient = $customerCardRepository->find($keyTab[1]);
                $staff = $firstClient->getStaff();
                $agency = $firstClient->getAgency();
                $hotels = []; 

                foreach ($firstClient->getTransferArrivals() as $arrivals) {
                    $hotels[] = $arrivals->getToArrival();
                }
                $hotel = $hotels[0];
                //dump('client id: ' . $firstClient->getId() . ' s: ' .$staff . ' a: ' . $agency . ' h: ' . $hotel);
                // pour chaque personne ce jour et ce staff, cet hotel et cet agence mettre a jour
                // 1st récupérer la liste de ces personnes
                $customersListForThisCouple = $customerCardRepository->findCustomersByDateHotelAgency($date, $hotel, $agency);
                    
                // 2d mettre a jour
                // récupérer chaque couple hotel agence pour ce rep a ce jour 
                // pour chaque résultats  

                foreach ($customersListForThisCouple as $customer ) {

                    // récupérer l'objet correspondant a l id
                    //$currentCustommerCard = $customerCardRepository->find($keyTab[1]);
                    $currentCustommerCard = $customer;

                    // si c est heure set l objet avec l heure
                    if ($keyTab[0] == 'hour') {
                        $dateTimeImmutable = new DateTimeImmutable($day . ' '. $currentRequest);
                        $currentCustommerCard->setMeetingAt($dateTimeImmutable);
                    }
                    // si c est l'endroit convertir l objet avec l endroit 
                    else if ($keyTab[0] == 'meetingPoint') {
                        $meetingPoint = $meetingPointRepository->find($currentRequest);
                        $currentCustommerCard->setMeetingPoint($meetingPoint);
                    }
                    // si c est l'endroit convertir l objet avec l endroit 
                    else if ($keyTab[0] == 'staff') {
                        $staff = $userRepository->find($currentRequest);
                        $currentCustommerCard->setStaff($staff);
                    }
                }

            }

            
            $manager->flush();
            return $this->redirect($this->generateUrl('app_admin_team_manager_replist'));
        }


        return $this->render('team_manager/attributionMeetings.html.twig', [
            "date" => $date,
            "attributionClientsByRepAndDate" => $attributionClientsByRepAndDate,
            "meetingPoints" => $meetingPoints, 
            "user" => $user,
            "users" => $users,
            'customersGrouping' => $customersGrouping
        ]);
    }



    // route qui affiche la fiche d un rep et ses assignations de clients pou un jour donné
    // la fiche doit permettre de changer la date du mmeting comme de rep
    #[Route('/team-manager/stickers',name: 'app_admin_stickers_par_date',methods:["POST", "GET"])]
    public function stickersParDate(CustomerCardRepository $customerCardRepository, 
                                    EntityManagerInterface $manager, 
                                    AgencyRepository $agencyRepository,
                                    Request $request,
                                    DefineQueryDate $defineQueryDate): Response 
    {

        $day =  $defineQueryDate->returnDay($request);
        $date = new DateTimeImmutable($day . '00:01:00');
        // sert a prévenir l utilisateur que lorsque qu il a changé les agences il faut aussi mettre a jour la date
        $formAgencySend = false;

        // récupérer les cutomerCard correspondant à la meeting date
        $meetings = $customerCardRepository->findByMeetingDate($date, true);

        $agencies = $agencyRepository->findAll();

        $checkFormAgencies = $request->request->get("form_check_agencies");
        if ( (isset($checkFormAgencies)) and ($checkFormAgencies == "ok") ){
            foreach ($agencies as $agency) {
                 
                $data = $request->request->get("agence_". $agency->getId());
                
                $test = ($data == "on") ? true : false;
                if ($agency->getIsActive() != $test) {
                    $agency->setIsActive($test);
                    $manager->flush($agency);
                } 
            
    
            }
            $this->addFlash(
                'danger',
                'Warning: To update the labels to be printed, please send back the date selection form'
            );
            $formAgencySend = true;
        }

        return $this->render('team_manager/stickers.html.twig', [
            "date" => $date,
            "meetings" => $meetings,
            "agencies" => $agencies,
            "formAgencySend" => $formAgencySend
        ]);

    }


}
