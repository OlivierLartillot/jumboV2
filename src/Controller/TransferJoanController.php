<?php

namespace App\Controller;

use App\Entity\AirportHotel;
use App\Entity\TransferArrival;
use App\Entity\TransferDeparture;
use App\Entity\TransferInterHotel;
use App\Entity\TransferJoan;
use App\Entity\TransferVehicleArrival;
use App\Entity\TransportCompany;
use App\Form\TransferJoanType;
use App\Repository\AirportHotelRepository;
use App\Repository\CustomerCardRepository;
use App\Repository\TransferArrivalRepository;
use App\Repository\TransferDepartureRepository;
use App\Repository\TransferInterHotelRepository;
use App\Repository\TransferJoanRepository;
use App\Repository\TransferVehicleArrivalRepository;
use App\Repository\TransportCompanyRepository;
use App\Services\ErrorsImportManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Route('team-transfer')]
class TransferJoanController extends AbstractController
{
    #[Route('/import/transfer', name: 'app_transfer_import', methods: ['GET'])]
    public function import(TransferJoanRepository $transferJoanRepository, Request $request): Response
    {

        $errorClients = ($request->get('errorClients')) ? $request->get('errorClients') : [];
        $numberOfRows = $request->get('numberOfRows');
        $insertedLine = $request->get('insertedLine') ? $request->get('insertedLine') : 0;
        
        $numberOfRowsType = gettype($numberOfRows);


        return $this->render('transfer/import.html.twig', [
            'transfer_joans' => $transferJoanRepository->findAll(),
            'errorClients' => $errorClients,
            'numberOfRows' => $numberOfRows,
            'insertedLine' => $insertedLine,
            'numberOfRowsType' => $numberOfRowsType,
        ]);
    }

