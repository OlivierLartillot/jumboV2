<?php

namespace App\Controller;

use App\Entity\Agency;
use App\Entity\AirportHotel;
use App\Entity\CustomerCard;
use App\Entity\DragAndDrop;
use App\Entity\TransferArrival;
use App\Entity\TransferDeparture;
use App\Entity\TransferInterHotel;
use App\Form\DragAndDropType;
use App\Repository\AgencyRepository;
use App\Repository\AirportHotelRepository;
use App\Repository\CustomerCardRepository;
use App\Repository\MeetingPointRepository;
use App\Repository\StatusRepository;
use App\Repository\TransferArrivalRepository;
use App\Repository\TransferDepartureRepository;
use App\Repository\TransferInterHotelRepository;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use League\Csv\Reader;

class HomeController extends AbstractController
{

    #[Route('admin/', name: 'home' )]
    public function test(UserRepository $userRepository)
    {
        return $this->render('index.html.twig', [
        ]);

    }


    #[Route('/import', name: 'app_import', methods: ['GET', 'POST'] )]
    public function import(Request $request)
    {

        $dragAndDrop = new DragAndDrop();
        $form = $this->createForm(DragAndDropType::class, $dragAndDrop, [
                'action' => $this->generateUrl('admin_traitement_csv'),
        ] );
       $form->handleRequest($request);




        return $this->render('team_manager/import.html.twig', [
            'form' => $form
        ]);

    }

