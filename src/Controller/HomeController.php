<?php

namespace App\Controller;

use App\Entity\Agency;
use App\Entity\AirportHotel;
use App\Entity\CustomerCard;
use App\Entity\DragAndDrop;
use App\Entity\TransferArrival;
use App\Form\DragAndDropType;
use App\Repository\AgencyRepository;
use App\Repository\AirportHotelRepository;
use App\Repository\CustomerCardRepository;
use App\Repository\MeetingPointRepository;
use App\Repository\StatusRepository;
use App\Repository\TransferArrivalRepository;
use App\Repository\UserRepository;
use App\Services\ErrorsImportManager;
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
        
        $row = ($request->get('row')) ? $request->get('row'): false ;
        return $this->render('team_manager/import.html.twig', [
            'form' => $form,
            'row' => $row
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
                                    ErrorsImportManager $errorsImportManager
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
           $errorDetails[] = 'Code import 1 - Token error, please refresh the page and start again.';
        }

        // test des données recues
        // infos sur le csv
        if ( $error > 0) {
            $errorDetails[] = 'Code import 2 - Error uploading the file. Error code :' . $error;
        }
    
        // Vérifier si le fichier a été correctement téléchargé
        if (!file_exists($fileToUpload)) {
            $errorDetails[] = "Code import 3 - File not found.";
        }
        
        // Vérifier le type de fichier
        if (($mimeType != "text/csv") and ($mimeType != "text/plain")) {
            $errorDetails[] = "Code import 4 - The file extension is not correct !";
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


            //**************************************************************************//
            //**************** DEBUT PREMIER TESTS ET GESTION ERREURS  ****************//
            
            $row = 0;
            // bloquer a une seule date possible par import
            $daysTab = []; // le tableau qui va enregistrer les dates
            foreach ($csv as $record) {
                $row++;

                if (!isset($record['Nº Vuelo/Transporte Origen']) ){
                    $errorsImportManager->addErrors('Three possibilities: 
                    <br>Delimiter: In your csv file, the delimiter must be "' . $csv->getDelimiter() .'"
                    <br>Is the title row is present ?
                    <br>Is Nº Vuelo/Transporte Origen is present and written like that ? ');
                    return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);    
                }

                if ($record['Nº Vuelo/Transporte Origen'] != NULL) {
                    $numbers = explode(", ", $record['Localizadores']);
                    // si il manque un des deux on sort et annonce l'erreur !
                    if ( (!isset($numbers[0])) or (!isset($numbers[1])) ) {
                        if ($record['Titular'] != null){
                            $errorsImportManager->addErrors('Code import 10 - Warning near row '.$row.' <br>Error in your csv file on the <b>"location" cell</b>. <br>The client is ' . $record['Titular'] . '.<br>Flight number: ' . $record['Nº Vuelo/Transporte Origen']);
                        } else {
                            $errorsImportManager->addErrors('Code import 11 - Warning near row '.$row.' <br>Error in your csv file on a <b>"location" cell</b>. <br>This client does not have a name in the csv file. <br>Flight number: ' . $record['Nº Vuelo/Transporte Origen']);
                        }

                        return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                    }
                    $jumboNumber = trim($numbers[0]);
                    $reservationNumber = trim($numbers[1]);
                    $serviceNumbersInCSV[] =  $reservationNumber;
                    // récupère le jour et l heure de l'arrivée 
                    if ($record['Fecha/Hora Origen']  != NULL ) { 
                        $record['Fecha/Hora Origen'] = trim($record['Fecha/Hora Origen']);
                        $dateTime = explode(" ", $record['Fecha/Hora Origen']);

                         // Gestion des erreurs 
                        if ( (!isset($dateTime[0])) or (!isset($dateTime[1])) ) {
                            if ($record['Titular'] != null){
                                $errorsImportManager->addErrors('Code import 12 - Warning near row '.$row.' <br>Error in your csv file on the "Fecha/Hora Origen" cell: <b>Wrong date formatting</b> <br>The client is ' . $record['Titular'] . '.<br>Flight number: ' . $record['Nº Vuelo/Transporte Origen']);
                            } else {
                                $errorsImportManager->addErrors('Code import 13 - Warning near row '.$row.' <br>Error in your csv file on a "Fecha/Hora Origen" cell: <b>Wrong date formatting</b> <br>This client does not have a name in the csv file. <br>Flight number: ' . $record['Nº Vuelo/Transporte Origen']);
                            }
                            return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                        }    
                    } // fecha origen null !! error 
                    else {
                        if ($record['Titular'] != null){
                            $errorsImportManager->addErrors('Code import 14 - Warning near row '.$row.' <br>Error in your csv file on the "Fecha/Hora Origen" cell: <b>Can not be null</b>. <br>The client is ' . $record['Titular'] . '.<br>Flight number: ' . $record['Nº Vuelo/Transporte Origen']);
                        } else {
                            $errorsImportManager->addErrors('Code import 15 - Warning near row '.$row.' <br>Error in your csv file on a "Fecha/Hora Origen" cell: <b>Can not be null</b>. <br>This client does not have a name in the csv file. <br>Flight number: ' . $record['Nº Vuelo/Transporte Origen']);
                        }
                        return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                    }
                    // si il n'y a pas d'erreur
                    $date = explode("/", $dateTime[0]);
                    $dateFormat = $date[2] . '-' . $date[1] .'-'. $date[0];
                    $dateTime = new DateTimeImmutable($dateFormat . ' ' .$dateTime[1]);

                    // enregsitrement des dates (on utilise la row pour différencier les clés plutot que d initialiser un $i)
                    if (!in_array ($dateFormat, $daysTab)) {
                        $daysTab[]= $dateFormat;
                        $errorsImportManager->addErrors('Code import 16 - There are several arrival dates in this csv file. Make sure all arrival dates are on the same day! <br>
                                                        Flight number:'.  $record['Nº Vuelo/Transporte Origen'].' <br>
                                                        Row: '.$row.'<br>
                                                        Date: '. $date[1] . '-' . $date[0] .'-'. $date[2]
                                                        );
                    }
                }

                $numeroPasajeros = explode(" ", $record['Número pasajeros']);
                // si la case 0 est nulle == la cellule dans csv est nulle
                if ($numeroPasajeros[0] == null) {
                    $errorsImportManager->removeErrors();
                    $errorsImportManager->addErrors('Code import <b>18.bis</b>: There is a formatting error on the number of passengers. <br>
                    Row: '.$row.'<br>
                    Number of passengers: <b> is NULL </b> must be in the format: <b>A: 2 N: 1 B: 1</b>'); 
                    return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                }
                // si le formattage est mauvais le [5] == au nombre de Bébé(derniere entrée de la cellule) sera absent !
                if (!isset($numeroPasajeros[5])) {
                    $errorsImportManager->removeErrors();
                    $errorsImportManager->addErrors('Code import 18: There is a formatting error on the number of passengers. <br>
                                                    Row: '.$row.'<br>
                                                    Number of passengers: <b>'. $record['Número pasajeros']. '</b> must be in the format: <b>A: 2 N: 1 B: 1</b>'); 
                    return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                }
                
                if ( ($record['Traslado desde'] == null) or ($record['Traslado hasta'] == null)) {
                    $errorsImportManager->removeErrors();
                    $airportTest = ($record['Traslado desde'] == null) ? 'must contain data but is equal to NULL' : $record['Traslado desde'];
                    $hotelTest = ($record['Traslado hasta'] == null) ? 'must contain data but is equal to NULL' : $record['Traslado hasta'];
                    $errorsImportManager->addErrors('Code import 19: There is a formatting error on Airport or Hotel. Both cells must contain data while one is null.<br>
                                                    Row: '.$row.'<br>
                                                    Airport: '. $airportTest .'<br>
                                                    Hotel: '. $hotelTest); 
                    return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);

                }


                
            }

            if (count($daysTab) > 1) {
                return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
            } else {
                // on supprime parceque sinon on va afficher dans les erreurs 'there are several arrival ... alors qu il n y en a qu une
                $errorsImportManager->removeErrors();
            }
            // si il n y a qu'une date on regarde qu'elle ne soit pas plus de 15 jours par rapport a aujourd'hui
            // aujourd'hui plus 15 jours 
            $now = new DateTimeImmutable('now');
            $maxDay = 15;
            $interval = $now->diff($dateTime);
            if ($interval->format("%a") > $maxDay) {
                if ($now<$dateTime) {
                    $errorsImportManager->addErrors('Code import 17 - You cannot import a date older than 15 days.<br>  Date in csv file: '. $date[1] . '-' . $date[0] .'-'. $date[2]);
                    return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                }
            }

            //**************** FIN PREMIER TESTS ET GESTION DES ERREURS ****************//
            //**************************************************************************//
            


            //$serviceNumbersInCSV[] = 1611603;
            // return [1611603 => 1 , 1611604 => 2 ]
            $serviceNumbersInCSV = array_count_values ($serviceNumbersInCSV);
            $incrementDeleteCustomerCard = 0;

            //***********************************************************************************************************************//
            //********************************* début de l'extraction de la LIGNE de données du csv *********************************//

            // traitement de la première entrée, puis de la deuxième etc ...
            $row = 0;
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
                // si l'entréee est annulée elle doit être ignorée (deja en tolower)
                if (( $record['Estado']  == "cancelado") or ($record['Estado']  == "canceled")) { 
                    continue;
                }
                //************************************************************************//
                //************************* FIN ENTREES IGNOREES *************************//
                
                //*************************** DEBUT EXTRACTION**** ***********************************************************//
                //************************************************************************************************************//
                //! extraction de jumboNumber et reservationNumber car ils se trouvent dans la meme case dans le csv 
                $numbers = explode(", ", $record['Localizadores']);
                $jumboNumber = trim($numbers[0]);
                $reservationNumber = trim($numbers[1]);
                
                //! extraction du nombre d'adultes/enfants/bébés car dans la même case dans le csv
                $numeroPasajeros = explode(" ", $record['Número pasajeros']);
                $adultsNumber = trim($numeroPasajeros[1]);
                $childrenNumber = trim($numeroPasajeros[3]);
                $babiesNumber = trim($numeroPasajeros[5]);


                //***************** MISE A JOUR AGENCE, AIRPORT ET HOTEL *****************//
                //************************************************************************//

                    // si l'hotel ou l aéroport n existe pas, on le MAJ
                    // pour savoir si c est un aéroport ou si c est un hotel ca depend de la nature du transfer
                        $airportHotel = new AirportHotel();
                        $desde = $airportHotelRepository->findOneBy(['name' => $record['Traslado desde']]);
                        $hasta = $airportHotelRepository->findOneBy(['name' => $record['Traslado hasta']]);


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
                            $transfer= new TransferArrival();
                            $flightNumber = $record['Nº Vuelo/Transporte Origen'];
                            
                            // MAJ Airport: check si cet Aéroport existe
                            if (empty($desde) ){
                                // ajouter l'airport dans la table
                                $airportHotel->setName($record['Traslado desde']);
                                $airportHotel->setIsAirport(1);
                                $manager->persist($airportHotel);
                                $manager->flush();
                                
                                $desde = $airportHotel;
                            };
                            // MAJ Hotel: check si cet Hotel existe
                            if (empty($hasta) ){
                                // ajouter l'hotel dans la table
                                $airportHotel->setName($record['Traslado hasta']);
                                $airportHotel->setIsAirport(0);
                                $manager->persist($airportHotel);
                                $manager->flush();
                                // hasta = $airport hotel courant
                                $hasta = $airportHotel;
                            };
                            // MAJ Agence: si l agence n existe pas on la met a jour
                            $agency = $agencyRepository->findOneBy(['name' => $record['Agencia']]);
                            if (empty($agency)) {
                                $agency = new Agency();
                                $agency->setName($record['Agencia']);
                                $agency->setIsActive(1);
                                $manager->persist($agency);
                                $manager->flush();
                            }
                //*************** FIN MISE A JOUR AGENCE, AIRPORT ET HOTEL ***************//
                //************************************************************************//                
                

                //************************************************************************//
                //***************** RECHERCHE DE L EXISTANT, CSV ET BDD ******************//
                
                // CSV    
                    // combien de fois ce n° client est présent dans le csv
                    $nbReservationNumberInCSV = $serviceNumbersInCSV[$reservationNumber];
                  

                // BDD
                    // ce numéro client est il déja présent dans la bdd
                        $clientExist = false;
                        $clientArrivalExist = false;
                        $clientArrivalExistThisDay = false;
                        $countCLientArrivalBddThisDay = 0;
                        $arrivalAnotherDay = false;
                
                // CSV + BDD
                    // + recupere le nombre de fois présent en bdd arrivée
                    $clientNumberArrivalList = [];
                    if ($record['Nº Vuelo/Transporte Origen'] != NULL) {
                        // ce client existe t'il en bdd ?
                        $clientCards = $customerCardRepository->findOneBy(['reservationNumber'=> $reservationNumber, 'holder' => $record['Titular']]);
                        if ($clientCards){
                            $clientExist = true;
                            // y a t il une arrivée pour ce client et combien ?
                            $numberTotalClientArrivalInBdd = (count($transferArrivalRepository->findBy(['customerCard'=> $clientCards])));
                            $clientArrivalExist = ($numberTotalClientArrivalInBdd > 0) ? true : false;
                        } else {
                            $numberTotalClientArrivalInBdd = 0;
                        }
                        // Combien de fois le client existe dans l'arrivée ce jour ! (transfer Arrival)
                        $clientNumberArrivalList = $transferArrivalRepository->findByDateNaturetransferClientnumber($reservationNumber,$dateFormat, $record['Titular']);               
                        // combien existe t'il de transfert arrivée ce jour la pour ce client
                        $countCLientArrivalBddThisDay = count($clientNumberArrivalList);
                        if ($countCLientArrivalBddThisDay > 0){
                            // Il existe au moins un tranfert Arrivée ce jour la pour ce client
                            $clientArrivalExistThisDay = true;
                        }
                        // existe t il une arrivée un autre jour ?
                        $arrivalAnotherDay = ($numberTotalClientArrivalInBdd > $countCLientArrivalBddThisDay)? true : false;
                    } 

                //*************** FIN RECHERCHE DE L EXISTANT, CSV ET BDD ****************//
                //************************************************************************//
                    
                  
                // si bdd et csv ce jour == 1 c est une mise à jour simple 
                // LA CARTE CLIENT ET L'ARRIVEE EXISTE EN  FOIS Donc MISE A JOUR !!!!
                if (($nbReservationNumberInCSV == $countCLientArrivalBddThisDay) AND ($nbReservationNumberInCSV == 1)) {
                    $transferArrivals = $transferArrivalRepository->findByDateNaturetransferClientnumber($reservationNumber, $dateFormat,  $record['Titular']);
                    $customerCard = $transferArrivals[0]->getCustomerCard();
                        dd('ici');
                    // mettre a jour le customerCard    
                    $customerCard->setReservationNumber($reservationNumber);
                    $customerCard->setJumboNumber($jumboNumber);
                    $customerCard->setHolder($record['Titular']);
                    $customerCard->setAgency($agency);
                    // TODO: $customerCard->setReservationCancelled(0);

                    // mettre a jour l'arrival
                     // MAJ du meeting ?
                    // si la date est différente on met a jour le briefing
                    $dateTransfer = $transferArrivals[0]->getDate()->format('Y/m/d');
                    $heureTransfer = $transferArrivals[0]->getHour()->format('H:i');
                    $dateTimeBDD = new DateTime($dateTransfer . '' . $heureTransfer);
                    
                
                    /* if ( ($fechaHora != $transfer->getDateHour()->format('m/d/Y'. ' H:i')) ){  */
                    if ( ($dateTime != $dateTimeBDD) ){ 
                        // mettre ajour le briefing
                        if ($record['Fecha/Hora Origen']) {

                            // enregistrement du meeting

                            $transferArrivals[0]->setMeetingAt($meetingAt);
                        }
            
                    }
                    $transferArrivals[0]->setCustomerCard($customerCard);
                    $transferArrivals[0]->setFromStart($desde);
                    $transferArrivals[0]->setToArrival($hasta);
                    $transferArrivals[0]->setServiceNumber($record['Número Servicio']);
                    $transferArrivals[0]->setDateHour($dateTime); 
                    $transferArrivals[0]->setFlightNumber($flightNumber);
                    $transferArrivals[0]->setDate($dateTime);
                    $transferArrivals[0]->setHour($dateTime);
                    $transferArrivals[0]->setStatus($status);
                    $transferArrivals[0]->setStatusUpdatedBy($user);
                    $transferArrivals[0]->setAdultsNumber($adultsNumber);
                    $transferArrivals[0]->setChildrenNumber($childrenNumber);
                    $transferArrivals[0]->setBabiesNumber($babiesNumber);
                    $transferArrivals[0]->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                    $transferArrivals[0]->setMeetingPoint($meetingPoint);
                    $manager->persist($transferArrivals[0]);
                

                }
                // si la carte existe déja  mais qu'il n'y a pas de transfert ce jour la, 
                // creer la nouvelle arrivée
                else if (($clientExist) and (!$clientArrivalExistThisDay)) {
                 
                    // si y a deja une arrivée un autre jour, on va créer une nouvelle carte
/*                     if ($arrivalAnotherDay){
                        $customerCard = new CustomerCard();
                        $persist = true;
                    } else {
                        // sinon on va juste modifier celle existante et ajouter l arrivée
                        $customerCard = $customerCardRepository->findOneBy(['reservationNumber' => $reservationNumber]);
                        $persist = false;
                    } */
                    // on va juste modifier celle existante et ajouter l arrivée
                    $customerCard = $customerCardRepository->findOneBy(['reservationNumber' => $reservationNumber, 'holder' => $record['Titular']]);

                    // enregistrement des données dans la card courante
                    // meetind At, le lendemain de l'arrivée
                    $customerCard->setReservationNumber($reservationNumber);
                    $customerCard->setJumboNumber($jumboNumber);
                    $customerCard->setHolder($record['Titular']);
                    $customerCard->setAgency($agency);

                    $transferArrival = new TransferArrival();
                    $transferArrival->setMeetingPoint($meetingPoint);
                    $transferArrival->setAdultsNumber($adultsNumber);
                    $transferArrival->setChildrenNumber($childrenNumber);
                    $transferArrival->setBabiesNumber($babiesNumber);
                    $transferArrival->setStatus($status);
                    $transferArrival->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                    $transferArrival->setStatusUpdatedBy($user);
                    $transferArrival->setMeetingAt($meetingAt);
                    $transferArrival->setServiceNumber($record['Número Servicio']);
                    $transferArrival->setDateHour($dateTime); 
                    $transferArrival->setDate($dateTime);
                    $transferArrival->setHour($dateTime);
                    $transferArrival->setFlightNumber($flightNumber);
                    $transferArrival->setFromStart($desde);
                    $transferArrival->setToArrival($hasta);
                    $transferArrival->setCustomerCard($customerCard);
                    $manager->persist($transferArrival);

                }

                // Doublons ce jour: si l'arrivée du client existe ce jour et en plusieurs fois 
                else if (($countCLientArrivalBddThisDay > 1))  {
                   
                    // on va supprimer toutes les arrivées présentes 
                        $transferArrivals = $transferArrivalRepository->findByDateNaturetransferClientnumber($reservationNumber, $dateFormat, $record['Titular']);
                        $customerCard = $transferArrivals[0]->getCustomerCard();
                        // on modifie la carte client enregistrement des données dans la card courante
                        $customerCard->setReservationNumber($reservationNumber);
                        $customerCard->setJumboNumber($jumboNumber);
                        $customerCard->setHolder($record['Titular']);
                        $customerCard->setAgency($agency);
                        
                        // on supprime les arrivées supplémentaires déja présentent ce jour
                        //$transferArrivals =  $transferArrivalRepository->findBy(['customerCard' => $customerCard, 'date'=> new DateTimeImmutable($dateFormat)]);
                        foreach ($transferArrivals as $transferArrival) {
                            $transferArrivalRepository->remove($transferArrival, true);
                        }
                        
                        
                                // Création de l'arrival
                                $transferArrival = new TransferArrival();                
                                $transferArrival->setServiceNumber($record['Número Servicio']);
                                $transferArrival->setDateHour($dateTime); 
                                $transferArrival->setDate($dateTime);
                                $transferArrival->setHour($dateTime);
                                $transferArrival->setFlightNumber($flightNumber);
                                $transferArrival->setFromStart($desde);
                                $transferArrival->setToArrival($hasta);
                                $transferArrival->setMeetingAt($meetingAt);
                                $transferArrival->setMeetingPoint($meetingPoint);
                                $transferArrival->setAdultsNumber($adultsNumber);
                                $transferArrival->setChildrenNumber($childrenNumber);
                                $transferArrival->setBabiesNumber($babiesNumber);
                                $transferArrival->setStatus($status);
                                $transferArrival->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                                $transferArrival->setStatusUpdatedBy($user);

                                $transferArrival->setCustomerCard($customerCard);
                                $manager->persist($transferArrival);
                  

                }
                // si l arrivée existe en 1x en bdd mais plusieurs x dans fichier
                else if (($countCLientArrivalBddThisDay == 1) and ($nbReservationNumberInCSV > 1)){   

                        $transferArrival = $transferArrivalRepository->findByDateNaturetransferClientnumber($reservationNumber, $dateFormat, $record['Titular']);
                        $customerCard = $transferArrival[0]->getCustomerCard();
                        // on supprime les arrivées supplémentaires déja présentent ce jour
                        $transferArrivals =  $transferArrivalRepository->findBy(['customerCard' => $customerCard, 'date'=> new DateTimeImmutable($dateFormat)]);
                        if ($incrementDeleteCustomerCard == 0) {
                            foreach ($transferArrivals as $transferArrival) {
                                $transferArrivalRepository->remove($transferArrival, true);
                            }
                        }
                            // Modification: On met a jour la carte au besoin

                            $customerCard->setReservationNumber($reservationNumber);
                            $customerCard->setJumboNumber($jumboNumber);
                            $customerCard->setHolder($record['Titular']);
                            $customerCard->setAgency($agency);
                            $manager->persist($customerCard);
                            
                            // insérer les informations associées
                            // sinon on va créer un nouvel objet
                            $transferArrival = new $transferArrival();
                            $transferArrival->setMeetingPoint($meetingPoint);
                            $transferArrival->setAdultsNumber($adultsNumber);
                            $transferArrival->setChildrenNumber($childrenNumber);
                            $transferArrival->setBabiesNumber($babiesNumber);
                            $transferArrival->setStatus($status);
                            $transferArrival->setMeetingAt($meetingAt);
                            $transferArrival->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                            $transferArrival->setStatusUpdatedBy($user);                     
                            $transferArrival->setServiceNumber($record['Número Servicio']);
                            $transferArrival->setDateHour($dateTime); 
                            $transferArrival->setDate($dateTime);
                            $transferArrival->setHour($dateTime);
                            $transferArrival->setFlightNumber($flightNumber);
                            $transferArrival->setFromStart($desde);
                            $transferArrival->setToArrival($hasta);
                            $transferArrival->setCustomerCard($customerCard);
                            $manager->persist($transferArrival);

                        // on icrémente pour ne plus supprimer ce customer Card
                        $incrementDeleteCustomerCard++;
                }
                // sinon créer la carte client et le transfert associé 
                else {
                    // creer la carte client
                    $customerCard = new CustomerCard();
                    // Créer la nouvelle customerCard
                    // enregistrement des données dans la card courante
                    $customerCard->setReservationNumber($reservationNumber);
                    $customerCard->setJumboNumber($jumboNumber);
                    $customerCard->setHolder($record['Titular']);
                    $customerCard->setAgency($agency);
                    $manager->persist($customerCard);
                    
                    $transferArrival = new TransferArrival();
                    // insérer les informations associées
                    // sinon on va créer un nouvel objet
                    $transferArrival->setServiceNumber($record['Número Servicio']);
                    $transferArrival->setDateHour($dateTime); 
                    $transferArrival->setDate($dateTime);
                    $transferArrival->setHour($dateTime);
                    $transferArrival->setFlightNumber($flightNumber);
                    $transferArrival->setMeetingPoint($meetingPoint);
                    $transferArrival->setAdultsNumber($adultsNumber);
                    $transferArrival->setChildrenNumber($childrenNumber);
                    $transferArrival->setBabiesNumber($babiesNumber);
                    $transferArrival->setStatus($status);
                    $transferArrival->setStatusUpdatedAt(new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo')));
                    $transferArrival->setStatusUpdatedBy($user);
                    $transferArrival->setMeetingAt($meetingAt);
                    $transferArrival->setFromStart($desde);
                    $transferArrival->setToArrival($hasta);
                    $transferArrival->setCustomerCard($customerCard);
                    $manager->persist($transferArrival);

                }

                $row++; 
            }


            $manager->flush();

            //****************************** Recherche des  Arrivéées de ce jour qui ne sont plus présentes dans le CSV ******************************//
            //****************************************************************************************************************************************//
                 $ClientNumbersInCsv = [];
                foreach ($serviceNumbersInCSV as $key => $value){
                    $ClientNumbersInCsv[] = $key;
                }   

                if ( (isset($dateFormat)) and ($dateFormat!= null) ) {
                
                    // recherche toute les arrivées de ce jour
                    $arrivals = $transferArrivalRepository->findBy(['date' => new DateTimeImmutable($dateFormat)]);

                    $clientNumberArrivalsInBdd = [];
                    foreach ($arrivals as $arrival) { 
                        $clientNumberArrivalsInBdd[] = $arrival->getCustomerCard()->getReservationNumber();
                    }

                    // !!! nom présent ... mais en cas de doublons qui passerait à un dans le csv, cela ne fonctionne pas en bdd !
                    // raison: le numéro de client est quand meme présent dans le csv donc impossible comme ca de savoir qu il y a un en moins
                    // dans le csv car le numéro existe encore (1x au lieu de 2 mais ca existe ...)
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
                }            
        
                return $this->redirectToRoute('app_import', ['row'=> $row]);
    }
}