    #[Route('/traitement_xls', name: 'admin_transfer_traitement_csv', methods: ['POST'])]
    public function index(Request $request, 
                          EntityManagerInterface $manager,
                          CustomerCardRepository $customerCardRepository,
                          TransferArrivalRepository $transferArrivalRepository,
                          TransferVehicleArrivalRepository $transferVehicleArrivalRepository,
                          TransferInterHotelRepository $transferInterHotelRepository,
                          TransferDepartureRepository $transferDepartureRepository,
                          TransportCompanyRepository $transportCompanyRepository, 
                          ErrorsImportManager $errorsImportManager,
                          AirportHotelRepository $airportHotelRepository,
                          ): Response
    {

        $fileToUpload = $request->files->get('drag_and_drop')["fileToUpload"];
       /*  $mimeType = $fileToUpload->getMimeType(); */
        $error = $fileToUpload->getError();

        $ecrituresDeLlegada = ['llegada', 'llegadas'];
        $ecrituresDeInterHotel = ['interhotel', 'interhotels' ,'inter-hotel' ,'inters-hotel','inter-hotels','inters-hotels', 'inter hotel', 'inters hotel', 'inter hotels', 'inters hotels'];
        $ecrituresDeSalidas = ['salida', 'salidas'];

 
        // récupération du token
        $submittedToken = $request->request->get('token');
                    
        $errorDetails = [];
        // 'delete-item' is the same value used in the template to generate the token
        if (!$this->isCsrfTokenValid('upload-item', $submittedToken)) {
           $errorDetails[] = 'Code import 1 - Token error, please refresh the page and start again.';
        }

        // test des données recues
        // infos sur le csv
        if ($error > 0) {
            $errorDetails[] = 'Code import 2 - Error uploading the file. Error code :' . $error;
        }
    
        // Vérifier si le fichier a été correctement téléchargé
        if (!file_exists($fileToUpload)) {
            $errorDetails[] = "Code import 3 - File not found.";
        }
        // Vérifier le type de fichier
        if ( ($fileToUpload->getClientOriginalExtension() != "xlsm") and ($fileToUpload->getClientOriginalExtension() != "xlsx") ){
            $errorDetails[] = "Code import 4 - The file extension is not correct !";
        }
        
        if (count($errorDetails) > 0) {
            
            return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorDetails]);

        }

        // Charger le fichier Excel
        $spreadsheet = IOFactory::load($fileToUpload);
        //$spreadsheet->setActiveSheetIndexByName('OPERATIVA');
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // 1. on va récupérer le nom pour savoir si c est llegada/interHotel/salida
        // LLEGADAS - INTERHOTEL - SALIDAS -> llegadas - interhotel - salidas
        $natureTransfer = strtolower($rows[2][1]);  
        
        if (in_array($natureTransfer,$ecrituresDeLlegada)) { 
            $natureTransferRepository = $transferArrivalRepository;
            $newTransfer = new TransferArrival ;
            $newTransferVehicleArrival = new TransferVehicleArrival();
        }
        else if (in_array($natureTransfer,$ecrituresDeInterHotel)) { 
            $natureTransferRepository = $transferInterHotelRepository;
            $newTransfer = new TransferInterHotel();
        }
        else if (in_array($natureTransfer,$ecrituresDeSalidas)) { 
            $natureTransferRepository = $transferDepartureRepository;
            $newTransfer = new TransferDeparture();
        } else {
            $listeLlegada = '';
            $listeInterHotel = '';
            $listesalidas = '';
            foreach ($ecrituresDeLlegada as $ecriture) { $listeLlegada .= '- ' . $ecriture . ' '; }
            foreach ($ecrituresDeInterHotel as $ecriture) { $listeInterHotel .= '- ' . $ecriture . ' '; }
            foreach ($ecrituresDeSalidas as $ecriture) { $listesalidas .= '- ' . $ecriture . ' '; }

            $errorsImportManager->addErrors('Code import 30 - In cell B3 should be written:');
            $errorsImportManager->addErrors('<b>Arrival:</b> ' . $listeLlegada);
            $errorsImportManager->addErrors('<b>InterHotel:</b> ' .$listeInterHotel);
            $errorsImportManager->addErrors('<b>Departure:</b>  ' . $listesalidas);
            return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' =>  $errorsImportManager->getErrors()]);
        }
        
        if (strtolower($rows[8][0]) != 'num reserva') {
            $errorsImportManager->addErrors('Code import 31 - In cell A9 should be written: Num Reserva');
            return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
        }
        // check toute les lignes et problèmes en amont pour ne pas executer le fichier
        $firstRow = 9;
        $i = 0;
        // on remplit pour voir si il y a plusieurs dates dans ce fichier
        $dates = [];
        $inOuts = [];
        $serviceNumbersInCSV = [];
        $reservationNumberFlightNumberIncsv = [];

        foreach ($rows as $row) {
            // les lignes à ignorer
            if ( ($i<$firstRow) AND ($row[0] == NULL OR $row[1] == NULL OR $row[2] == NULL) OR ($row[0] == 'N. Reserva' OR $row[1] == "Agencia") ) {
                $i++;
                continue;
            }    
            // si les deux premieres cellules de la ligne sont vides 
            // on considère qu'il n'y a plus de données à récupérer
            // et on sort de la boucle
            else if ( ($row[0] == null) and ($row[1] == null) ) {
                break;
            }
            // on va checker les erreurs dans les lignes à garder
            else {
                // check le format de la date, s'il n'est pas bon s'est inadmissible :p
                $testDate = explode("/", $row[4]);
                if (!isset($testDate[2])) {
                    $errorsImportManager->addErrors('Code import 33 -At least one date is not in day/month/year format: ' . $row[4]); 
                    return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                }
                // si il existe plusieurs dates différentes => erreur
                if (!in_array($row[4], $dates)) {
                $dates[] = $row[4];
                    if (count($dates) > 1) {
                        $errorsImportManager->addErrors('Code import 32 - There are several dates in the file. Make sure all "Dia Vuelo" are on the same day !');
                        return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]); 
                    }
                }
                //check si tous les in/out sont pareils
                if (!in_array($row[12], $inOuts)) {
                $dates[] = $row[12];
                    if (count($inOuts) > 1) {
                        $errorsImportManager->addErrors('Code import 33 - There are several in/out in the file. Make sure all "In/Out" are the same !'); 
                        return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                    }
                }
                // liste des écritures valables
                $liste = '';
                // check si le in/out est == a B3
                if (in_array($natureTransfer,$ecrituresDeLlegada)) {
                    if (!in_array(strtolower($row[12]),$ecrituresDeLlegada))  {
                        $natureTransferError = true;
                        
                        foreach ($ecrituresDeLlegada as $ecriture) { $liste .= '- ' . $ecriture . ' '; }
                        $errorsImportManager->addErrors('Code import 30 - In column M (in/out) should be written: ' . $liste);
                        return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                    }
                } 
                else if (in_array($natureTransfer,$ecrituresDeInterHotel)) {
                    if ( !in_array(strtolower($row[12]), $ecrituresDeInterHotel) )
                    {
                        $natureTransferError = true;
                        foreach ($ecrituresDeInterHotel as $ecriture) { $liste .= '- ' . $ecriture . ' '; }
                        $errorsImportManager->addErrors('Code import 30 - In column M (in/out) should be written: ' . $liste);
                        return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                    }
                }
                else {
                    if (!in_array(strtolower($row[12]),$ecrituresDeSalidas)) {
                        $natureTransferError = true;
                        foreach ($ecrituresDeSalidas as $ecriture) { $liste .= '- ' . $ecriture . ' '; }
                        $errorsImportManager->addErrors('Code import 30 - In column M (in/out) should be written: ' . $liste);
                        return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                    }
                }
                //sinon renvoie l'erreur
                if ((isset($natureTransferError)) and ($natureTransferError) ) {
                    $errorsImportManager->addErrors('Code import 34 - the nature of the transfer declared in B3 is not the same as in/out.'); 
                    return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorsImportManager->getErrors()]);
                }

                //check si la compagnie existe sinon il faut la rajouter en bdd
                $suplidor = trim(strtolower($row[16])); 
                if ($suplidor != null) {
                    $suplidor=str_replace("\n"," ",$suplidor);
                    $suplidor=str_replace("\r"," ",$suplidor);
                    // on regarde si elle existe
                    $transportCompany = $transportCompanyRepository->findOneBy(['name' => $suplidor]);
                    // sinon, ajouter le transportCompany en bdd
                    if (!$transportCompany) {
                        $transportCompany = new TransportCompany();
                        $transportCompany->setName($suplidor);
                        $manager->persist($transportCompany);
                        $manager->flush();
                    }

                }

                $reservationNumber = trim($row[0]);
                $serviceNumbersInCSV[] = $reservationNumber;
                $vuelo = trim(strtolower($row[3])); 
                $reservationNumberFlightNumberIncsv[] = $reservationNumber .'-'. $vuelo ;
            }
        }
        $countEachServiceNumbersInCSV = array_count_values ($serviceNumbersInCSV);
