<?php

namespace App\Controller;

use App\Entity\DragAndDrop;
use App\Entity\TransferArrival;
use App\Entity\TransferDeparture;
use App\Entity\TransferInterHotel;
use App\Entity\TransferJoan;
use App\Entity\TransferVehicleArrival;
use App\Entity\TransferVehicleDeparture;
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
    public function import(TransferJoanRepository $transferJoanRepository): Response
    {

        return $this->render('transfer/import.html.twig', [
            'transfer_joans' => $transferJoanRepository->findAll(),
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
                          AirportHotelRepository $AirportHotelRepository,
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
        
        if ($natureTransfer == 'llegadas') { 
            $natureTransferRepository = $transferArrivalRepository;
            $natureTransferVehicleObject = new TransferVehicleArrival();
        }
        else if ($natureTransfer == 'interhotel') { 
            $natureTransferRepository = $transferInterHotelRepository;
        }
        else if ($natureTransfer == 'salidas') { 
            $natureTransferRepository = $transferDepartureRepository;
        } else {
            $errorsImportManager->addErrors('Code import 30 - In cell B3 should be written: LLEGADAS, INTERHOTEL or SAlIDAS');
            return $this->render("bundles/TwigBundle/Exception/error-import.html.twig", ['errorDetails' => $errorDetails]);
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
                // check si le in/out est == a B3
                if ($natureTransfer == 'llegadas') {
                    if (strtolower($row[12]) != 'llegada') {
                        $natureTransferError = true;
                    }
                } 
                else if ($natureTransfer == 'interhotel') {
                    if (strtolower($row[12]) != 'interhotel') {
                        $natureTransferError = true;
                    }
                }
                else {
                    if (strtolower($row[12]) != 'salida') {
                        $natureTransferError = true;
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

            }
        }


        $i = 0;
        $errorClients = [];
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
    
            
            
            /*******************  TODO: ************************************ */

            if ($customerCard) {

                // transferArrival ou transferIH ou transferDeparture
                // $natureTransferVehicleObjectExiste ?
                if ($natureTransfer == 'llegadas') {
                    $transferArrivalExiste =  $natureTransferRepository->findOneBy(['customerCard'=> $customerCard]);
                    // si le transfertArrival n existe pas je ne peux pas mettre a jour les données de vehicleArrival
                    if (!$transferArrivalExiste) {
                        $errorClients[] = 'La fiche client existe bien mais il n\'y a pas d\'arrivée/interHOtel ou Depart pour cette fiche client !';
                    } 
                     // si le transfertArrival existe, je peux mettre a jour les données de vehicleArrival
                    else {
                        $natureTransferVehicleObjectExiste =  $transferArrivalExiste->getTransferVehicleArrival();
                        // si 1 vehicleArrival existe ou pas 
                        $transferVehicleArrival  = ($natureTransferVehicleObjectExiste) ? $natureTransferVehicleObjectExiste : new TransferVehicleArrival;
                        
                        $transferVehicleArrival->setTransferArrival($transferArrivalExiste);
                        
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

                        if (!$natureTransferVehicleObjectExiste) { 
                            $manager->persist($transferVehicleArrival);
                        }

                    }
                } 
                else if ($natureTransfer == 'interhotel')  {

                    $transferInterHotelExiste =  $natureTransferRepository->findOneBy(['customerCard'=> $customerCard]);
            
                    // il faut créer le transferInterHotel
                    $transferInterHotel = (!$transferInterHotelExiste) ? new TransferInterHotel :  $transferInterHotelExiste ;
                    
                    $transferInterHotel->setCustomerCard($customerCard);
                    
                    $from = $AirportHotelRepository->findOneBy(['name'=> $desde]);
                    $to = $AirportHotelRepository->findOneBy(['name'=> $hasta]);
                    $transferInterHotel->setFromStart($from);
                    $transferInterHotel->setToArrival($to);
                    $transferInterHotel->setTransportCompany($transportCompany);
                    $transferInterHotel->setDate($dia_vuelo);
                    $transferInterHotel->setPickUp($pickup);
                    $transferInterHotel->setVehicleNumber($n_veh);
                    $transferInterHotel->setVehicleType($t_veh);
                    $transferInterHotel->setIsCollective($tipo_trf);
                    $transferInterHotel->setVoucherNumber($bono);
                    $transferInterHotel->setArea($zonas);
                    $transferInterHotel->setAdultsNumber($ad);
                    $transferInterHotel->setChildrenNumber($ni);
                    $transferInterHotel->setBabiesNumber($bb);                    
                    
                    if (!$transferInterHotelExiste) { 
                        $manager->persist($transferInterHotel);
                    }

                    } 
                // transfer departure
                else {
                    $transferDepartureExiste =  $natureTransferRepository->findOneBy(['customerCard'=> $customerCard]);
                    // il faut créer le transferInterHotel
                    if (!$transferDepartureExiste) { 
                        $transferDeparture = new TransferDeparture;
                    } else {
                        $transferDeparture = $transferDepartureExiste;
                    }

                    $transferDeparture->setCustomerCard($customerCard);
                    $from = $AirportHotelRepository->findOneBy(['name'=> $desde]);
                    $to = $AirportHotelRepository->findOneBy(['name'=> $hasta]);
                    $transferDeparture->setFromStart($from);
                    $transferDeparture->setToArrival($to);
                    $transferDeparture->setTransportCompany($transportCompany);
                    $transferDeparture->setFlightNumber($vuelo);
                    $transferDeparture->setDate($dia_vuelo);
                    $transferDeparture->setHour($hour);
                    $transferDeparture->setPickUp($pickup);
                    $transferDeparture->setVehicleNumber($n_veh);
                    $transferDeparture->setVehicleType($t_veh);
                    $transferDeparture->setIsCollective($tipo_trf);
                    $transferDeparture->setVoucherNumber($bono);
                    $transferDeparture->setArea($zonas);
                    $transferDeparture->setAdultsNumber($ad);
                    $transferDeparture->setChildrenNumber($ni);
                    $transferDeparture->setBabiesNumber($bb);

                    if (!$transferDepartureExiste) { 
                        $manager->persist($transferDeparture);
                    }
                    
            }                
                // on met a jour le natureTransfer
                // sinon il faut créer un nouvel objet natureTransfer on utilise $natureTransferObject

/*                 $natureTransferVehicleObject->setDate($dia_vuelo);
                ($natureTransfer == 'llegadas') ? $natureTransferVehicleObject->setPickUp($hora_v) : $natureTransferVehicleObject->setPickUp($pickup);
                $natureTransferVehicleObject->setVoucherNumber($bono);
                $natureTransferVehicleObject->setArea($zonas);

                
                
               
                
                $natureTransferVehicleObject->setTransportCompany($transportCompany);

                if (!$natureTransferVehicleObjectExiste) {
                    $manager->persist($natureTransferVehicleObject);
                }  */

            } 
            // on va prévenir l'utilisateur que ces lignes n'ont pas étéaient importées car il n'y pas de carte client associées
            else {
                $errorClients[] = 'The reservation number ' . $reservaId . ' and the fullname of the client ' . ucfirst($nombre) . ' are not present in the database';
            }
        }
        
        $manager->flush();
        
        return $this->render('transfer/import.html.twig', [
            'errorClients' => $errorClients
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

        return $this->renderForm('transfer_joan/edit.html.twig', [
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
