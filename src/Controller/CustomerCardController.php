<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\CustomerCard;
use App\Entity\StatusHistory;
use App\Entity\TransferArrival;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\CustomerCardType;
use App\Form\TransferArrivalNewType;
use App\Repository\AgencyRepository;
use App\Repository\AirportHotelRepository;
use App\Repository\CommentRepository;
use App\Repository\CustomerCardRepository;
use App\Repository\MeetingPointRepository;
use App\Repository\StatusHistoryRepository;
use App\Repository\StatusRepository;
use App\Repository\TransferArrivalRepository;
use App\Repository\TransferDepartureRepository;
use App\Repository\TransferInterHotelRepository;
use App\Repository\TransferVehicleArrivalRepository;
use App\Repository\TransportCompanyRepository;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerCardController extends AbstractController
{
    #[Route('customer/card', name: 'app_customer_card_index', methods: ['GET'])]
    public function index(Request $request, 
                          CustomerCardRepository $customerCardRepository, 
                          StatusRepository $statusRepository, 
                          UserRepository $userRepository,
                          AgencyRepository $agencyRepository,
                          AirportHotelRepository $airportHotelRepository, 
                          PaginatorInterface $paginator): Response
    {
        /*
        $path = $this->getParameter('fiches_clients_folder');
        $nom_fichier = $path . date('d-m-Y-h_i_s').'.csv';
        fopen($nom_fichier, 'w');
        $records = [
            [1, 2, 5],
            ['foo', 'bar', 'baz'],
            ['john', 'doe', 'john.doe@example.com'],
        ];
        
        try {
            $writer = Writer::createFromPath($nom_fichier, 'w+');
            $writer->insertAll($records);
        } catch (CannotInsertRecord $e) {
            $e->getRecord(); //returns [1, 2, 3]
        } 
        */


        //Listes des informations a afficher dans les tris
        $agencies = $agencyRepository->findAllAsc();
        $hotels = $airportHotelRepository->findAllAsc();
   
        
        $statusList = $statusRepository->findAll();
        $users = $userRepository->findAll();
        $reps = [];
        foreach ($users as $user) {
            if (in_array("ROLE_REP", $user->getRoles() )) {
                if ($user->getUsername() != "skip") {
                    $reps[] = $user;
                }
            }
        }

        $empty = true;
        // on vérifie si on a cliqué sur envoyé
        if (count($request->query) > 0) {
            // si y en a qu un et que c est page alors tu renvoie la page de base donc empty true
            if ( (count($request->query) === 1) and $request->query->get('page') ) {
                $empty = true;
            } else {
                //on vérifie si on a envoyé au moins un élément de tri (donc différent de page)
                foreach ($request->query as $param) {    
                    if ($param != null) {
                        $empty = false;
                        break;
                    }
                }

            }
        }
    
        // si y a au moins un élément envoyé au tri
        if ($empty == false) {

            // alors on peut récupérer les données et les filtrer
            $rep = $request->query->get('reps');
            // si on est uniquement rep(role_rep & user) d ou  == 2 
            if (( count($this->getUser()->getRoles()) == 2 ) and (in_array('ROLE_REP', $this->getUser()->getRoles()))) {
                // si on envoie un autre rep => denied
                if ($userRepository->find($rep) != $this->getUser()) {
                    return throw $this->createAccessDeniedException();
                };
            }

            $customerPresence = $request->query->get('customerPresence');


            // si tout va bien  on envoie la dql 
            $dateStart = $request->query->get('dateStart');
            $dateEnd = $request->query->get('dateEnd');

            $dateStart = ($dateStart != "") ? New DateTimeImmutable($dateStart . '00:00:00') : null ;
            $dateEnd = ($dateEnd != "") ? $dateEnd = New DateTimeImmutable($dateEnd . '23:59:59') : null;
            
            $natureTransfer = $request->query->get('natureTransfer');
            $status = $request->query->get('status');

            //! hotels
            $hotel = $request->query->get('hotel');
            $agency = $request->query->get('agency');
            $flightNumber = $request->query->get('flightNumber');
            $search = $request->query->get('search');
                
            $flightNumber = ($flightNumber == "") ? "all" : $flightNumber;


            // si c est présence
            if ($customerPresence == 1){
                // la requete qui execute la recherche
                $results = $customerCardRepository->customerCardPageSearchPresence($dateStart, $dateEnd, $rep, $status, $agency, $hotel, $search, $flightNumber);
            } else { // si c est opération
                $results = $customerCardRepository->customerCardPageSearchOperation($dateStart, $dateEnd, $rep, $status, $agency, $hotel, $search, $natureTransfer, $flightNumber);

            }

            $count = count($results);
            

            $pagination = $paginator->paginate(
                $results,
                $request->query->getInt('page', 1),
                27,
            );

            //dd($results);
            // et on envoi la nouvelle page 
            return $this->render('customer_card/index.html.twig', [
                'customer_cards' => $pagination,
                'count' => $count,
                'agencies' => $agencies,
                'hotels' => $hotels,
                'statusList' => $statusList,
                'reps' => $reps,
                'clientsMenu' => true
            ]);
            
            
            // sinon renvoyer la page de base
            

        }
        // sinon on renvoie la page de base 
        // todo ? peut etre un message flash ?
        

        // quand on arrive sur la page on récupere les mouvements du jour
        $findAllByNow = $customerCardRepository->findByNow();
        $count = count($findAllByNow);
        $pagination = $paginator->paginate(
            $findAllByNow,
            $request->query->getInt('page', 1),
            27,
        );
        return $this->render('customer_card/index.html.twig', [
            'customer_cards' => $pagination,
            'agencies' => $agencies,
            'hotels' => $hotels,
            'statusList' => $statusList,
            'reps' => $reps,
            'count' => $count,
            'clientsMenu' => true
        ]);
    }

    #[Route('/search', name: 'app_customer_card_search', methods: ['GET', 'POST'])]
    public function search(Request $request, CustomerCardRepository $customerCardRepository, PaginatorInterface $paginator): Response
    {
        $results = $customerCardRepository->search($request->request->get('search'));
        $count = count($results);

        $pagination = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            27,
        );

        return $this->render('customer_card/search.html.twig', [
            'customer_cards' => $pagination,
            'count' => $count,
        ]); 
        
    }
    
    #[Route('team-manager/pax', name: 'app_customer_card_pax', methods: ['GET'])]
    public function pax(Request $request, CustomerCardRepository $customerCardRepository, UserRepository $userRepository, StatusRepository $statusRepository): Response
    { 
     
        $users = $userRepository->findBy([],['username' => 'ASC']);
        $rep = false;
        $reps = [];
        foreach ($users as $user) {
            if (in_array("ROLE_REP", $user->getRoles() )) {
                if ($user->getUsername() != 'skip') {
                    $reps[] = $user;
                }
            }
        }
        // on va ranger les résultats dans un tableau pour les transmettre a la vue en une fois
        $results = [];
        // si on a cliqué sur envoyer
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
                $dateStart = $request->query->get('dateStart');
                $dateEnd = $request->query->get('dateEnd');
                $rep = ($request->query->get('reps') != "all") ? $userRepository->find($request->query->get('reps')) : "all";    
            } 
        } else {
            $dateStart = new DateTimeImmutable('now');
            $dateStart = $dateStart->format('Y-m-01');
            $dateEnd = new DateTimeImmutable('now');
            $dateEnd = $dateEnd->format('Y-m-d');
            $rep="all";
        }


        $noShow = $statusRepository->findOneBy(["name"=> "No Show"]);

         //pax adults de tel date à tel date
        $results['nbrTotalAdults'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $rep, "adults");
        $results['nbrTotalAdults'] = intval($results['nbrTotalAdults']);
        $results['nbrTotalChildren'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $rep, "children");
        $results['nbrTotalChildren'] = intval($results['nbrTotalChildren']);
        $results['paxTotalChildren'] = $results['nbrTotalChildren'] * 0.5;
        $results['nbrTotalbabies'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $rep, "babies");
        $results['nbrTotalbabies'] = intval($results['nbrTotalbabies']);
        $results['sumNbrTotal'] = $results['nbrTotalAdults'] + $results['nbrTotalChildren'] + $results['nbrTotalbabies'];
        $results['sumPaxTotal'] = $results['nbrTotalAdults'] + $results['paxTotalChildren'];
        // pax adults sans no show
        $results['nbrAdultsShow'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $rep, "adults", $noShow);
        $results['nbrAdultsShow'] = intval($results['nbrAdultsShow']);
        $results['nbrChildrenShow'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $rep, "children", $noShow);
        $results['nbrChildrenShow'] = intval($results['nbrChildrenShow']);
        $results['paxChildrenShow'] = $results['nbrChildrenShow'] * 0.5;
        $results['nbrBabiesShow'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $rep, "babies", $noShow);
        $results['nbrBabiesShow'] = intval($results['nbrBabiesShow']);
        $results['sumNbrShow'] = $results['nbrAdultsShow'] + $results['nbrChildrenShow'] + $results['nbrBabiesShow'];
        $results['sumPaxShow'] = $results['nbrAdultsShow'] + $results['paxChildrenShow'];
        
        if ($rep == "all") {
            $tabDetails = [];
            $i = 0;
            
            foreach ($reps as $repUser) {
             
                $tabDetails[$i]['nbrTotalAdults'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $repUser, "adults");
                $tabDetails[$i]['nbrTotalAdults'] = intval($tabDetails[$i]['nbrTotalAdults']);
                $tabDetails[$i]['nbrTotalChildren'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $repUser, "children");
                $tabDetails[$i]['nbrTotalChildren'] = intval($tabDetails[$i]['nbrTotalChildren']);
                $tabDetails[$i]['paxTotalChildren'] = $tabDetails[$i]['nbrTotalChildren'] * 0.5;
                $tabDetails[$i]['nbrTotalbabies'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $repUser, "babies");
                $tabDetails[$i]['nbrTotalbabies'] = intval($tabDetails[$i]['nbrTotalbabies']);
                $tabDetails[$i]['sumNbrTotal'] = $tabDetails[$i]['nbrTotalAdults'] + $tabDetails[$i]['nbrTotalChildren'] + $tabDetails[$i]['nbrTotalbabies'];
                $tabDetails[$i]['sumPaxTotal'] = $tabDetails[$i]['nbrTotalAdults'] + $tabDetails[$i]['paxTotalChildren'];



                // pax adults sans no show
                $tabDetails[$i]['nbrAdultsShow'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $repUser, "adults", $noShow);
                $tabDetails[$i]['nbrAdultsShow'] = intval($tabDetails[$i]['nbrAdultsShow']);
                $tabDetails[$i]['nbrChildrenShow'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $repUser, "children", $noShow);
                $tabDetails[$i]['nbrChildrenShow'] = intval($tabDetails[$i]['nbrChildrenShow']);
                $tabDetails[$i]['paxChildrenShow'] = $tabDetails[$i]['nbrChildrenShow'] * 0.5;
                $tabDetails[$i]['nbrBabiesShow'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $repUser, "babies", $noShow);
                $tabDetails[$i]['nbrBabiesShow'] = intval($tabDetails[$i]['nbrBabiesShow']);

                $tabDetails[$i]['sumNbrShow'] = $tabDetails[$i]['nbrAdultsShow'] + $tabDetails[$i]['nbrChildrenShow'] + $tabDetails[$i]['nbrBabiesShow'];
                $tabDetails[$i]['sumPaxShow'] = $tabDetails[$i]['nbrAdultsShow'] + $tabDetails[$i]['paxChildrenShow'];

                $i++;
            }
        } 
    
        if (!isset($tabDetails)) { $tabDetails = [];}

        return $this->render('customer_card/calcul_pax_rep.html.twig', [
            'reps' => $reps,
            'rep' => $rep,
            'results' => $results,
            'tabDetailsRep' => $tabDetails
        ]); 
    }

    #[Route('/pax/rep/{id}', name: 'app_customer_card_pax_par_rep', methods: ['GET', 'POST'])]
    public function paxParRep(Request $request, User $user, CustomerCardRepository $customerCardRepository, StatusRepository $statusRepository): Response
    { 
        
        //! Attention si l id est différent du user courant, pas le droit
        if ($user != $this->getUser()) {
            return throw $this->createAccessDeniedException();
        }

        // on va ranger les résultats dans un tableau pour les transmettre a la vue en une fois
        $results = [];
        // si on a cliqué sur envoyé
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
                // todo  : alors on peut récupérer les données et les filtrer
                $dateStart = $request->query->get('dateStart');
                $dateEnd = $request->query->get('dateEnd');
            } 
        } else {
            $dateStart = new DateTimeImmutable('now');
            $dateStart = $dateStart->format('Y-m-01');
            $dateEnd = new DateTimeImmutable('now');
            $dateEnd = $dateEnd->format('Y-m-d');
        }

        $noShow = $statusRepository->findOneBy(["name"=> "No Show"]);

        //pax adults de tel date à tel date
        $results['nbrTotalAdults'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $this->getUser(), "adults");
        $results['nbrTotalAdults'] = intval($results['nbrTotalAdults']);
        $results['nbrTotalChildren'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $this->getUser(), "children");
        $results['nbrTotalChildren'] = intval($results['nbrTotalChildren']);
        $results['paxTotalChildren'] = $results['nbrTotalChildren'] * 0.5;
        $results['nbrTotalbabies'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $this->getUser(), "babies");
        $results['nbrTotalbabies'] = intval($results['nbrTotalbabies']);
        $results['sumNbrTotal'] = $results['nbrTotalAdults'] + $results['nbrTotalChildren'] + $results['nbrTotalbabies'];
        $results['sumPaxTotal'] = $results['nbrTotalAdults'] + $results['paxTotalChildren'];
        // pax adults sans no show
        $results['nbrAdultsShow'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $this->getUser(), "adults", $noShow);
        $results['nbrAdultsShow'] = intval($results['nbrAdultsShow']);
        $results['nbrChildrenShow'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $this->getUser(), "children", $noShow);
        $results['nbrChildrenShow'] = intval($results['nbrChildrenShow']);
        $results['paxChildrenShow'] = $results['nbrChildrenShow'] * 0.5;
        $results['nbrBabiesShow'] = $customerCardRepository->numberOfPaxPerDateAndAge($dateStart, $dateEnd, $this->getUser(), "babies", $noShow);
        $results['nbrBabiesShow'] = intval($results['nbrBabiesShow']);
        $results['sumNbrShow'] = $results['nbrAdultsShow'] + $results['nbrChildrenShow'] + $results['nbrBabiesShow'];
        $results['sumPaxShow'] = $results['nbrAdultsShow'] + $results['paxChildrenShow'];

        return $this->render('customer_card/calcul_pax_par_rep.html.twig', [
            'results' =>$results,
            'paxMenu' => true
        ]);
    }

    #[Route('/transportation/management', name: 'app_customer_card_transportation_management', methods: ['GET', 'POST'])]
    public function transportationManagement(Request $request, 
                                             TransferVehicleArrivalRepository $transferVehicleArrivalRepository, 
                                             TransferInterHotelRepository $transferInterHotelRepository, 
                                             TransferDepartureRepository $transferDepartureRepository, 
                                             TransportCompanyRepository $transportCompanyRepository,
                                            ): Response
    {

        // TODO refaire les compagnies a partir du repos vehicleArrival

        $companies = $transportCompanyRepository->findBy([],['name' => 'ASC']);
      

        // si on recoit le formulaire
        // si on a cliqué sur envoyé
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
                // todo  : alors on peut récupérer les données et les filtrer
                $dateStart = $request->query->get('dateStart');
                $dateEnd = $request->query->get('dateEnd');
                $company = ($request->query->get('company') != 'all') 
                            ? $transportCompanyRepository->findOneBy(['name' => $request->query->get('company')]) 
                            : $request->query->get('company')
                            ;  
                $area = $request->query->get('area');
                $type = ($request->query->get('type') == 2)? false: $request->query->get('type');
            } 
        } else {
                $dateStart = new DateTime();
                $dateStart = $dateStart->format('Y-m-01');
                $dateEnd = new DateTime();
                $dateEnd = $dateEnd->format('Y-m-d');
                $company ="all";
                $area = "all";
                $type = "all";
        }

        $transferVehicleArrivals = $transferVehicleArrivalRepository->findVehicleArrivalsBydatesAndCompanies($dateStart, $dateEnd, $company, $area, $type);
        $transferInterHotels = $transferInterHotelRepository->findInterHotelsBydatesAndCompanies($dateStart, $dateEnd, $company, $area, $type);
        $transferDepartures = $transferDepartureRepository->findDeparturesBydatesAndCompanies($dateStart, $dateEnd, $company, $area, $type); 

        // récupération des zones pour chaque transfer et array unique 
        
        $transferArrivalsAreas = $transferVehicleArrivalRepository->findTransferVehicleArrivalAreas();
        $transferInterhotelsAreas = $transferInterHotelRepository->findTransferInterhotelAreas();
        $transferDeparturesAreas = $transferDepartureRepository->findTransferDepartureAreas();
        $mergeTransferAreas = array_merge($transferArrivalsAreas,$transferInterhotelsAreas,$transferDeparturesAreas);
        $uniqueAreas = [];
        foreach ($mergeTransferAreas as $area) {
            if (!in_array($area,$uniqueAreas)) { $uniqueAreas[] = $area; }
        }


        $allTransfers = [];
        $adultsNumber = 0;
        $childrenNumber = 0;
        $babiesNumber = 0;

        $iKey = 0;
        foreach ($transferVehicleArrivals as $transferVehicleArrival) {
            $allTransfers[$iKey]['object'] = $transferVehicleArrival;
            $allTransfers[$iKey]['instance'] = 'arrival';
            $allTransfers[$iKey]['date'] = $transferVehicleArrival->getDate()->format('Y-m-d');
            $allTransfers[$iKey]['hour'] = $transferVehicleArrival->getDate()->format('H:i');
            $adultsNumber +=  $transferVehicleArrival->getTransferArrival()->getAdultsNumber();
            $childrenNumber +=  $transferVehicleArrival->getTransferArrival()->getChildrenNumber();
            $babiesNumber +=  $transferVehicleArrival->getTransferArrival()->getBabiesNumber();
            $iKey++;
        }

        //dd($allTransfers);
        foreach ($transferInterHotels as $transferInterHotel) {
            $allTransfers[$iKey]['object'] = $transferInterHotel;
            $allTransfers[$iKey]['instance'] = 'interhotel';
            $allTransfers[$iKey]['date'] = $transferInterHotel->getDate()->format('Y-m-d');
            $allTransfers[$iKey]['hour'] = $transferInterHotel->getPickUp()->format(('H:i'));
            $adultsNumber += $transferInterHotel->getAdultsNumber();
            $childrenNumber += $transferInterHotel->getChildrenNumber();
            $babiesNumber += $transferInterHotel->getBabiesNumber();
            $iKey++;
        }
        foreach ($transferDepartures as $transferDeparture) {
            $allTransfers[$iKey]['object'] = $transferDeparture;
            $allTransfers[$iKey]['instance'] = 'departure';
            $allTransfers[$iKey]['date'] = $transferDeparture->getDate()->format('Y-m-d');
            $allTransfers[$iKey]['hour'] = $transferDeparture->getPickUp()->format('H:i');
            $adultsNumber += $transferDeparture->getAdultsNumber();
            $childrenNumber += $transferDeparture->getChildrenNumber();
            $babiesNumber += $transferDeparture->getBabiesNumber();
            $iKey++;
        } 

        return $this->render('customer_card/transportation_management.html.twig', [
            'transportCompanies' => $companies,
            'allTransfers' => $allTransfers,
            'transferVehicleArrivals' => $transferVehicleArrivals,
            'adultsNumber' => $adultsNumber,
            'childrenNumber' => $childrenNumber,
            'babiesNumber' => $babiesNumber,
            'uniqueAreas' => $uniqueAreas
        ]);
    }

    #[Route('team-manager/customer/card/new', name: 'app_customer_card_new', methods: ['GET', 'POST'])]
    public function new(Request $request, 
                        CustomerCardRepository $customerCardRepository,
                        AgencyRepository $agencyRepository,
                        TransferArrivalRepository $transferArrivalRepository,
                        MeetingPointRepository $meetingPointRepository,
                        StatusRepository $statusRepository
                        ): Response
    {



        $transferArrival = new TransferArrival();
        $form = $this->createForm(TransferArrivalNewType::class, $transferArrival);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $post = $_POST['transfer_arrival_new'];
   
            $transferArrival->setStatusUpdatedBy($this->getUser());
            $transferArrival->setStatusUpdatedAt(new DateTimeImmutable('now', new DateTimeZone('America/Santo_Domingo')));
            //récupère le premier élément de meeting
            $firstMeetingPoint = $meetingPointRepository->findOneBy([]);
            $firstStatus = $statusRepository->findOneBy([]);    
            $transferArrival->setMeetingPoint($firstMeetingPoint);
            $transferArrival->setStatus($firstStatus);


            // mettre a jour le meeting grace a l'arrivée;
            $meetingDate = new DateTimeImmutable($post['date']);
            $meetingDateFormat = new DateTimeImmutable($meetingDate->format('Y-m-d 00:01'));
            $meetingDateHour = $meetingDateFormat->modify('+1 day');
            $transferArrival->setMeetingAt( $meetingDateHour);  

 
            $customerCard = new CustomerCard();
            $customerCard->setHolder($post['fullName']);
            $customerCard->setReservationNumber($post['reservationNumber']);
            $customerCard->setJumboNumber($post['jumboNumber']);
            $agencyObject = $agencyRepository->find($post['agency']);
            $customerCard->setAgency($agencyObject);

            $transferArrival->setCustomerCard($customerCard);

            $customerCardRepository->save($customerCard, false);
            $transferArrivalRepository->save($transferArrival, true);
            return $this->redirectToRoute('app_customer_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer_card/new.html.twig', [
            'customer_card' => $transferArrival,
            'form' => $form,
        ]);
    }


    #[Route('customer/card/{id}', name: 'app_customer_card_show', methods: ['GET' , 'POST'])]
    public function show(CustomerCard $customerCard, Request $request, CommentRepository $commentRepository): Response
    {


        $user = $this->getUser();
        $comments = $commentRepository->findby(['customerCard' => $customerCard]);

        // enregistre les date

        $tableauTimeline = [];
        $i = 0;
        // date d'arrivée
        foreach ($customerCard->getTransferArrivals() as $arrival) {
            $tableauTimeline[$i]['name'] = 'Arrival';
            $tableauTimeline[$i]['date'] = $arrival->getDate();
            $tableauTimeline[$i]['hour'] = $arrival->getHour()->format('H:i');
            
            $tableauTimeline[$i]['name'] = 'Meeting';
            $tableauTimeline[$i]['date'] = $arrival->getMeetingAt();
            $tableauTimeline[$i]['hour'] = $arrival->getMeetingAt()->format('H:i');
            $tableauTimeline[$i]['staff'] = $arrival->getStaff();
            $tableauTimeline[$i]['meetingPoint'] = $arrival->getMeetingPoint();
            $i++;            
            
            
            
            
            $i++;
        }

        foreach ($customerCard->getTransferInterHotels() as $interHotel) {
            $tableauTimeline[$i]['name'] = 'Inter Hotel';
            $tableauTimeline[$i]['date'] = $interHotel->getDate();
            $tableauTimeline[$i]['hour'] = $interHotel->getPickUp()->format('H:i');
            $i++;
        }
        foreach ($customerCard->getTransferDeparture() as $departure) {
            $tableauTimeline[$i]['name'] = 'Departure';
            $tableauTimeline[$i]['date'] = $departure->getDate();
            $tableauTimeline[$i]['hour'] = $departure->getHour()->format('H:i');
            $i++;
        }
/*         if ($customerCard->getMeetingAt()) {
            $tableauTimeline[$i]['name'] = 'Meeting';
            $tableauTimeline[$i]['date'] = $customerCard->getMeetingAt();
            $tableauTimeline[$i]['hour'] = $customerCard->getMeetingAt()->format('H:i');
            $tableauTimeline[$i]['staff'] = $customerCard->getStaff();
            $tableauTimeline[$i]['meetingPoint'] = $customerCard->getMeetingPoint();
            $i++;
        } */



            if (count($customerCard->getStatusHistories())  > 0 ) {
                foreach ($customerCard->getStatusHistories() as $modifiedStatus) {
                    $tableauTimeline[$i]['name'] = 'Status';
                    $tableauTimeline[$i]['title'] = $modifiedStatus->getStatus();
                    $tableauTimeline[$i]['date'] = $modifiedStatus->getCreatedAt();
                    $tableauTimeline[$i]['hour'] = $modifiedStatus->getCreatedAt()->format('H:i');
                    $tableauTimeline[$i]['updatedBy'] = $modifiedStatus->getUpdatedBy();
                    $i++;
                }

            }         

        /*  dd($tableauTimeline); */
        // date des inter hotels
        // date de départ 
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            


            $file = $form['media']->getData();
            $comment = new Comment;

            if ( ($file == null ) and ($form['content']->getData() == null) and ($form['predefinedCommentsMessages']->getData() == null) ) {
                return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard->getId()], Response::HTTP_SEE_OTHER);
            }

            if ($file !== null) {
                $someNewFilename = $user->getUsername() . '_report_' . date("dmYgi");
                $directory = 'images/comments_medias/';
                $extension = $file->guessExtension();
                $file->move($directory, $someNewFilename.'.'.$extension);
                $comment->setMedia($someNewFilename.'.'.$extension);
            } 
            if ($form['content']->getData() !== null) {
                $comment->setContent($form['content']->getData()); 
            }
            if ($form['predefinedCommentsMessages']->getData() !== null) {
                $comment->setpredefinedCommentsMessages($form['predefinedCommentsMessages']->getData());
            }

            $comment->setCreatedBy($user);
            $comment->setCustomerCard($customerCard);


            $commentRepository->save($comment, true);
            return $this->redirectToRoute('app_customer_card_show', [
                'id' => $customerCard->getId(),
                'clientsMenu' => true
            ], 
            Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer_card/show.html.twig', [
            'customer_card' => $customerCard,
            'comments' => $comments,
            'form' => $form,
            'tableauTimeline' => $tableauTimeline,
            'clientsMenu' => true
        ]);
    }

    #[Route('customer/card/change/{id}', name: 'app_customer_card_change_status', methods: ['POST'])]
    public function changeStatus(TransferArrival $transferArrival, 
                                 StatusRepository $statusRepository, 
                                 EntityManagerInterface $entityManager): Response
    {

        $statusName = $_POST['status'];
        
        //je fais mes traitements
        $newStatus = $statusRepository->findOneBy(['name' => strtolower($statusName)]);
        $transferArrival->setStatus($newStatus);

        // On met à jour le statusHistory
        $newStatusHistory = new StatusHistory();
        $currentUser = $this->getUser();
        $newStatusHistory->setStatus($newStatus);
        $newStatusHistory->setCustomerCard( $transferArrival->getCustomerCard());
        $newStatusHistory->setUpdatedBy($currentUser);

        $entityManager->persist($newStatusHistory);
        $entityManager->flush();

        return $this->redirectToRoute("app_customer_card_show", ['id' => $transferArrival->getCustomerCard()->getId()]);

    }



    #[Route('customer/card/{id}/edit', name: 'app_customer_card_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CustomerCard $customerCard, CustomerCardRepository $customerCardRepository, StatusHistoryRepository $statusHistoryRepository): Response
    {

        //$queryString = $request->query;
        $customerPresence = $request->query->get('customerPresence');
        $dateStart = $request->query->get('dateStart');
        $dateEnd = $request->query->get('dateEnd');
        $natureTransfer = $request->query->get('natureTransfer');
        $reps = $request->query->get('reps');
        $status = $request->query->get('status');
        $agency = $request->query->get('agency');
        $hotel = $request->query->get('hotel');
        $flightNumber = $request->query->get('flightNumber');
        $search = $request->query->get('search');
       // dd($queryString);

        // si l'utilisateur n'a pas les droits
        $user = $this->getUser();
        if ( (!in_array('ROLE_HULK', $user->getRoles())) and (!in_array('ROLE_SUPERMAN', $user->getRoles())) ) {
            return throw $this->createAccessDeniedException();
        }


        $form = $this->createForm(CustomerCardType::class, $customerCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $customerCardRepository->save($customerCard, true);

            return $this->redirectToRoute('app_customer_card_show', [
                'id' => $customerCard->getId(),
                'customerPresence'=> $customerPresence,
                'dateStart'=> $dateStart,
                'dateEnd'=> $dateEnd,
                'natureTransfer'=> $natureTransfer,
                'reps'=> $reps,
                'status'=> $status,
                'agency'=> $agency,
                'hotel'=> $hotel,
                'flightNumber'=> $flightNumber,
                'search'=> $search,         
                ]
                , Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer_card/edit.html.twig', [
            'customer_card' => $customerCard,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_card_delete', methods: ['POST'])]
    public function delete(Request $request, 
                            CustomerCard $customerCard, CustomerCardRepository $customerCardRepository, 
                            TransferArrivalRepository $transferArrivalRepository,
                            TranslatorInterface $translator): Response
    {


        
        if ($customerCard->getTransferInterHotels()->count() > 0) {
            // vous ne pouvez pas supprimer car ce client a des interHotels. Supprimer les départs avant 
            $this->addFlash(
                'warning',
                'You can\'t delete this client card because there is an interHotel associated. Please remove the inter hotel first'
            );
            return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard->getId()], Response::HTTP_SEE_OTHER);
        }
        if ($customerCard->getTransferDeparture()->count() > 0) {
            // vous ne pouvez pas supprimer car ce client a des departs. Supprimer les départs avant 
            $this->addFlash(
                'warning',
                'You can\'t delete this client card because there is a departure associated. Please remove the departure first'
            );
            return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($customerCard->getTransferArrivals()->count() > 0) {
            // récupérer les arrivées et les supprimer 1 a 1
            foreach ($customerCard->getTransferArrivals() as $transfer) {
               $currentTransferArrival = $transferArrivalRepository->find($transfer);
               $transferArrivalRepository->remove($currentTransferArrival, true);
            } 
        }

        if ($this->isCsrfTokenValid('delete'.$customerCard->getId(), $request->request->get('_token'))) {
            $customerCardRepository->remove($customerCard, true);
        }

        return $this->redirectToRoute('app_customer_card_index', [], Response::HTTP_SEE_OTHER);
    }

}
