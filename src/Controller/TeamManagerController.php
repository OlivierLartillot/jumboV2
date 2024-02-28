<?php

namespace App\Controller;

use App\Entity\PrintingOptions;
use App\Entity\User;
use App\Form\RepAttributionType;
use App\Repository\AgencyRepository;
use App\Repository\AirportHotelRepository;
use App\Repository\CustomerCardRepository;
use App\Repository\MeetingPointRepository;
use App\Repository\PrintingOptionsRepository;
use App\Repository\TransferArrivalRepository;
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
    #[Route('/team-manager/briefings/attribution', name: 'app_admin_team_manager',methods:["POST", "GET"])]
    public function index(Request $request, 
                        CustomerCardRepository $customerCardRepository,  
                        DefineQueryDate $defineQueryDate,
                        TransferArrivalRepository $transferArrivalRepository,
                        UserRepository $userRepository
                        ): Response
    {


        // utilisation du service qui définit si on utilise la query ou la session
        $day =  $defineQueryDate->returnDay($request);

        $date = new DateTimeImmutable($day . '00:01:00');
        $meetingDate = $date->modify('+1 day');

        // Récupérer tous les transferArrival qui n'ont pas de staff id et qui colle avec la date
            $firstClient = $transferArrivalRepository->findOneBy(
            [
                'staff' => NULL,
                'meetingAt' => $meetingDate
            ]);
            $countNonAttributedClients = count($transferArrivalRepository->findBy(
                [
                    'staff' => NULL,
                    'meetingAt' => $meetingDate
                ]
            ));

        $daterestantes = $transferArrivalRepository->datesForCustomersWithoutRep();

        // le représentant "skip" si ivan ne veut pas attribuer des gens tout de suite
        $skipUsername = 'skip';
        $rep = $userRepository->findBy(['username' => $skipUsername]);
        $datesWithSkippedClients = $transferArrivalRepository->findDatesWithSkippedClients($rep);
        
        // si il y a encore des clients (firstclient)
        if ($firstClient != null) {
            //On récupère l'hotel d'arrivé
            
            $hotel = $firstClient->getToArrival();
            $agency = $firstClient->getCustomerCard()->getAgency();
            // !!! je les ai laissé dans le customer card mais on coompte les arrivals !!!
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
                
                // récupérer tous les arrivées qui n ont pas de staff et meeting = $date et qui ont le meme couple hotels-agence
                $arrivalsWithoutRep = $transferArrivalRepository->findByForAttribbutionRep($meetingDate, $hotel, $agency);
                
                // récupérer tous les clients qui n ont pas de staff et meeting = $date et qui ont le meme couple hotels-agence
                /* $customersWithoutRep = $customerCardRepository->findByForAttribbutionRep($meetingDate, $hotel, $agency); */
                
                // pour chacun de ces objets, leur attribuer le staff correpondant
                foreach ($arrivalsWithoutRep as $transferArrival) {
                    $transferArrival->setStaff($staff);
                }
                
                $transferArrivalRepository->save($transferArrival, true);   
                            
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
                'daterestantes' => $daterestantes,
                'skipDates' => $datesWithSkippedClients
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
                'daterestantes' =>  $daterestantes,
                'skipDates' => $datesWithSkippedClients
            ]); 
        } 
    }

    // route qui affiche la liste des rep - client en fonction de la date
    // la liste doit comporter le nombre de client par rep 
    #[Route('/team-manager/briefings/replist', name: 'app_admin_team_manager_replist',methods:["POST", "GET"])]
    public function repList(UserRepository $userRepository, TransferArrivalRepository $transferArrivalRepository, Request $request,DefineQueryDate $defineQueryDate): Response 
    {
        // utilisation du service qui définit si on utilise la query ou la session
        $day = $defineQueryDate->returnDay($request);

        // on fixe la date que l'on va utiliser dans le filtre
        $date = new DateTimeImmutable($day . '00:01:00');
        $arrivalDate = $date->modify('-1 day');

        // SI C EST QUELQU UN d'autre il n'est pas autorisé dans le sécurity
        // si on est un rep et dans une entreprise authorisée alors on récup juste l'user courant
        $imNotJustRep = ( 
            in_array('ROLE_HULK', $this->getUser()->getRoles()) or 
            in_array('ROLE_SUPERMAN', $this->getUser()->getRoles()) or 
            in_array('ROLE_AIRPORT_SUPERVISOR', $this->getUser()->getRoles()) or 
            in_array('ROLE_BRIEFING', $this->getUser()->getRoles()) 
        );

        // si je suis juste un rep !
        if (!$imNotJustRep) { 
            if (in_array('ROLE_REP', $this->getUser()->getRoles())) {
                // si le projet est configuré avec repCanChooseMeetingHour a true on peut récup, sinon access denied
                if ($this->getParameter('app.repCanChooseMeetingHour')) {
                    $repUsers = $userRepository->findBy(['id' => $this->getUser()->getId()]);
                } else {
                    return throw $this->createAccessDeniedException();
                }
            }
        } else {
            // récupération de tous les utilisateurs du site (pas nombreux a ne pas etre rep donc on checkera apres)
            $repUsers = $userRepository->findAll();
        }

        // nombre de arrivals sans attributions
        $countNonAssignedClient = $transferArrivalRepository->countNumberNonAttributedMeetingsByDate($date);
        $users = [];
        $paxTab = []; // on va récupérer les paw globaux pour chaque rep
        $paxPerHotelAgency = []; // on va récupérer les pax pour chaque rep et par agence et hotels 
       // $regroupementsClients = $customerCardRepository->regroupmentByDayStaffAgencyAndHotel($date);
       $regroupementsClients =[];
       // pour chaque staff on va définir les infos a récupérer
       foreach($repUsers as $user) {
           if(in_array('ROLE_REP', $user->getRoles())){  
               $users[] = $user; 
            
               // pour la recherche date == meetingDate on récupere les pax de date -1 pour avoir les arrivées
               $paxTab[$user->getUsername()]['adults'] = $transferArrivalRepository->staffPaxByDate($user, $date, "adults");
               $paxTab[$user->getUsername()]['children'] = $transferArrivalRepository->staffPaxByDate($user, $date, "children");
               $paxTab[$user->getUsername()]['babies'] = $transferArrivalRepository->staffPaxByDate($user, $date, "babies");
           
       
               $regroupements = $transferArrivalRepository->meetingRegroupmentByDayStaffAgencyAndHotel($date, $user);
           
               $regroupementsClients[] = $regroupements;
               foreach ($regroupements as $transferArrival) {
                  
                   $hotels = [];
                   $agency = $transferArrival->getCustomerCard()->getAgency();
                   $hotels[] = $transferArrival->getToArrival();

                   //dd($transferArrival->getMeetingAt());
                   $paxRegroupAdults = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($hotels[0],$agency, $user, 'adults', $transferArrival->getMeetingAt(), $transferArrival->getMeetingPoint() );
                   $paxRegroupChildren = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($hotels[0],$agency, $user, 'children', $transferArrival->getMeetingAt(), $transferArrival->getMeetingPoint());
                   $paxRegroupBabies = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($hotels[0],$agency, $user, 'babies', $transferArrival->getMeetingAt(), $transferArrival->getMeetingPoint()); 
              
                   $paxPerHotelAgency[$user->getUsername().'_adults'][$agency->getId() . '_'.$hotels[0]->getId() . '_'. $transferArrival->getMeetingAt()->format('H:i') . '_'. $transferArrival->getMeetingPoint()] =  $paxRegroupAdults;
                   $paxPerHotelAgency[$user->getUsername().'_children'][$agency->getId() . '_'.$hotels[0]->getId() . '_'. $transferArrival->getMeetingAt()->format('H:i') . '_'. $transferArrival->getMeetingPoint()] =  $paxRegroupChildren;
                   $paxPerHotelAgency[$user->getUsername().'_babies'][$agency->getId() . '_'.$hotels[0]->getId() . '_'. $transferArrival->getMeetingAt()->format('H:i') . '_'. $transferArrival->getMeetingPoint()] =  $paxRegroupBabies;
                
                } 
            }
        }  

        return $this->render('team_manager/repList.html.twig', [
            'date' => $date,
            'users' => $users,
            'regroupementsClients' => $regroupementsClients,
            'countNonAssignedClient' => $countNonAssignedClient,
            'paxTab' => $paxTab,
            'paxPerHotelAgency' => $paxPerHotelAgency,
            'imNotJustRep' => $imNotJustRep
        ]);

    } 

    // route qui affiche la fiche d un rep et ses assignations de clients pou un jour donné
    // la fiche doit permettre de changer la date du mmeting comme de rep
    #[Route('/team-manager/briefings/fiche/{user}/date', name: 'app_admin_team_manager_fiche_par_date',methods:["POST", "GET"])]
    public function ficheRepParDate(User $user,
                                    TransferArrivalRepository $transferArrivalRepository,
                                    MeetingPointRepository $meetingPointRepository,  
                                    UserRepository $userRepository,
                                    EntityManagerInterface $manager, Request $request,DefineQueryDate $defineQueryDate): Response 
    {

        $day =  $defineQueryDate->returnDay($request);
        $date = new DateTimeImmutable($day . '00:01:00');

        // SI C EST QUELQU UN d'autre il n'est pas autorisé dans le sécurity
        // si on est un rep et dans une entreprise authorisée alors on récup juste l'user courant
        $imNotJustRep = ( 
            in_array('ROLE_HULK', $this->getUser()->getRoles()) or 
            in_array('ROLE_SUPERMAN', $this->getUser()->getRoles()) or 
            in_array('ROLE_AIRPORT_SUPERVISOR', $this->getUser()->getRoles()) or 
            in_array('ROLE_BRIEFING', $this->getUser()->getRoles()) 
        );

        // si je suis juste un rep !
        if (!$imNotJustRep) { 
            if (in_array('ROLE_REP', $this->getUser()->getRoles())) {
                // si le projet est configuré avec repCanChooseMeetingHour a true on peut continuer, sinon access denied
                // par contre le user doit etre le meme que le currentUser
                if ((!$this->getParameter('app.repCanChooseMeetingHour')) or ($user != $this->getUser())) {
                    return throw $this->createAccessDeniedException();
                } 
            }
        }

        //dd($day);06/09
        // attraper la liste des objets correpsondants au representant et au jour du meeting
        //$attributionClientsByRepAndDate = $customerCardRepository->findByStaffAndMeetingDate($user, $date);
        $meetingPoints = $meetingPointRepository->findAll();
        
        $users = [];  /*   $users = $userRepository->findAll(); MAIS EN METTANT TRIE + SKIP ET NO rep en premier */
        // trouve le skip pour le mettre en premier puis le No rep
        $skip =  $userRepository->findOneBy(['username' => 'skip']);
        if ($skip != null) {
            $users[] = $skip; 
        }
        $noRep =  $userRepository->findOneBy(['username' => 'no rep']);
        if ($noRep != null) {
            $users[] = $noRep; 
        }
        
        $usersRestant = $userRepository->findBy([], ['username' => 'ASC']);
        foreach ($usersRestant as $userToPut) {
            
            // mais si $user = skip tu le sautes !!!
            if ( (strtolower($userToPut->getUsername()) !== 'skip' ) and (strtolower($userToPut->getUsername()) !== 'no rep') ){ 
                $users [] = $userToPut;
            }
            
        }

        // on a un seul user dans cette page
        // Ne sert pas pour les pax car c'est groupé, on ne peut pas ajouté
        $regroupements = $transferArrivalRepository->meetingRegroupmentByDayStaffAgencyAndHotel($date, $user);
        //$customersGroupingPax = $transferArrivalRepository->meetingRegroupmentPax($date, $user);
        $paxTab = [];
        foreach ($regroupements as $transferArrival) {

            $agency = $transferArrival->getCustomerCard()->getAgency();
            $hotels = [];
            $hotels[] = $transferArrival->getToArrival();

/*             $paxRegroupAdults = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($date,$hotels[0],$agency, $user, 'adults', null);
            $paxRegroupChildren = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($date,$hotels[0],$agency, $user, 'children', null);
            $paxRegroupBabies = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($date,$hotels[0],$agency, $user, 'babies', null);
            
            $paxPerHotelAgency['adults'][$agency->getId() . '_'.$hotels[0]->getId().'_'.$transferArrival->getflightNumber()] =  $paxRegroupAdults;
            $paxPerHotelAgency['children'][$agency->getId() . '_'.$hotels[0]->getId().'_'.$transferArrival->getflightNumber()] =  $paxRegroupChildren;
            $paxPerHotelAgency['babies'][$agency->getId() . '_'.$hotels[0]->getId().'_'.$transferArrival->getflightNumber()] =  $paxRegroupBabies; */
            
            $paxRegroupAdults = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($hotels[0],$agency, $user, 'adults', $transferArrival->getMeetingAt(), $transferArrival->getMeetingPoint() );
            $paxRegroupChildren = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($hotels[0],$agency, $user, 'children', $transferArrival->getMeetingAt(), $transferArrival->getMeetingPoint());
            $paxRegroupBabies = $transferArrivalRepository->paxForRegroupementHotelAndAgencies($hotels[0],$agency, $user, 'babies', $transferArrival->getMeetingAt(), $transferArrival->getMeetingPoint()); 
       
            $paxPerHotelAgency['adults'][$agency->getId() . '_'.$hotels[0]->getId() . '_'.$transferArrival->getMeetingAt()->format('H:i') . '_'. $transferArrival->getMeetingPoint()] =  $paxRegroupAdults;
            $paxPerHotelAgency['children'][$agency->getId() . '_'.$hotels[0]->getId() . '_'.$transferArrival->getMeetingAt()->format('H:i') . '_'. $transferArrival->getMeetingPoint()] =  $paxRegroupChildren;
            $paxPerHotelAgency['babies'][$agency->getId() . '_'.$hotels[0]->getId() . '_'.$transferArrival->getMeetingAt()->format('H:i') . '_'. $transferArrival->getMeetingPoint()] =  $paxRegroupBabies;



        }

        // calcul les pax de chaque regroupement
        foreach ($paxPerHotelAgency as $key => $itemAge) {
            $paxTab[$key] = 0;
            foreach ($itemAge as $age) {
                $paxTab[$key] += $age;
            }
        }
        // calcul du pax total
        $countPax = $paxTab['adults'] + ($paxTab['children']*0.5);


        if (!empty($_POST) and $request->getMethod() == "POST") { 

            // récupérer toutes les personnes avec ce couple ce jour et staff 
            
            // pour chacun de ces objets, mettre a jour time, rep et place
            
            foreach ($request->request as $key => $currentRequest) {
                
                /* dump($key .' - '. $currentRequest); */
                // convertir la clé en tableau
                $keyTab = explode("_", $key);
    
               /*  dump($keyTab); */
                $transfer = $transferArrivalRepository->find($keyTab[1]);
                $firstClient =  $transfer->getCustomerCard();
                $staff = $transfer->getStaff();
                $agency = $firstClient->getAgency();

                $hotels = []; 

                foreach ($firstClient->getTransferArrivals() as $arrivals) {
                    $hotels[] = $arrivals->getToArrival();
                }
                $hotel = $hotels[0];
                //dump('client id: ' . $firstClient->getId() . ' s: ' .$staff . ' a: ' . $agency . ' h: ' . $hotel);
                // pour chaque personne ce jour et ce staff, cet hotel et cet agence mettre a jour
                // 1st récupérer la liste de ces personnes
                $arrivalsListForThisCouple = $transferArrivalRepository->findCustomersByDateHotelAgency($date, $hotel, $agency, null,$transfer->getMeetingPoint());
               
                // 2d mettre a jour
                // récupérer chaque couple hotel agence pour ce rep a ce jour 
                // pour chaque résultats  

                foreach ($arrivalsListForThisCouple as $currentArrival ) {

                    
                    // récupérer l'objet correspondant a l id
                    //$currentCustommerCard = $customerCardRepository->find($keyTab[1]);

                    /* dump($keyTab[0] .': '. $currentRequest .' - '.$currentArrival->getId()); */

                    // si c est heure set l objet avec l heure
                    if ($keyTab[0] == 'hour') {
                        $dateTimeImmutable = new DateTimeImmutable($day . ' '. $currentRequest);
                        $currentArrival->setMeetingAt($dateTimeImmutable);
                    }
                    // si c est l'endroit convertir l objet avec l endroit 
                    else if ($keyTab[0] == 'meetingPoint') {
                        $meetingPoint = $meetingPointRepository->find($currentRequest);
                        $currentArrival->setMeetingPoint($meetingPoint);
                    }
                    // si c est l'endroit convertir l objet avec l endroit 
                    else if ($keyTab[0] == 'staff') {

                        if (!$imNotJustRep) {
                            return throw $this->createAccessDeniedException('Access Denied: You do not have the right to change the representative\'s assignment');
                        } 
                        $staff = $userRepository->find($currentRequest);
                        $currentArrival->setStaff($staff);
                    }          
                }
            }
           
            
            $manager->flush();
            return $this->redirect($this->generateUrl('app_admin_team_manager_replist'));
        }

        return $this->render('team_manager/attributionMeetings.html.twig', [
            "date" => $date,
            //"attributionClientsByRepAndDate" => $attributionClientsByRepAndDate,
            "meetingPoints" => $meetingPoints, 
            "user" => $user,
            "users" => $users,
            "paxPerHotelAgency" => $paxPerHotelAgency,
            'regroupements' => $regroupements,
            'paxTab' => $paxTab,
            'countPax' => $countPax,
            'imNotJustRep' => $imNotJustRep
        ]);
    }


    // route qui affiche la fiche d un rep et ses assignations de clients pou un jour donné
    // la fiche doit permettre de changer la date du mmeting comme de rep
    #[Route('/team-manager/briefings/fiche/{user}/date/details', name: 'app_admin_team_manager_fiche_par_date_details',methods:["POST", "GET"])]
    public function ficheRepParDateDetails( User $user, 
                                            TransferArrivalRepository $transferArrivalRepository,
                                            MeetingPointRepository $meetingPointRepository,  
                                            UserRepository $userRepository,
                                            EntityManagerInterface $manager, Request $request,DefineQueryDate $defineQueryDate
                                          ): Response 
    {

        $day =  $defineQueryDate->returnDay($request);
        $date = new DateTimeImmutable($day . '00:01:00');

        // SI C EST QUELQU UN d'autre il n'est pas autorisé dans le sécurity
        // si on est un rep et dans une entreprise authorisée alors on récup juste l'user courant
        $imNotJustRep = ( 
            in_array('ROLE_HULK', $this->getUser()->getRoles()) or 
            in_array('ROLE_SUPERMAN', $this->getUser()->getRoles()) or 
            in_array('ROLE_AIRPORT_SUPERVISOR', $this->getUser()->getRoles()) or 
            in_array('ROLE_BRIEFING', $this->getUser()->getRoles()) 
        );

        // si je suis juste un rep !
        if (!$imNotJustRep) { 
            if (in_array('ROLE_REP', $this->getUser()->getRoles())) {
                // si le projet est configuré avec repCanChooseMeetingHour a true on peut continuer, sinon access denied
                if ((!$this->getParameter('app.repCanChooseMeetingHour')) or ($user != $this->getUser())) {
                    return throw $this->createAccessDeniedException();
                } 
            }
        }

        
        // attraper la liste des objets correpsondants au representant et au jour 
        $attributionClientsByRepAndDate = $transferArrivalRepository->findByStaffAndMeetingDate($user, $date);

        $meetingPoints = $meetingPointRepository->findAll();
        $users = [];  /*   $users = $userRepository->findAll(); */
        // trouve le skip pour le mettre en premier puis le No rep
        $skip =  $userRepository->findOneBy(['username' => 'skip']);
        if ($skip != null) {
            $users[] = $skip; 
        }
        $noRep =  $userRepository->findOneBy(['username' => 'no rep']);
        if ($noRep != null) {
            $users[] = $noRep; 
        }
        
        $usersRestant = $userRepository->findBy([], ['username' => 'ASC']);
        foreach ($usersRestant as $userToPut) {
            
            // mais si $user = skip tu le sautes !!!
            if ( (strtolower($userToPut->getUsername()) !== 'skip' ) and (strtolower($userToPut->getUsername()) !== 'no rep') ){ 
                $users [] = $userToPut;
            }
            
        }
    

        // on check - pour le 1er client, l heure, puis le rep, puis le lieu, puis le 2eme client, l heure ... etc
        // si jamais on veut mettre à jour à partir d ici
        if (!empty($_POST) and $request->getMethod() == "POST") { 

            // récupérer toutes les personnes avec ce couple ce jour et staff 
            
            // pour chacun de ces objets, mettre a jour time, rep et place
        
            foreach ($request->request as $key => $currentRequest) {
                
                // convertir la clé en tableau
                $keyTab = explode("_", $key);

                $transfer = $transferArrivalRepository->find($keyTab[1]);
                $client = $transfer->getCustomerCard();
                $staff = $transfer->getStaff();
                $agency = $client->getAgency();
                $flightNumber = $transfer->getFlightNumber();
                $hotel = $transfer->getToArrival();

                //  mettre à jour le transfer

                    // si c est heure set l objet avec l heure
                    if ($keyTab[0] == 'hour') {
                        $dateTimeImmutable = new DateTimeImmutable($day . ' '. $currentRequest);
                        $transfer->setMeetingAt($dateTimeImmutable);
                    }
                    // si c est l'endroit convertir l objet avec l endroit 
                    else if ($keyTab[0] == 'meetingPoint') {
                        $meetingPoint = $meetingPointRepository->find($currentRequest);
                        $transfer->setMeetingPoint($meetingPoint);
                    }
                    // si c est l'endroit convertir l objet avec l endroit 
                    else if ($keyTab[0] == 'staff') {
                        if (!$imNotJustRep) {
                            return throw $this->createAccessDeniedException('Access Denied: You do not have the right to change the representative\'s assignment');
                        } 
                        $staff = $userRepository->find($currentRequest);
                        $transfer->setStaff($staff);
                  
                    }
            }
           

            
            $manager->flush();
            return $this->redirect($this->generateUrl('app_admin_team_manager_replist'));
        }

        $paxTab = [];
        $paxTab['adults'] = 0;
        $paxTab['children'] = 0;
        $paxTab['babies'] = 0;
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
            'countPax' => $countPax,
            'imNotJustRep' => $imNotJustRep,

        ]);
    }


    // route qui affiche la fiche d un rep et ses assignations de clients pou un jour donné
    // la fiche doit permettre de changer la date du mmeting comme de rep
    #[Route('/team-manager/briefings/stickers',name: 'app_admin_stickers_par_date',methods:["POST", "GET"])]
    public function stickersParDate(TransferArrivalRepository $transferArrivalRepository,
                                    EntityManagerInterface $manager, 
                                    AgencyRepository $agencyRepository,
                                    AirportHotelRepository $airportHotelRepository,
                                    PrintingOptionsRepository $printingOptionsRepository,
                                    Request $request,
                                    DefineQueryDate $defineQueryDate,
                                    ): Response 
    {
        $day =  $defineQueryDate->returnDay($request);
       
        $date = new DateTimeImmutable($day . '00:01:00');
        // sert a prévenir l utilisateur que lorsque qu il a changé les agences il faut aussi mettre a jour la date
        $formAgencySend = false;
        $user = $this->getUser();
        $agencies = $agencyRepository->findAll();
        $airports = $airportHotelRepository->findBy(['isAirport' => true]);
        
        // regarder si une fiche Printing Options existe pour cet utilisateur
        $printingOptionsUser = $printingOptionsRepository->findOneBy(["user" => $user]); 
        
        $choosenAirports = [];
        $choosenAgencies = [];
        if ($printingOptionsUser != null) {
            foreach ($printingOptionsUser->getAirport() as $airport) {
                $choosenAirports[] = $airport;
            }
            foreach ($printingOptionsUser->getAgencies() as $agency) {
                $choosenAgencies[] = $agency;
            }
        }
        
        

        // si y a le parametre clientArrival dans l adresse alors le tableau des meetings (transferArrival)
        // n'aura que le transferArrival de ce client
        if ($request->get("ta")) {
            $meetings = [];
            $meetings =  $transferArrivalRepository->findBy(['id' => $request->get("ta")]);
            $date = $meetings[0]->getDate();
            /* dump("on a le param d url");
            dd($meetings); */
        } else {
            //sinon
            // récupérer les cutomerCard correspondant à la meeting date
            $meetings = $transferArrivalRepository->findByMeetingDate($date, $choosenAirports, $choosenAgencies);
        
            $checkFormAgencies = $request->request->get("form_check_agencies");
            if ( (isset($checkFormAgencies)) and ($checkFormAgencies == "ok") ){
                foreach ($agencies as $agency) {
                    
                    $data = $request->request->get("agence_". $agency->getId());
                    
                    $test = ($data == "on") ? true : false;
                    
                    // si non, la créer
                    
                    if ($printingOptionsUser == null) {
                        $printingOptionsUser = new PrintingOptions();
                        $printingOptionsUser->setUser($user);
                    } 
                    
                    if($test) {
                        $printingOptionsUser->addAgency($agency);
                    } else {
                        $printingOptionsUser->removeAgency($agency);
                    }
                
                    foreach ($airports as $airport) { 
                        $data = $request->request->get("airport_". $airport->getId());
                        $test = ($data == "on") ? true : false;
                        
                        // si c est on on rajoute
                        if($test) {
                            $printingOptionsUser->addAirport($airport);
                        } else {
                            $printingOptionsUser->removeAirport($airport);
                        }
                        $manager->persist($printingOptionsUser);
                        $manager->flush();                   
                    }
                }
                $this->addFlash(
                    'danger',
                    'Warning: To update the labels to be printed, please send back the date selection form'
                );
                $formAgencySend = true;
            }
        }

        return $this->render('team_manager/stickers.html.twig', [
            "date" => $date,
            "meetings" => $meetings,
            "agencies" => $agencies,
            "airports" => $airports,
            "formAgencySend" => $formAgencySend,
            "printingOptionsUser" => $printingOptionsUser,
        ]);

    }

    // route qui affiche la fiche d un rep et ses assignations de clients pou un jour donné
    // la fiche doit permettre de changer la date du mmeting comme de rep
    #[Route('/team-manager/stickers-test',name: 'app_admin_stickers_par_date_test',methods:["POST", "GET"])]
    public function stickersParDateBis(CustomerCardRepository $customerCardRepository, 
                                        EntityManagerInterface $manager, 
                                        AgencyRepository $agencyRepository,
                                        AirportHotelRepository $airportHotelRepository,
                                        PrintingOptionsRepository $printingOptionsRepository,
                                        Request $request,
                                        DefineQueryDate $defineQueryDate): Response 
    {
        $day =  $defineQueryDate->returnDay($request);

        $date = new DateTimeImmutable($day . '00:01:00');
        // sert a prévenir l utilisateur que lorsque qu il a changé les agences il faut aussi mettre a jour la date
        $formAgencySend = false;
        $user = $this->getUser();

        
        $agencies = $agencyRepository->findAll();
        $airports = $airportHotelRepository->findBy(['isAirport' => true]);
        
        // regarder si une fiche Printing Options existe pour cet utilisateur
        $printingOptionsUser = $printingOptionsRepository->findOneBy(["user" => $user]); 
        
        $printingOptionsUserExist = true;
        
        $choosenAirports = [];
        $choosenAgencies = [];

        $choosenAirports = [];
        $choosenAgencies = [];
        if ($printingOptionsUser != null) {
            foreach ($printingOptionsUser->getAirport() as $airport) {
                $choosenAirports[] = $airport;
            }
            foreach ($printingOptionsUser->getAgencies() as $agency) {
                $choosenAgencies[] = $agency;
            }
        }


        // récupérer les cutomerCard correspondant à la meeting date
        $meetings = $customerCardRepository->findByMeetingDate($date, $choosenAirports, $choosenAgencies);


        return $this->render('team_manager/stickers-bis.html.twig');

    }


}