    #[Route('/traitement_csv', name: 'admin_traitement_csv')]
    public function traitement_csv(Request $request, EntityManagerInterface $manager, 
                                    StatusRepository $statusRepository, 
                                    MeetingPointRepository $meetingPointRepository, 
                                    UserRepository $userRepository,
                                    CustomerCardRepository $customerCardRepository,
                                    AirportHotelRepository $airportHotelRepository,
                                    AgencyRepository $agencyRepository,
                                    TransferArrivalRepository $transferArrivalRepository,
                                    TransferInterHotelRepository $transferInterHotelRepository,
                                    TransferDepartureRepository $transferDepartureRepository,
                                    ): Response
    {

        $fileToUpload = $request->files->get('drag_and_drop')["fileToUpload"];
        $mimeType = $fileToUpload->getMimeType();
        $error = $fileToUpload->getError();

       
        // récupération du token
        $submittedToken = $request->request->get('token');
                                        
        // 'delete-item' is the same value used in the template to generate the token
        if (!$this->isCsrfTokenValid('upload-item', $submittedToken)) {
            // ... do something, like deleting an object
           // redirige vers page erreur token
           //dd('stop erreur token');
        }

        
        // test des données recues
        
        // infos sur le csv
        if ( $error > 0) {
            die("Erreur lors de l'upload du fichier. Code d'erreur : " . $error);
        }
        
        // Vérifier si le fichier a été correctement téléchargé
        if (!file_exists($fileToUpload)) {
            die("Fichier non trouvé.");
        }
        
        // Vérifier le type de fichier
        if ($mimeType != "text/csv") {
            die("l extension du fichier n est pas bonne !");
        }
        
        
    
            // a faire dans le traitement
            //load the CSV document from a stream
            /*  $stream = fopen('csv/servicios.csv', 'r'); */
            $csv = Reader::createFromStream(fopen($fileToUpload, 'r+'));
            //$csv = Reader::createFromPath($_FILES["fileToUpload"]["tmp_name"], 'r');
            $csv->setDelimiter(',');
            $csv->setHeaderOffset(0);
            
            
            // les entités par défaut
            $status = $statusRepository->find(1);
            $user = $userRepository->find(1);
            $meetingPoint = $meetingPointRepository->find(1);
        

            // début de l'extraction des données du csv
            foreach ($csv as $record) {
    
                $record['Número Servicio'] = trim($record['Número Servicio']);
                $record['Traslado desde'] = trim(strtolower($record['Traslado desde']));
                $record['Traslado hasta'] = trim(strtolower($record['Traslado hasta']));
                $record['Tipo traslado'] = trim(strtolower($record['Tipo traslado']));
                // on met privée au debut car si c est pas privé, c a peut etre shuttle ou colectivo
                $record['Tipo traslado'] = ((preg_match("/pri/i", $record['Tipo traslado']) ? false : true));
                $record['Nº Vuelo/Transporte Origen'] = trim($record['Nº Vuelo/Transporte Origen']);
                $record['Nº Vuelo/Transporte Destino'] = trim($record['Nº Vuelo/Transporte Destino']);
                $record['Fecha/Hora recogida'] = trim($record['Fecha/Hora recogida']);
                $record['Fecha/Hora Origen'] = trim($record['Fecha/Hora Origen']);
                $record['Titular'] = trim(strtolower($record['Titular'])); 
                $record['Agencia'] = trim(strtolower($record['Agencia']));



                // les entrées possédant ce numéro doivent être ignorée
                if (($record['Nº Vuelo/Transporte Origen'] == "XX9999") or 
                ($record['Nº Vuelo/Transporte Destino'] == "XX9999") or 
                ($record['Fecha/Hora recogida'] == "XX9999")){
                    continue;
                }
                //dd($csv);

                //! extraction de jumboNumber et reservationNumber car ils se trouvent dans la meme case dans le csv 
                $numbers = explode(", ", $record['Localizadores']);
                $jumboNumber = trim($numbers[0]);
                $reservationNumber = trim($numbers[1]);
                
                //! extraction du nombre d'adultes/enfants/bébés car dans la même case dans le csv
                $numeroPasajeros = explode(" ", $record['Número pasajeros']);
                $adultsNumber = trim($numeroPasajeros[1]);
                $childrenNumber = trim($numeroPasajeros[3]);
                $babiesNumber = trim($numeroPasajeros[5]);

                // on essaie de récupérer la fiche pour savoir si on va create or update
                $customerCardResult = $customerCardRepository->findOneBy(['reservationNumber' => $reservationNumber]);
                // si l'enregistrement existe déja, on va le mettre a jour
                if ($customerCardResult) {
                    $customerCard = $customerCardResult;
                    $agency = $agencyRepository->findOneBy(['name' => $record['Agencia']]);
                    // faut il mettre a jour la date du meeting en cas de changement de la date d arrivée
                    // on peut comparer si la date est la meme que celle de l'objet
                    // on vérifie que la date n'ai pas changée
                    //définir si c est une arrivée car les meetings se font a l arrivée
                    if ($record['Nº Vuelo/Transporte Origen'] != NULL) {
                        $fechaHora = $record['Fecha/Hora Origen'];
                        

                        foreach ($customerCard->getTransferArrivals() as $transfer) {
                            // si le transfer est arrivée ! il faut regarder si les dates correpondent (fichier et bdd)
                            // si la date est différente on met a jour le briefing
                            $dateTransfer = $transfer->getDate()->format('m/d/Y');
                            $heureTransfer = $transfer->getHour()->format('H:i');
                            $dateTime = new DateTime($dateTransfer . '' . $heureTransfer);
                          
                            /* if ( ($fechaHora != $transfer->getDateHour()->format('m/d/Y'. ' H:i')) ){  */
                            if ( ($fechaHora != $dateTime) ){ 
                                // mettre ajour le briefing
                                if ($record['Fecha/Hora Origen']) {
                                    $dateTime = explode(" ", $record['Fecha/Hora Origen']);
                                    $date = new DateTime($dateTime[0]);
                                    $date = $date->modify('+1 month');
                                    $hour = '00:01';
                                    $meetingAt = new DateTimeImmutable($date->format('Y-d-m') . $hour);
                                    $customerCard->setMeetingAt($meetingAt);
                                }
                            }
                        }
                    }
                } 
                else // sinon on va créer un nouvel objet
                {
                    $customerCard = new CustomerCard();
                    $customerCard->setMeetingPoint($meetingPoint);

                    
                    // regarde si l'agence exsite deja pour l'enregistrer?
                    $agency = $agencyRepository->findOneBy(['name' => $record['Agencia']]);
                    // si elle est vide il faut la créeer 
                    if (empty($agency)) {
                        $agency = new Agency();
                        $agency->setName($record['Agencia']);
                        $manager->persist($agency);
                        $manager->flush($agency);
                    } 

                    if ($record['Fecha/Hora Origen']) {
                        $dateTime = explode(" ", $record['Fecha/Hora Origen']);
                        $date = new DateTime($dateTime[0]);
                        $date = $date->modify('+1 month');
                        $hour = '00:01';
                        $meetingAt = new DateTimeImmutable($date->format('Y-d-m') . $hour);
                        $customerCard->setMeetingAt($meetingAt);
                    }
                }

                // enregistrement des données dans la card courante
                    $customerCard->setReservationNumber($reservationNumber);
                    $customerCard->setJumboNumber($jumboNumber);
                    $customerCard->setHolder($record['Titular']);
                    $customerCard->setAgency($agency);
                    $customerCard->setAdultsNumber($adultsNumber);
                    $customerCard->setChildrenNumber($childrenNumber);
                    $customerCard->setBabiesNumber($babiesNumber);
                    $customerCard->setStatus($status);
                    $customerCard->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                    $customerCard->setStatusUpdatedBy($user);
                    // meetind At, le lendemain de l'arrivée
                   
                    $customerCard->setReservationCancelled(0);
                    
                    // si la carte n existe pas deja on fait le persist sinon doctrine s'en occupe
                    if (!$customerCardResult) {
                        $manager->persist($customerCard);
                    }
                    

                //! traitement des infos de la table transfer

                // on peut checker les hotels pour voir si l'hotel existe ou pas afin de le rajouter dans la table hotel
                        //nature transfer 
                            // si Arrivée == to arrival -> $record['Traslado hasta']
                            // si inter hotel == to arrival -> $record['Traslado hasta']
                            // si depart == from_start -> $record['Traslado desde']
                $airportHotel = new AirportHotel();
                $desde = $airportHotelRepository->findOneBy(['name' => $record['Traslado desde']]);
                $hasta = $airportHotelRepository->findOneBy(['name' => $record['Traslado hasta']]);
                //définir si c est une arrivée/depart/interHotel

                // si c est une arrivée
                if ($record['Nº Vuelo/Transporte Origen'] != NULL) {
                    //$fechaHora = $record['Fecha/Hora Origen'];
                    $fechaHora = explode(" ", $record['Fecha/Hora Origen']);
                    $natureTransfer = 1;
                    $flightNumber = $record['Nº Vuelo/Transporte Origen'];
                    $dateTime = explode(" ", $record['Fecha/Hora Origen']);
                    // check si cet hotel existe
                    if (empty($desde) ){
                        // ajouter l'airport dans la table
                        $airportHotel->setName($record['Traslado desde']);
                        $airportHotel->setIsAirport(1);
                        $manager->persist($airportHotel);
                        $manager->flush($airportHotel);
                        
                        $desde = $airportHotel;
                     };
                    if (empty($hasta) ){
                        // ajouter l'hotel dans la table
                        $airportHotel->setName($record['Traslado hasta']);
                        $airportHotel->setIsAirport(0);
                        $manager->persist($airportHotel);
                        $manager->flush($airportHotel);
                        // hasta = $airport hotel courant
                        $hasta = $airportHotel;
                    };
                // Si c est un départ
                } 
                else if ($record['Nº Vuelo/Transporte Destino'] != NULL) {
                    //$fechaHora = $record['Fecha/Hora Destino'];
                    $fechaHora = explode(" ", $record['Fecha/Hora Destino']);
                    $natureTransfer = 3;
                    $flightNumber = $record['Nº Vuelo/Transporte Destino'];
                    $dateTime = explode(" ", $record['Fecha/Hora Destino']);
                    // check si cet hotel existe
                    if (empty($desde) ){
                        // ajouter l'hotel dans la table
                        $airportHotel->setName($record['Traslado desde']);
                        $airportHotel->setIsAirport(0);
                        $manager->persist($airportHotel);
                        $manager->flush($airportHotel);
                        $desde = $airportHotel;
                    };
                    if (empty($hasta) ){
                        // ajouter l'airport dans la table
                        $airportHotel->setName($record['Traslado hasta']);
                        $airportHotel->setIsAirport(1);
                        $manager->persist($airportHotel);
                        $manager->flush($airportHotel);
                        $hasta = $airportHotel;
                     };
                // si c est un interHotel
                } else {
                    //$fechaHora = $record['Fecha/Hora recogida'];
                    $fechaHora = explode(" ", $record['Fecha/Hora recogida']);
                    $natureTransfer = 2;
                    $flightNumber = NULL;
                    $dateTime = explode(" ", $record['Fecha/Hora recogida']);
                    // check si cet hotel existe
                    if (empty($desde)){
                        // ajouter l'hotel dans la table
                        $airportHotel->setName($record['Traslado desde']);
                        $airportHotel->setIsAirport(0);
                        $manager->persist($airportHotel);
                        $manager->flush($airportHotel);
                        $desde = $airportHotel;
                    } ;
                    if (empty($hasta)){
                        // ajouter l'hotel dans la table
                        $airportHotel->setName($record['Traslado hasta']);
                        $airportHotel->setIsAirport(0);
                        $manager->persist($airportHotel);
                        $manager->flush($airportHotel);
                        $hasta = $airportHotel;
                    } ;
                }
               

                $date = new DateTime($dateTime[0]);
                $dateTime = $date->format('Y-d-m ' .$dateTime[1]);
                $fechaHora = new DateTimeImmutable($dateTime);;        
                
                // on essaie de récupérer la fiche pour savoir si on va create or update
                //$transferResult = $transferRepository->findOneBy(['customerCard' => $customerCard]);
                if ($natureTransfer == 1) {
                    $transferResult = $transferArrivalRepository->findOneBy(['customerCard' => $customerCard]);
                } elseif ($natureTransfer == 2) {
                    $transferResult = $transferInterHotelRepository->findOneBy(['customerCard' => $customerCard]);
                } else {
                    $transferResult = $transferDepartureRepository->findOneBy(['customerCard' => $customerCard]);
                    //dd($transferResult);
                }
                // $transfer = $this->transferRepository
                // si l'enregistrement existe déja, on va le mettre a jour
                if ($transferResult) {
                    $transfer = $transferResult;
                } else {
                    if ($natureTransfer == 1) {
                        $transfer = new TransferArrival();
                    } elseif ($natureTransfer == 2) {
                        $transfer= new TransferInterHotel();
                    } else {
                        $transfer = new TransferDeparture();
                    }
                }
                 // sinon on va créer un nouvel objet
                 $transfer->setServiceNumber($record['Número Servicio']);
                  $transfer->setDateHour($fechaHora); 
                 $transfer->setDate($fechaHora);
                 $transfer->setHour($fechaHora);
                 $transfer->setFlightNumber($flightNumber);
                 
                 /*                dd($record['Traslado desde']);
                 dd($hotelRepository->findBy(['name' => $record['Traslado desde']])); */
                 $transfer->setFromStart($desde);
                 $transfer->setToArrival($hasta);
                 $transfer->setIsCollective($record['Tipo traslado']);

                 if ($transferResult == NULL) {
                     $transfer->setCustomerCard($customerCard);
                    $manager->persist($transfer);
                }
                
            }
            
                $manager->flush();
                
                    // TODO : regarder si un enregistrement a été supprimé
                    $csvArrivees = [];
                    $csvInterHotels = [];
                    $csvDeparts = [];

                    //si ce jour existe dans la bdd
                    // compare tous les reservatioNumber de ce jour si il y est dans la bdd mais pas dans le fichier, il faut le suupprimer des tansfers.
                    
                    //définir si c est une arrivée/depart/interHotel
                // rempli le tableau de resrvation_number pour ce jour et arrivée (csv reservation number)
                if ($record['Nº Vuelo/Transporte Origen'] != NULL) {
                    $csvArrivees[] =  $reservationNumber;
                    // fait un tableau de resrevation number pour ce jour et Interhotel (csv reservation number)
                } else if ($record['Nº Vuelo/Transporte Destino'] != NULL) {
                    $csvInterHotels[] =  $reservationNumber;
                   
                } else {
                    // fait un tableau de resrevation number pour ce jour et Départ (csv reservation number)
                    $csvDeparts[] = $reservationNumber;
                }

                dump($csvArrivees);
                dump($csvInterHotels);
                dump($csvDeparts);
                 // recupere tous les enregistrements avec doctrine, pour ce ( jour et arrivée ) avec la clé reservationNumber
                 //! ajouter un champ de bdd dans la table transfer (probably_modified)
                 // TODO : etre sur qu il n y a que la meme date dans le fichier !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                //compare, s'il en manque un , le marquer comme surrement modifié dans la table transfer !!! 

            return $this->redirectToRoute('home');

    }





}