/*         $countEachreservationNumberFlightNumberIncsv = array_count_values ($reservationNumberFlightNumberIncsv);
 */

        /*******************************************************************************************************/
        /*********************************** Début des traitements *********************************************/
        $i = 0;
        $errorClients = [];
        $numberOfRows = 0;
        $insertedLine = 0;
        foreach ($rows as $row) {

            if ( ($i<9) AND ($row[0] == NULL OR $row[1] == NULL OR $row[2] == NULL) OR ($row[0] == 'N. Reserva' OR $row[1] == "Agencia") ) {
                $i++;
                continue;
            }
            // si les deux premieres cellules de la ligne sont vides 
            // on considère qu'il n'y a plus de données à récupérer
            // et on sort de la boucle
            else if ( ($row[0] == null) and ($row[1] == null) ) {
                
                break;
            }
            else {

                $numberOfRows++;

                // On définit la nature du transfert en fonction de ce qui est écrit dans le fichier 
                if (in_array($natureTransfer,$ecrituresDeLlegada)) { 
                    $natureTransferRepository = $transferArrivalRepository;
                    $newTransfer = new TransferArrival ;
                    $newTransferVehicleArrival = new TransferVehicleArrival();
                }
                else if (in_array($natureTransfer,$ecrituresDeInterHotel)) { 
                    $natureTransferRepository = $transferInterHotelRepository;
                    $newTransfer = new TransferInterHotel();
                }
                else if (in_array($natureTransfer,$ecrituresDeSalidas)) { 
                    $natureTransferRepository = $transferDepartureRepository;
                    $newTransfer = new TransferDeparture();
                }
        
                // on extrait les cellules pour initialiser les variables
                $reservaId= trim($row[0]); 
                $agencia = trim(strtolower($row[1])); 
                $nombre = trim(strtolower($row[2])); 
                $vuelo = trim(strtolower($row[3])); 
                $dia_vuelo = $row[4]; 
                $hora_v = $row[5]; 
                $desde = trim(strtolower($row[6])); 
                $hasta = trim(strtolower($row[7])); 
                $tipo_trf = trim(strtolower($row[8])); 
                $tipo_trf = ($tipo_trf == "colectivo") ? true : false ; 
                $ad = trim($row[9]); 
                $ni = trim($row[10]); 
                $bb = trim($row[11]); 
                $in_out = trim(strtolower($row[12])); 
                $n_veh = trim($row[13]); 
                $t_veh = trim(strtolower($row[14]));     
                $pickup = trim($row[15]); 
                $suplidor = trim(strtolower($row[16])); 
                $bono = trim(strtolower($row[17])); 
                $zonas = trim(strtolower($row[18]));
               
                $date= explode("/", $dia_vuelo);
                $dateFormat = $date[2] . '-' . $date[1] .'-'. $date[0];
                $dia_vuelo = new DateTimeImmutable($dateFormat);
        
                if ($hora_v != "") {
                    $hour = explode(':', $hora_v);
                    $dateTimeFormat = $hour[0] .':'. $hour[1];
                    $hour = new DateTimeImmutable($dateTimeFormat);
                }
                
                if ($pickup != "") {
                    $pickup = explode(':', $pickup);
                    $dateTimeFormat = $pickup[0] .':'. $pickup[1];
                    $pickup = new DateTimeImmutable($dateTimeFormat);
                }
                
            }
            if ($agencia != null) {
                $agencia=str_replace("\n"," ",$agencia);
                $agencia=str_replace("\r"," ",$agencia);
            }
            if ($nombre != null) {
                $nombre=str_replace("\n"," ",$nombre);
                $nombre=str_replace("\r"," ",$nombre);
            }
            if ($zonas != null) {
                $zonas=str_replace("\n"," ",$zonas);
                $zonas=str_replace("\r"," ",$zonas);
            }
            
            if ($desde != null) {
                $desde=str_replace("\n"," ",$desde);
                $desde=str_replace("\r"," ",$desde);
            }
            if ($hasta != null) {
                $hasta=str_replace("\n"," ",$hasta);
                $hasta=str_replace("\r"," ",$hasta);
            }
            if ($in_out != null) {
                $in_out=str_replace("\n"," ",$in_out);
                $in_out=str_replace("\r"," ",$in_out);
            }
            if ($suplidor != null) {
                $suplidor=str_replace("\n"," ",$suplidor);
                $suplidor=str_replace("\r"," ",$suplidor);
            }
            
            // Normalement ce numéro de reservation existe dans le customer card
            $customerCard = $customerCardRepository->findOneBy(['reservationNumber' => $reservaId, 'holder' => $nombre]);
            $transportCompany = $transportCompanyRepository->findOneBy(['name' => $suplidor]);

            // on regarde si cet aéroport et cet hotel existe dans la liste sinon on l'ajoute !!!
            //Aéroport ou hotel
            $checkAirportHotelDesde = $airportHotelRepository->findOneBy(['name' => $desde]);
            $checkAirportHotelHasta = $airportHotelRepository->findOneBy(['name' => $hasta]);

            // si desde n 'existe pas on va regarder la nature et l insérer comme il faut
            if (!$checkAirportHotelDesde){
                // si c est arrivée alors c est un aéroport sinon c est un hotel 
                $isAirport = in_array($natureTransfer, $ecrituresDeLlegada);
                $newAirportHotel = new AirportHotel();
                $newAirportHotel->setName($desde);
                $newAirportHotel->setIsAirport($isAirport);
                $manager->persist($newAirportHotel);
                $manager->flush();
            }
            // si hasta n 'existe pas on va regarder la nature et l insérer comme il faut
            if (!$checkAirportHotelHasta){
                // si c est départ alors c est un aéroport sinon c est un hotel 
                $isAirport = in_array($natureTransfer, $ecrituresDeSalidas);
                $newAirportHotel = new AirportHotel();
                $newAirportHotel->setName($hasta);
                $newAirportHotel->setIsAirport($isAirport);
                $manager->persist($newAirportHotel);
                $manager->flush();
            }



            /*******************  TODO: ************************************ */
            
            
             
            if ($customerCard) {
                //dd($customerCard);
               
                $transfersExistent =  $natureTransferRepository->findBy(['customerCard'=> $customerCard, 'date'=> $dia_vuelo]);
                
                // si dans la bdd ce n est pas présent, il faut ajouter 
                // (si le transfert n existe pas ce jour il faut le signaler !! on ne créé pas un nouveau transfert !!!)
                if (!$transfersExistent) {  
                    // dd('Le transfert n existe pas');     
                    // l'arrivée (transferArrival doit etre uniquement créé par ivan ou son fichier)
                    // par conséquent si y a une fiche client mais pas d'arrivée ce jour, on ne peut pas l'importer
                    if (in_array($natureTransfer,$ecrituresDeLlegada)) {
                        $errorClients[] = 'You cannot create an arrival transfer if there is no arrival on this day. ' . ucfirst($nombre) . ', reservation number ' . $reservaId.'  has a client card but no arrival today. Create the associated arrival first or ask an administrator to do it before importing the transfer of this arrival.';
                        continue;
                    } else {
                        $newTransfer->setCustomerCard($customerCard);
                        $from = $airportHotelRepository->findOneBy(['name'=> $desde]);
                        $to = $airportHotelRepository->findOneBy(['name'=> $hasta]);
                        $newTransfer->setFromStart($from);
                        $newTransfer->setToArrival($to);
                        $newTransfer->setTransportCompany($transportCompany);
                        $newTransfer->setDate($dia_vuelo);
                        $newTransfer->setPickUp($pickup);
                        $newTransfer->setVehicleNumber($n_veh);
                        $newTransfer->setVehicleType($t_veh);
                        $newTransfer->setIsCollective($tipo_trf);
                        $newTransfer->setVoucherNumber($bono);
                        $newTransfer->setArea($zonas);
                        $newTransfer->setAdultsNumber($ad);
                        $newTransfer->setChildrenNumber($ni);
                        $newTransfer->setBabiesNumber($bb);                    
                        
                        if (in_array($natureTransfer,$ecrituresDeSalidas)) {
                            $newTransfer->setFlightNumber($vuelo);
                            $newTransfer->setHour($hour);
                        }
                        $manager->persist($newTransfer);      
                        $insertedLine++;
                    }                       
                }
            
                /************ !!!  A ce stade ca existe en bdd !!!  **************/
                
                else {
                   
                    // si dans le csv c est présent qu une fois et dans la bdd présent une fois juste MAJ
                    if ( ($countEachServiceNumbersInCSV[$reservaId] == 1) and (count($transfersExistent) == 1) ) {
                        
                        // si c est une arrivée 
                        if (in_array($natureTransfer,$ecrituresDeLlegada)) {
                           
                            // il y a un vehicule pour une arrivée et 1 SEUL PRESENT EN BDD 
                            // si le transfer vehicle existe MAJ sinon NEW
                            $transferVehicleArrivalexiste = $transfersExistent[0]->getTransferVehicleArrival();
                            $transferVehicleArrival = ($transferVehicleArrivalexiste == null) ? new TransferVehicleArrival() : $transferVehicleArrivalexiste;
                            $from = $airportHotelRepository->findOneBy(['name'=> $desde]);
                            $to = $airportHotelRepository->findOneBy(['name'=> $hasta]);
                            $transfersExistent[0]->setFromStart($from);
                            $transfersExistent[0]->setToArrival($to);
                            $transferVehicleArrival->setTransferArrival($transfersExistent[0]);
                            $transferVehicleArrival->setIsCollective($tipo_trf);
                            $transferVehicleArrival->setVehicleNumber($n_veh);
                            $transferVehicleArrival->setVehicleType($t_veh);
                            $transferVehicleArrival->setDate($dia_vuelo);
                            $transferVehicleArrival->setVoucherNumber($bono);
                            $transferVehicleArrival->setArea($zonas);
                            $transferVehicleArrival->setTransportCompany($transportCompany);
                            $transferVehicleArrival->setAdultsNumber($ad);
                            $transferVehicleArrival->setChildrenNumber($ni);
                            $transferVehicleArrival->setBabiesNumber($bb);
                                  
                            if($transferVehicleArrivalexiste == null){
                                $manager->persist($transferVehicleArrival);
                            }
                            $insertedLine++;
                        }

                        else {
                            // sinon c'est un inter hotel ou depart
                            $transfersExistent[0]->setCustomerCard($customerCard);
                            $from = $airportHotelRepository->findOneBy(['name'=> $desde]);
                            $to = $airportHotelRepository->findOneBy(['name'=> $hasta]);
                            $transfersExistent[0]->setFromStart($from);
                            $transfersExistent[0]->setToArrival($to);
                            $transfersExistent[0]->setTransportCompany($transportCompany);
                            $transfersExistent[0]->setDate($dia_vuelo);
                            $transfersExistent[0]->setPickUp($pickup);
                            $transfersExistent[0]->setVehicleNumber($n_veh);
                            $transfersExistent[0]->setVehicleType($t_veh);
                            $transfersExistent[0]->setIsCollective($tipo_trf);
                            $transfersExistent[0]->setVoucherNumber($bono);
                            $transfersExistent[0]->setArea($zonas);
                            $transfersExistent[0]->setAdultsNumber($ad);
                            $transfersExistent[0]->setChildrenNumber($ni);
                            $transfersExistent[0]->setBabiesNumber($bb);                    
                            
                            if (in_array($natureTransfer,$ecrituresDeSalidas)) {
                                $transfersExistent[0]->setFlightNumber($vuelo);
                                $transfersExistent[0]->setHour($hour);
                            }
                            $insertedLine++;  
                        }
                    }
                    else if (($countEachServiceNumbersInCSV[$reservaId] > 1) or (count($transfersExistent) > 1)) {  
                        // dd('c est present plusieurs fois');
                        // dd($customerCard);
                        // si c est une arrivée = del transferVehicle   
                        if (in_array($natureTransfer,$ecrituresDeLlegada)) {                            
                            // si dans le csv c'est présent plusieurs fois on supprime tous dans la bdd 
                            // dd('c est bien une arrivée !pour une carte cliente préente deux fois');
                            // rechercher l'arrivée par le flight number
                            // Liste des customercards avec ce numéro
                            $customerCards = $customerCardRepository->findBy(['reservationNumber' => $reservaId ]);
                            // on cherche le bon grace au numéro de vol et date
                            // seulement si y en a plusieurs
                            if (count($customerCards) >  1) {
                                foreach ($customerCards as $currentCustomerCard) {
                                    $checkCustommerArrival = $natureTransferRepository->findOneBy(['customerCard'=> $currentCustomerCard, 'date'=> $dia_vuelo, 'flightNumber' => $vuelo]);
                                    if ($checkCustommerArrival) {
                                        $transfersaMaj = $checkCustommerArrival;
                                        break;
                                    }
                                }
                            } else {
                                $transfersaMaj =  $natureTransferRepository->findOneBy(['customerCard'=> $customerCard, 'date'=> $dia_vuelo, 'flightNumber' => $vuelo]);
                            }
                            // si le transferMaj est reconnu (== a 1) on met a jour
                            if ($transfersaMaj) {
                                
                                // si transfer vehicleArrival existe on maj sinon crée
                                $newTransferVehicleArrival = ($transfersaMaj->getTransferVehicleArrival() == null) ? new TransferVehicleArrival(): $transfersaMaj->getTransferVehicleArrival();
                                $from = $airportHotelRepository->findOneBy(['name'=> $desde]);
                                $to = $airportHotelRepository->findOneBy(['name'=> $hasta]);
                                $transfersaMaj->setFromStart($from);
                                $transfersaMaj->setToArrival($to);

                                
                                if ($transfersaMaj->getTransferVehicleArrival() == null) {
                                    
                                    $newTransferVehicleArrival->setTransferArrival($transfersaMaj);
                                }
                                
                                $newTransferVehicleArrival->setIsCollective($tipo_trf);
                                $newTransferVehicleArrival->setVehicleNumber($n_veh);
                                $newTransferVehicleArrival->setVehicleType($t_veh);
                                $newTransferVehicleArrival->setDate($dia_vuelo);
                                $newTransferVehicleArrival->setVoucherNumber($bono);
                                $newTransferVehicleArrival->setArea($zonas);
                                $newTransferVehicleArrival->setTransportCompany($transportCompany);
                                $newTransferVehicleArrival->setAdultsNumber($ad);
                                $newTransferVehicleArrival->setChildrenNumber($ni);
                                $newTransferVehicleArrival->setBabiesNumber($bb);
                                
                                if ($transfersaMaj->getTransferVehicleArrival() == null) {
                                    $manager->persist($newTransferVehicleArrival);
                                }
                                
                                $insertedLine++;
                            }
                            // sinon on prévient avec une erreur
                            else{
                                $errorClients[] = 'The combination of flight number, date and customer card does not match for this day. ' . ucfirst($nombre) . ', reservation number: ' . $reservaId . ', flight number: ' . $vuelo   ;
                            }
                        }
                        
                        // sinon del transfer
                        else {
    
                            foreach ($transfersExistent as $transfer) {
                                $manager->remove($transfer);
                                $manager->flush();
                            }

                            // on recré le transfer courant, (les autres seront des nouveaux pour la boucle car 0 en bdd)
                            $newTransfer->setCustomerCard($customerCard);
                            $from = $airportHotelRepository->findOneBy(['name'=> $desde]);
                            $to = $airportHotelRepository->findOneBy(['name'=> $hasta]);
                            $newTransfer->setFromStart($from);
                            $newTransfer->setToArrival($to);
                            $newTransfer->setTransportCompany($transportCompany);
                            $newTransfer->setDate($dia_vuelo);
                            $newTransfer->setPickUp($pickup);
                            $newTransfer->setVehicleNumber($n_veh);
                            $newTransfer->setVehicleType($t_veh);
                            $newTransfer->setIsCollective($tipo_trf);
                            $newTransfer->setVoucherNumber($bono);
                            $newTransfer->setArea($zonas);
                            $newTransfer->setAdultsNumber($ad);
                            $newTransfer->setChildrenNumber($ni);
                            $newTransfer->setBabiesNumber($bb);                    
                            
                            if (in_array($natureTransfer,$ecrituresDeSalidas)) {
                                $newTransfer->setFlightNumber($vuelo);
                                $newTransfer->setHour($hour);
                            }
                            
                            $manager->persist($newTransfer);
                            $insertedLine++;
                        }
                    }


                }
                
            } 
            // on va prévenir l'utilisateur que ces lignes n'ont pas étéaient importées car il n'y pas de carte client associées
            else {
                $errorClients[] = 'The reservation number ' . $reservaId . ' and the fullname of the client ' . ucfirst($nombre) . ' are not present in the database';
            } 

        }

        $manager->flush();
        

        //****************************** Recherche des  Arrivéées de ce jour qui ne sont plus présentes dans le CSV ******************************//
        //****************************************************************************************************************************************//
            if ( (isset($dateFormat)) and ($dateFormat!= null) ) {
            
                if (in_array($natureTransfer,$ecrituresDeLlegada)) {
                    // construit un tableau pour chaque arrivée de ce jour
                    $clientNumberArrivalsInBdd = [];    
                    // recherche toute les arrivées de ce jour
                    $arrivalVehicles = $transferVehicleArrivalRepository->findBy(['date' => new DateTimeImmutable($dateFormat)]);
                    // les arrivées de ce jour dans un tableau avec 182232-cm802 (reservationNumber, flightNumber)
                    foreach ($arrivalVehicles as $arrivalVehicle) { 
                        $clientNumberArrivalsInBdd[] = $arrivalVehicle->getTransferArrival()->getCustomerCard()->getReservationNumber() .'-'.  strtolower($arrivalVehicle->getTransferArrival()->getFlightNumber());
                    }
                    // on regarde si il y a des diff ( des numéros qui étaient en bdd mais plus dans le csv)
                    $nonPresentsDansLeNouveauCSV = array_diff($clientNumberArrivalsInBdd, $reservationNumberFlightNumberIncsv);
                    
                    // sinon on peut supprimer
                    foreach ($nonPresentsDansLeNouveauCSV as $toDelete) {

                        $dataToDelete = explode('-',$toDelete);

                        $customerCard = $customerCardRepository->findOneBy(["reservationNumber" => $dataToDelete[0]]);
                        $flightNumber = $dataToDelete[1];   

                        // on re-récupère les arrivées  avec le numéro de vol qui correspond !!!
                        $arrivalsDayInBdd = $transferArrivalRepository->findBy(['customerCard' => $customerCard, 
                                                                                'date' => new DateTimeImmutable($dateFormat),
                                                                                'flightNumber'=> $flightNumber  ,                   
                                                                                ]);
                        // on supprimer les transferts vehicule arrivée
                        foreach ($arrivalsDayInBdd as $arrival) {   
                            $transferVehicleArrivalRepository->remove($arrival->getTransferVehicleArrival(), true);
                        }
                    
                    
                    }
                }
                else if (in_array($natureTransfer,$ecrituresDeInterHotel)) {
                    $clientNumberTransfersInBdd = []; 
                    $interHotels = $transferInterHotelRepository->findBy(['date' => new DateTimeImmutable($dateFormat)]);
                    foreach ($interHotels as $interHotel) { 
                        $clientNumberTransfersInBdd[] = $interHotel->getCustomerCard()->getReservationNumber() .'-inthtl';
                    }
                    $nonPresentsDansLeNouveauCSV = array_diff($clientNumberTransfersInBdd, $reservationNumberFlightNumberIncsv);
                    // sinon on peut supprimer
                    foreach ($nonPresentsDansLeNouveauCSV as $toDelete) {

                        $dataToDelete = explode('-',$toDelete);

                        $customerCard = $customerCardRepository->findOneBy(["reservationNumber" => $dataToDelete[0]]);
 

                        // arrivée 
                        $interHotelsDayInBdd = $transferInterHotelRepository->findBy(['customerCard' => $customerCard, 
                                                                                'date' => new DateTimeImmutable($dateFormat),                
                                                                                ]);
                        foreach ($interHotelsDayInBdd as $interHotel) {   
                            $transferInterHotelRepository->remove($interHotel, true);
                        }
                    }
                }
                // sinon c est depart
                else {
                    $clientNumberTransfersInBdd = []; 
                    $departures = $transferDepartureRepository->findBy(['date' => new DateTimeImmutable($dateFormat)]);
                    foreach ($departures as $departure) { 
                        $clientNumberTransfersInBdd[] = $departure->getCustomerCard()->getReservationNumber() .'-'.  strtolower($departure->getFlightNumber());
                    }
                    $nonPresentsDansLeNouveauCSV = array_diff($clientNumberTransfersInBdd, $reservationNumberFlightNumberIncsv);
                    // sinon on peut supprimer
                    foreach ($nonPresentsDansLeNouveauCSV as $toDelete) {

                        $dataToDelete = explode('-',$toDelete);
                        $customerCard = $customerCardRepository->findOneBy(["reservationNumber" => $dataToDelete[0]]);
                        $flightNumber = $dataToDelete[1];   
 
                        // arrivée 
                        $departureDayInBdd = $transferDepartureRepository->findBy(['customerCard' => $customerCard, 
                                                                                    'date' => new DateTimeImmutable($dateFormat),
                                                                                    'flightNumber'=> $flightNumber  ,                   
                                                                                ]);
                        foreach ($departureDayInBdd as $departure) {   
                            $transferDepartureRepository->remove($departure, true);
                        }
                    }
                }                
            } 
        //***************************************************************** fin  *****************************************************************//
        //****************************************************************************************************************************************//

        if (count($errorClients) > 5) {
            $newerrorClientsTab = [];
            for ($i=0; $i<5; $i++) {
                $newerrorClientsTab[] = $errorClients[$i];
            }
            $errorClients = $newerrorClientsTab;
        }
        return $this->redirectToRoute('app_transfer_import', [
            'numberOfRows' => $numberOfRows,
            'insertedLine' => $insertedLine,
            'errorClients' => $errorClients,
        ]);
