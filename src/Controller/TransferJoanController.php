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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Route('/transfer')]
class TransferJoanController extends AbstractController
{


    #[Route('/import', name: 'app_transfer_import', methods: ['GET'])]
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
                          TransferVehicleDepartureRepository $transferVehicleDepartureRepository
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
        if ($mimeType != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
            die("l extension du fichier n est pas bonne !");
        }


/*         $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fileToUpload);

        dd($spreadsheet); */

        // Charger le fichier Excel
        $spreadsheet = IOFactory::load($fileToUpload);
        //$spreadsheet->setActiveSheetIndexByName('OPERATIVA');
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        // Supprimez la première ligne si elle contient les en-têtes de colonne
        //array_shift($rows);

        

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
            die('La nature du transfer n est pas reconnue !');
        }
 
        // recuperer toutes les entrees qui ont la date du nouvel envoi
        // si c est > a 0 alors il faut les supprimer une a une $row[8] = date !!!

        // il va falloir récupérer la premiere date, on recherche ici le numéro de la ligne
        $d = 0;
        while ( ($rows[$d][4] == NULL) OR ($rows[$d][4]=='Dia Vuelo') ){
            $d++;
        }


        
        $i = 0;
        foreach ($rows as $row) {
            
            if ( ($i<9) AND ($row[0] == NULL OR $row[1] == NULL OR $row[2] == NULL) OR ($row[0] == 'N. Reserva' OR $row[1] == "Agencia") ) {
                $i++;
                continue;
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
                
                if ($natureTransferExiste) {
                    // on met a jour le natureTransfer
                        dd('on est ici dans le met a jour le nature transfer');

                    //dd('ce nature transfert existe déja');
                } else {
                    if ($natureTransfer == 'llegadas') { 
                        $natureTransferObject = new TransferVehicleArrival();
                    } else if ($natureTransfer == 'interhotel') { 
                        $natureTransferObject = new TransferVehicleInterHotel();
                    } else if ($natureTransfer == 'salidas') { 
                        $natureTransferObject = new TransferVehicleDeparture();
                    }

                  
                    // sinon il faut créer un nouvel objet natureTransfer on utilise $natureTransferObject
                    $natureTransferObject->setCustomerCard($customerCard);
                    $natureTransferObject->setIsCollective($tipo_trf);
                    $natureTransferObject->setVehicleNumber($n_veh);
                    $natureTransferObject->setVehicleType($t_veh);
                    $natureTransferObject->setPickUp($pickup);
                    $natureTransferObject->setTransportCompany($suplidor);
                    $natureTransferObject->setVoucherNumber($bono);
                    $natureTransferObject->setArea($zonas);

                    $manager->persist($natureTransferObject);
                    //dd('ce nature transfert n existe PAS');
                    
                }
            } 
            // customer card "bateau" ou rendre nullable customer card et insérer le numéro de reserva dans une nouvelle colonne
            else {

            }
            
            
            // si les deux premieres cellules de la ligne sont vides 
            // on considère qu'il n'y a plus de données à récupérer
            // et on sort de la boucle
            if ( ($row[0] == null) and ($row[1] == null) ) {
                
                break;
            }
        }
        
        $manager->flush();
        
        
        







        
        return $this->redirectToRoute('app_transfer_import');
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

        return $this->renderForm('transfer_joan/new.html.twig', [
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
