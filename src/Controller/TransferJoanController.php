<?php

namespace App\Controller;

use App\Entity\DragAndDrop;
use App\Entity\TransferArrival;
use App\Entity\TransferDeparture;
use App\Entity\TransferInterHotel;
use App\Entity\TransferJoan;
use App\Entity\TransferVehicleArrival;
use App\Entity\TransferVehicleDeparture;
use App\Entity\TransferVehicleInterHotel;
use App\Form\DragAndDropType;
use App\Form\TransferJoanType;
use App\Repository\CustomerCardRepository;
use App\Repository\TransferArrivalRepository;
use App\Repository\TransferDepartureRepository;
use App\Repository\TransferInterHotelRepository;
use App\Repository\TransferJoanRepository;
use App\Repository\TransferVehicleArrivalRepository;
use App\Repository\TransferVehicleDepartureRepository;
use App\Repository\TransferVehicleInterHotelRepository;
use App\Services\ErrorsImportManager;
use DateTime;
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
                          TransferVehicleArrivalRepository $transferVehicleArrivalRepository,
                          TransferVehicleInterHotelRepository $transferVehicleInterHotelRepository,
                          TransferVehicleDepartureRepository $transferVehicleDepartureRepository,
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
            $natureTransferRepository = $transferVehicleArrivalRepository;
            $natureTransferObject = new TransferVehicleArrival();
        }
        else if ($natureTransfer == 'interhotel') { 
            $natureTransferRepository = $transferVehicleInterHotelRepository;
            $natureTransferObject = new TransferVehicleInterHotel();
        
        }
        else if ($natureTransfer == 'salidas') { 
            $natureTransferRepository = $transferVehicleDepartureRepository;
            $natureTransferObject = new TransferVehicleDeparture();
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
                $agencia = $row[1]; 
                $nombre = $row[2]; 
                $vuelo = $row[3]; 
                $dia_vuelo = $row[4]; 
                $hora_v = $row[5]; 
                $desde = $row[6]; 
                $hasta = $row[7]; 
                $tipo_trf = $row[8]; 
                $tipo_trf = ($tipo_trf == "Colectivo") ? true : false ; 
                $ad = $row[9]; 
                $ni = $row[10]; 
                $bb = $row[11]; 
                $in_out = $row[12]; 
                $n_veh = $row[13]; 
                $t_veh = $row[14]; 
                $pickup = $row[15]; 
                $suplidor = $row[16]; 
                $bono = $row[17]; 
                $zonas = $row[18];
               

                $date= explode("/", $dia_vuelo);
                $dateFormat = $date[2] . '-' . $date[1] .'-'. $date[0];
                $dia_vuelo = new DateTimeImmutable($dateFormat);
            }
            if ($agencia != null) {
                $agencia=str_replace("\n"," ",$agencia);
                $agencia=str_replace("\r"," ",$agencia);
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
            
            // Normalement ce numéro de reservation existe dans le customer card
            $customerCard = $customerCardRepository->findOneBy(['reservationNumber' => $reservaId]);
            //dd($customerCard);

            if ($customerCard) {
                // on regarde si le nature transfert existe 
                $natureTransferExiste = $natureTransferRepository->findOneBy(['customerCard' => $customerCard]); 
                if ($natureTransfer == 'llegadas') { 
                    $natureTransferObject = new TransferVehicleArrival();
                } else if ($natureTransfer == 'interhotel') { 
                    $natureTransferObject = new TransferVehicleInterHotel();
                } else if ($natureTransfer == 'salidas') { 
                    $natureTransferObject = new TransferVehicleDeparture();
                }
                
                if ($natureTransferExiste) {
                    // on met a jour le natureTransfer
                    // sinon il faut créer un nouvel objet natureTransfer on utilise $natureTransferObject
                    $natureTransferExiste->setCustomerCard($customerCard);
                    $natureTransferExiste->setIsCollective($tipo_trf);
                    $natureTransferExiste->setVehicleNumber($n_veh);
                    $natureTransferExiste->setVehicleType($t_veh);
                    $natureTransferExiste->setDate($dia_vuelo);
                    
                    ($natureTransfer == 'llegadas') ? $natureTransferExiste->setPickUp($hora_v) : $natureTransferExiste->setPickUp($pickup);
                    
                    $natureTransferExiste->setTransportCompany($suplidor);
                    $natureTransferExiste->setVoucherNumber($bono);
                    $natureTransferExiste->setArea($zonas);

                    //dd('ce nature transfert existe déja');
                } else {                  
                    // sinon il faut créer un nouvel objet natureTransfer on utilise $natureTransferObject
                    $natureTransferObject->setCustomerCard($customerCard);
                    $natureTransferObject->setIsCollective($tipo_trf);
                    $natureTransferObject->setVehicleNumber($n_veh);
                    $natureTransferObject->setVehicleType($t_veh);
                    $natureTransferObject->setDate($dia_vuelo);
                    ($natureTransfer == 'llegadas') ? $natureTransferObject->setPickUp($hora_v) : $natureTransferObject->setPickUp($pickup);
                    $natureTransferObject->setTransportCompany($suplidor);
                    $natureTransferObject->setVoucherNumber($bono);
                    $natureTransferObject->setArea($zonas);

                    $manager->persist($natureTransferObject);
                }
            } 
            // on va prévenir l'utilisateur que ces lignes n'ont pas étéaient importées car il n'y pas de carte client associées
            else {
                $errorClients[] = $reservaId;
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