/*         return $this->redirectToRoute('app_transfer_import', [
            'errorClients' => $errorClients
        ]); */
    }
    

    #[Route('/new', name: 'app_transfer_joan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TransferJoanRepository $transferJoanRepository): Response
    {
        $transferJoan = new TransferJoan();
        $form = $this->createForm(TransferJoanType::class, $transferJoan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transferJoanRepository->save($transferJoan, true);

            return $this->redirectToRoute('app_transfer_joan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transfer_joan/new.html.twig', [
            'transfer_joan' => $transferJoan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transfer_joan_show', methods: ['GET'])]
    public function show(TransferJoan $transferJoan): Response
    {
        return $this->render('transfer_joan/show.html.twig', [
            'transfer_joan' => $transferJoan,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transfer_joan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TransferJoan $transferJoan, TransferJoanRepository $transferJoanRepository): Response
    {
        $form = $this->createForm(TransferJoanType::class, $transferJoan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transferJoanRepository->save($transferJoan, true);

            return $this->redirectToRoute('app_transfer_joan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transfer_joan/edit.html.twig', [
            'transfer_joan' => $transferJoan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transfer_joan_delete', methods: ['POST'])]
    public function delete(Request $request, TransferJoan $transferJoan, TransferJoanRepository $transferJoanRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transferJoan->getId(), $request->request->get('_token'))) {
            $transferJoanRepository->remove($transferJoan, true);
        }

        return $this->redirectToRoute('app_transfer_joan_index', [], Response::HTTP_SEE_OTHER);
    }
}
