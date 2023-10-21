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

    #[Route('/admin', name: 'home' )]
    public function accueil()
    {
        return $this->render('index.html.twig', [
        ]);
    }

    #[Route('/team-manager/import', name: 'app_import', methods: ['GET', 'POST'] )]
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

    #[Route('team-manager/traitement_csv', name: 'admin_traitement_csv')]
    public function traitement_csv(Request $request, EntityManagerInterface $manager, 
                                    StatusRepository $statusRepository, 
                                    MeetingPointRepository $meetingPointRepository, 
                                    UserRepository $userRepository,
                                    CustomerCardRepository $customerCardRepository,
                                    AirportHotelRepository $airportHotelRepository,
                                    AgencyRepository $agencyRepository,
                                    TransferArrivalRepository $transferArrivalRepository,
                                    ): Response
    {

        $fileToUpload = $request->files->get('drag_and_drop')["fileToUpload"];
        $mimeType = $fileToUpload->getMimeType();
        $error = $fileToUpload->getError();
       
        // récupération du token
        $submittedToken = $request->request->get('token');
                       
        $errorDetails = [];
        // 'delete-item' is the same value used in the template to generate the token
        if (!$this->isCsrfTokenValid('upload-item', $submittedToken)) {
            //TODO: ... do something, like deleting an object
           // ! redirige vers page erreur token
           //dd('stop erreur token');
           $errorDetails[] = 'Erreur de token, veuillez rafraichir la page et recommencer';
        }

        // test des données recues
        // infos sur le csv
        if ( $error > 0) {
            $errorDetails[] = 'Erreur lors de l`\'upload du fichier. Code d\'erreur : "' . $error;
        }
    
        // Vérifier si le fichier a été correctement téléchargé
        if (!file_exists($fileToUpload)) {
            $errorDetails[] = "Fichier non trouvé.";
        }
        
        // Vérifier le type de fichier
        if (($mimeType != "text/csv") and ($mimeType != "text/plain")) {
            $errorDetails[] = "l extension du fichier n est pas bonne !";
        }
        
        if (count($errorDetails) > 0) {
            
            return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorDetails]);

        }
            // a faire dans le traitement
            //load the CSV document from a stream
            /*  $stream = fopen('csv/servicios.csv', 'r'); */
            $csv = Reader::createFromStream(fopen($fileToUpload, 'r+'));
            //$csv = Reader::createFromPath($_FILES["fileToUpload"]["tmp_name"], 'r');
            $csv->setDelimiter('|');
            $csv->setHeaderOffset(0);
            
            // les entités par défaut
            $status = $statusRepository->find(1);
            $user = $userRepository->find(1);
            $meetingsPoints = $meetingPointRepository->findAll();
            // Le meeting point va etre le premier de la liste !
            $meetingPoint = $meetingsPoints[0];
        

            // constituer le tableau des imports, enregistre les numéros de services, cela va servir
            // 1. a voir si plusieurs groupes sont présents sur la même fiche dans ce fichier: utile surtout si on renvoit le fichier et que la carte existe deja mais qu'un groupe se rajoute
            // 2. a voir à la fin si une entrée a été supprimée 

            $serviceNumbersInCSV = [];


            foreach ($csv as $record) {
                $numbers = explode(", ", $record['Localizadores']);
                $jumboNumber = trim($numbers[0]);
                $reservationNumber = trim($numbers[1]);
                $serviceNumbersInCSV[] =  $reservationNumber;
                // récupère le jour et l heure de l'arrivée 
                if ($record['Nº Vuelo/Transporte Origen'] != NULL) {
                    $record['Fecha/Hora Origen'] = trim($record['Fecha/Hora Origen']);
                    $dateTime = explode(" ", $record['Fecha/Hora Origen']);
                    $date = explode("/", $dateTime[0]);
                    $dateFormat = $date[2] . '-' . $date[1] .'-'. $date[0];
                    $dateTime = new DateTimeImmutable($dateFormat . ' ' .$dateTime[1]);
                }                
            }
            //$serviceNumbersInCSV[] = 1611603;
            // return [1611603 => 1 , 1611604 => 2 ]
            $serviceNumbersInCSV = array_count_values ($serviceNumbersInCSV);

            // 
            $incrementDeleteCustomerCard = 0;

            
            // début de l'extraction de la LIGNE de données du csv
            // traitement de la première entrée ...
            foreach ($csv as $record) {
    
                $record['Número Servicio'] = trim($record['Número Servicio']);
                $record['Traslado desde'] = trim(strtolower($record['Traslado desde']));
                $record['Traslado hasta'] = trim(strtolower($record['Traslado hasta']));
                $record['Tipo traslado'] = trim(strtolower($record['Tipo traslado']));
                // on met privée au debut car si c est pas privé, c a peut etre shuttle ou colectivo
                $record['Tipo traslado'] = ((preg_match("/pri/i", $record['Tipo traslado']) ? false : true));
                $record['Nº Vuelo/Transporte Origen'] = trim($record['Nº Vuelo/Transporte Origen']);
                $record['Fecha/Hora Origen'] = trim($record['Fecha/Hora Origen']);
                $record['Titular'] = trim(strtolower($record['Titular'])); 
                $record['Agencia'] = trim(strtolower($record['Agencia']));
                $record['Estado'] = trim(strtolower($record['Estado']));


                //*************************** ENTREES IGNOREES ***************************//
                //************************************************************************//
                // si l'entréee possède ce numéro elle doit être ignorée
                if (($record['Nº Vuelo/Transporte Origen'] == "XX9999")) {
                    continue;
                }
                // si c est un interHotel ou une arrivée, tu l'ignores
                if ($record['Nº Vuelo/Transporte Origen'] == NULL) {
                    continue;
                }
                // si l'entréee est annulée elle doit être ignorée
                if (( $record['Estado']  == "cancelado") or ($record['Estado']  == "cancelled")) { 
                    continue;
                }
                //************************************************************************//
                //************************* FIN ENTREES IGNOREES *************************//
                
                
                //*************************** DEBUT EXTRACTION**** ***********************//
                //************************************************************************//
                //! extraction de jumboNumber et reservationNumber car ils se trouvent dans la meme case dans le csv 
                $numbers = explode(", ", $record['Localizadores']);
                $jumboNumber = trim($numbers[0]);
                $reservationNumber = trim($numbers[1]);
                
                //! extraction du nombre d'adultes/enfants/bébés car dans la même case dans le csv
                $numeroPasajeros = explode(" ", $record['Número pasajeros']);
                $adultsNumber = trim($numeroPasajeros[1]);
                $childrenNumber = trim($numeroPasajeros[3]);
                $babiesNumber = trim($numeroPasajeros[5]);

                //************************************************************************//
                // CSV    
                    // combien de fois ce n° client est présent dans le csv
                    $nbReservationNumberInCSV = $serviceNumbersInCSV[$reservationNumber];
                    
                // CSV + BDD
                    // est ce une arrivée inter hotel ou départ dan le csv ?
                    // + recupere le nombre de fois présent en bdd arrivée
                    $clientNumberArrivalList = [];
                    if ($record['Nº Vuelo/Transporte Origen'] != NULL) {
                        //$natureTransferCSV = 'arrival'; 
                        $clientNumberArrivalList = $transferArrivalRepository->findByDateNaturetransferClientnumber($reservationNumber,$dateFormat);               

                        // combien de fois en bdd ( arrival/interH/Depart)
                        $countCLientArrivalBddThisDay = count($clientNumberArrivalList);

                        // MAJ Agence si l agence n existe pas on la met a jour
                        $agency = $agencyRepository->findOneBy(['name' => $record['Agencia']]);
                        if (empty($agency)) {
                            $agency = new Agency();
                            $agency->setName($record['Agencia']);
                            $agency->setIsActive(1);
                            $manager->persist($agency);
                            $manager->flush();
                    } 

                    // si l'hotel ou l aéroport n existe pas, on le MAJ
                    // pour savoir si c est un aéroport ou si c est un hotel ca depend de la nature du transfer
                        $airportHotel = new AirportHotel();
                        $desde = $airportHotelRepository->findOneBy(['name' => $record['Traslado desde']]);
                        $hasta = $airportHotelRepository->findOneBy(['name' => $record['Traslado hasta']]);

                        if ($record['Nº Vuelo/Transporte Origen'] != NULL) {
                            // mise en forme DATES
                                $dateTime = explode(" ", $record['Fecha/Hora Origen']);
                                $date = explode("/", $dateTime[0]);
                                $dateFormat = $date[2] . '-' . $date[1] .'-'. $date[0];
                                $dateObject = new DateTime($dateFormat);
                                $dateTime = new DateTimeImmutable($dateFormat . ' ' .$dateTime[1]);
                                
                                // enregistrement du meeting
                                $date = $dateObject->modify('+1 day'); 
                                $hour = '00:01';
                                $meetingAt = new DateTimeImmutable($date->format('Y-m-d'. ' ' . $hour));
                            
                            // autres paramètres     
                            $natureTransfer = "arrival";
                            $transfer= new TransferArrival();
                            $flightNumber = $record['Nº Vuelo/Transporte Origen'];
                            
                            // check si cet hotel existe
                            if (empty($desde) ){
                                // ajouter l'airport dans la table
                                $airportHotel->setName($record['Traslado desde']);
                                $airportHotel->setIsAirport(1);
                                $manager->persist($airportHotel);
                                $manager->flush();
                                
                                $desde = $airportHotel;
                            };
                            if (empty($hasta) ){
                                // ajouter l'hotel dans la table
                                $airportHotel->setName($record['Traslado hasta']);
                                $airportHotel->setIsAirport(0);
                                $manager->persist($airportHotel);
                                $manager->flush();
                                // hasta = $airport hotel courant
                                $hasta = $airportHotel;
                            };
                        
                        }
                    
                // ARRIVEE EN CSV = si bdd et csv == 1 c est une mise à jour simple 
                if (($nbReservationNumberInCSV == $countCLientArrivalBddThisDay) AND ($nbReservationNumberInCSV == 1)) {
 
                    $transferArrival = $transferArrivalRepository->findByDateNaturetransferClientnumber($reservationNumber, $dateFormat);
                    $customerCard = $transferArrival[0]->getCustomerCard();
                        
                    // mettre a jour le customerCard    
                
                        // MAJ du meeting ?
                        foreach ($customerCard->getTransferArrivals() as $transfer) {
                            // si le transfer est arrivée ! il faut regarder si les dates correpondent (fichier et bdd)
                            // si la date est différente on met a jour le briefing
                            $dateTransfer = $transfer->getDate()->format('Y/m/d');
                            $heureTransfer = $transfer->getHour()->format('H:i');
                            $dateTimeBDD = new DateTime($dateTransfer . '' . $heureTransfer);
                            
                       
                                /* if ( ($fechaHora != $transfer->getDateHour()->format('m/d/Y'. ' H:i')) ){  */
                                if ( ($dateTime != $dateTimeBDD) ){ 
                                    // mettre ajour le briefing
                                    if ($record['Fecha/Hora Origen']) {

                                        // enregistrement du meeting

                                        $customerCard->setMeetingAt($meetingAt);
                                    }
                        
                                }
                
                        }
                    // enregistrement des données dans la card courante

                    $customerCard->setReservationNumber($reservationNumber);
                    $customerCard->setJumboNumber($jumboNumber);
                    $customerCard->setHolder($record['Titular']);
                    $customerCard->setAgency($agency);
                    $customerCard->setMeetingPoint($meetingPoint);
                    $customerCard->setAdultsNumber($adultsNumber);
                    $customerCard->setChildrenNumber($childrenNumber);
                    $customerCard->setBabiesNumber($babiesNumber);
                    $customerCard->setStatus($status);
                    $customerCard->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                    $customerCard->setStatusUpdatedBy($user);
                    // meetind At, le lendemain de l'arrivée
                
                    $customerCard->setReservationCancelled(1);
                    // mettre a jour l'arrival

                    // on essaie de récupérer la fiche pour savoir si on va create or update
                    //$transferResult = $transferRepository->findOneBy(['customerCard' => $customerCard]);                       
                    $transferResult = $transferArrivalRepository->findOneBy(['customerCard' => $customerCard]);

                    // si l'enregistrement existe déja, on va le mettre a jour
                    if ($transferResult) {
                        $transfer = $transferResult;
                    } else {
                        $transfer = new TransferArrival();
                    }

                    // sinon on va créer un nouvel objet
                    $transfer->setServiceNumber($record['Número Servicio']);
                    $transfer->setDateHour($dateTime); 
                    $transfer->setDate($dateTime);
                    $transfer->setHour($dateTime);
                    $transfer->setFlightNumber($flightNumber);
                    $transfer->setFromStart($desde);
                    $transfer->setToArrival($hasta);
                    $transfer->setIsCollective($record['Tipo traslado']);


                    if ($transferResult == NULL) {
                        $transfer->setCustomerCard($customerCard);
                            $manager->persist($transfer);
                    }

                }
                // Doublons: si le client dans la bdd existe ce jour (> 0) 
                else if (($countCLientArrivalBddThisDay > 1))  {
 
                    // on va tout supprimer (arrivée) 
                    if ($countCLientArrivalBddThisDay > 1) {

                        $transferArrival = $transferArrivalRepository->findByDateNaturetransferClientnumber($reservationNumber, $dateFormat);
                        $customerCard = $transferArrival[0]->getCustomerCard();
                        // on supprime les arrivées supplémentaires déja présentent ce jour
                            $transferArrivals =  $transferArrivalRepository->findBy(['customerCard' => $customerCard, 'date'=> new DateTimeImmutable($dateFormat)]);
                            foreach ($transferArrivals as $transferArrival) {
                                $transferArrivalRepository->remove($transferArrival, true);
                            }
                                $customerCard->setMeetingAt($meetingAt);

                                // enregistrement des données dans la card courante
                                $customerCard->setReservationNumber($reservationNumber);
                                $customerCard->setJumboNumber($jumboNumber);
                                $customerCard->setHolder($record['Titular']);
                                $customerCard->setAgency($agency);
                                $customerCard->setMeetingPoint($meetingPoint);
                                $customerCard->setAdultsNumber($adultsNumber);
                                $customerCard->setChildrenNumber($childrenNumber);
                                $customerCard->setBabiesNumber($babiesNumber);
                                $customerCard->setStatus($status);
                                $customerCard->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                                $customerCard->setStatusUpdatedBy($user);
                                // meetind At, le lendemain de l'arrivée
                            
                                $customerCard->setReservationCancelled(0);

                                $manager->persist($customerCard);

                            // Création de l'arrival
                                $transfer = new TransferArrival();
                        
                            
                    } 
                    $transfer->setServiceNumber($record['Número Servicio']);
                    $transfer->setDateHour($dateTime); 
                    $transfer->setDate($dateTime);
                    $transfer->setHour($dateTime);
                    $transfer->setFlightNumber($flightNumber);
                    $transfer->setFromStart($desde);
                    $transfer->setToArrival($hasta);
                    $transfer->setIsCollective($record['Tipo traslado']);

                    $transfer->setCustomerCard($customerCard);
                    $manager->persist($transfer);

                }
                // si la carte existe en 1x en bdd mais plusieurs x dans fichier
                else if ( 
                    ($countCLientArrivalBddThisDay == 1) and ($nbReservationNumberInCSV > 1)){   

                    if ($natureTransfer == "arrival") {
                        $transferArrival = $transferArrivalRepository->findByDateNaturetransferClientnumber($reservationNumber, $dateFormat);
                        $customerCard = $transferArrival[0]->getCustomerCard();
                        // on supprime les arrivées supplémentaires déja présentent ce jour
                        $transferArrivals =  $transferArrivalRepository->findBy(['customerCard' => $customerCard, 'date'=> new DateTimeImmutable($dateFormat)]);
                        foreach ($transferArrivals as $transferArrival) {
                            $transferArrivalRepository->remove($transferArrival, true);
                        }
                        if ($incrementDeleteCustomerCard == 0) {
                            $customerCardRepository->remove($customerCard, true);
                        }
                        // creer la carte client
                        $customerCard = new CustomerCard();
    
                        // Créer la nouvelle customerCard
                            // si c'est une arrivée 
                            if ($natureTransfer == "arrival") {
                                $customerCard->setMeetingAt($meetingAt);
                            }
                            // enregistrement des données dans la card courante
                            $customerCard->setReservationNumber($reservationNumber);
                            $customerCard->setJumboNumber($jumboNumber);
                            $customerCard->setHolder($record['Titular']);
                            $customerCard->setAgency($agency);
                            $customerCard->setMeetingPoint($meetingPoint);
                            $customerCard->setAdultsNumber($adultsNumber);
                            $customerCard->setChildrenNumber($childrenNumber);
                            $customerCard->setBabiesNumber($babiesNumber);
                            $customerCard->setStatus($status);
                            $customerCard->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                            $customerCard->setStatusUpdatedBy($user);
                            // meetind At, le lendemain de l'arrivée
                        
                            $customerCard->setReservationCancelled(0);
        
                            $manager->persist($customerCard);
                    }

                    // insérer les informations associées
                        // sinon on va créer un nouvel objet
                        $transfer->setServiceNumber($record['Número Servicio']);
                        $transfer->setDateHour($dateTime); 
                        $transfer->setDate($dateTime);
                        $transfer->setHour($dateTime);
                        $transfer->setFlightNumber($flightNumber);
                        
                        /*                dd($record['Traslado desde']);
                        dd($hotelRepository->findBy(['name' => $record['Traslado desde']])); */
                        $transfer->setFromStart($desde);
                        $transfer->setToArrival($hasta);
                        $transfer->setIsCollective($record['Tipo traslado']);
        
                        $transfer->setCustomerCard($customerCard);
                        $manager->persist($transfer);

                        // on icrémente pour ne plus supprimer ce customer CArd
                        $incrementDeleteCustomerCard++;
                }

                // si la carte existe déja  mais qu'il n'y a pas de transfert ce jour la, ajouter un nouveau transfert
                else if ($customerCardRepository->findOneBy(['reservationNumber' => $reservationNumber]) != null) {
                    $customerCard = $customerCardRepository->findOneBy(['reservationNumber' => $reservationNumber]);

                    //  Si y a deja une arrivée un jour précédent, il faut la rajouter la CARTE CLIENT !!!! 
                    if ($natureTransfer == 'arrival') {
                        // regarder si une arrivée est déja présente un autre jour qu'aujourdhui
                        $isArrivalExistAnotherDay = $transferArrivalRepository->CheckIfArrivalExistAnotherDay($reservationNumber, $dateFormat);
                        
                        if ($isArrivalExistAnotherDay>0) {
                            $customerCard = new CustomerCard();
                            $customerCard->setMeetingAt($meetingAt);

                            // enregistrement des données dans la card courante
                            $customerCard->setReservationNumber($reservationNumber);
                            $customerCard->setJumboNumber($jumboNumber);
                            $customerCard->setHolder($record['Titular']);
                            $customerCard->setAgency($agency);
                            $customerCard->setMeetingPoint($meetingPoint);
                            $customerCard->setAdultsNumber($adultsNumber);
                            $customerCard->setChildrenNumber($childrenNumber);
                            $customerCard->setBabiesNumber($babiesNumber);
                            $customerCard->setStatus($status);
                            $customerCard->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                            $customerCard->setStatusUpdatedBy($user);
                            // meetind At, le lendemain de l'arrivée
                        
                            $customerCard->setReservationCancelled(0);

                            $manager->persist($customerCard);
                        }
                   }
                    

                        // sinon on va créer un nouvel objet transfer
                        $transfer->setServiceNumber($record['Número Servicio']);
                        $transfer->setDateHour($dateTime); 
                        $transfer->setDate($dateTime);
                        $transfer->setHour($dateTime);
                        $transfer->setFlightNumber($flightNumber);
                        
                        /*                dd($record['Traslado desde']);
                        dd($hotelRepository->findBy(['name' => $record['Traslado desde']])); */
                        $transfer->setFromStart($desde);
                        $transfer->setToArrival($hasta);
                        $transfer->setIsCollective($record['Tipo traslado']);

                        $transfer->setCustomerCard($customerCard);
                        $manager->persist($transfer);
            
                }
                // sinon créer la carte client et le transfert associé 
                else {
                    // creer la carte client
                    $customerCard = new CustomerCard();

                    // Créer la nouvelle customerCard

                        // si c'est une arrivée 
                        if ($natureTransfer == "arrival") {
                            $customerCard->setMeetingAt($meetingAt);
                        }

                        // enregistrement des données dans la card courante
                        $customerCard->setReservationNumber($reservationNumber);
                        $customerCard->setJumboNumber($jumboNumber);
                        $customerCard->setHolder($record['Titular']);
                        $customerCard->setAgency($agency);
                        $customerCard->setMeetingPoint($meetingPoint);
                        $customerCard->setAdultsNumber($adultsNumber);
                        $customerCard->setChildrenNumber($childrenNumber);
                        $customerCard->setBabiesNumber($babiesNumber);
                        $customerCard->setStatus($status);
                        $customerCard->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                        $customerCard->setStatusUpdatedBy($user);
                        // meetind At, le lendemain de l'arrivée
                    
                        $customerCard->setReservationCancelled(0);

                        $manager->persist($customerCard);

                    // insérer les informations associées
                        // sinon on va créer un nouvel objet
                        $transfer->setServiceNumber($record['Número Servicio']);
                        $transfer->setDateHour($dateTime); 
                        $transfer->setDate($dateTime);
                        $transfer->setHour($dateTime);
                        $transfer->setFlightNumber($flightNumber);
                        
                        /*                dd($record['Traslado desde']);
                        dd($hotelRepository->findBy(['name' => $record['Traslado desde']])); */
                        $transfer->setFromStart($desde);
                        $transfer->setToArrival($hasta);
                        $transfer->setIsCollective($record['Tipo traslado']);
        
                        $transfer->setCustomerCard($customerCard);
                        $manager->persist($transfer);

                }

            }


            $manager->flush();

            //****************************** Recherche des  Arrivéées de ce jour qui ne sont plus présentes dans le CSV ******************************//
            //****************************************************************************************************************************************//
                //dd($serviceNumbersInCSV);
                $ClientNumbersInCsv = [];
                foreach ($serviceNumbersInCSV as $key => $value){
                    $ClientNumbersInCsv[] = $key;
                }   

            
                // recherche toute les arrivées de ce jour
                $arrivals = $transferArrivalRepository->findBy(['date' => new DateTimeImmutable($dateFormat)]);

                $clientNumberArrivalsInBdd = [];
                foreach ($arrivals as $arrival) { 
                    $clientNumberArrivalsInBdd[] = $arrival->getCustomerCard()->getReservationNumber();
                }
                
                $nonPresentsDansLeNouveauCSV = array_diff($clientNumberArrivalsInBdd, $ClientNumbersInCsv);


                foreach ($nonPresentsDansLeNouveauCSV as $toDelete) {
                    $customerCard = $customerCardRepository->findOneBy(["reservationNumber" => $toDelete]);

                    $arrivalsDayInBddToDelete = $transferArrivalRepository->findBy(['customerCard' => $customerCard, 'date' => new DateTimeImmutable($dateFormat)]);

                    //dd($arrivalDayInBddToDelete);
                    foreach ($arrivalsDayInBddToDelete as $arrival) {
                        $transferArrivalRepository->remove($arrival,true);
                    }
                    
                    // on checkera les customers cartes orphelines dans la page d'accueil
                    $numberArrivals = count($customerCard->getTransferArrivals());

                    $totalTransfersRestant = $numberArrivals;
                    
                    if ($totalTransfersRestant == 0) {
                        $customerCardRepository->remove($customerCard, true);
                    }

                }


                 // recupere tous les enregistrements avec doctrine, pour ce ( jour et arrivée ) avec la clé reservationNumber
                 //! ajouter un champ de bdd dans la table transfer (probably_modified)
                 // TODO : etre sur qu il n y a que la meme date dans le fichier !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                //compare, s'il en manque un , le marquer comme surrement modifié dans la table transfer !!! 

                
            }
            
        return $this->redirectToRoute('home');
    }
}
