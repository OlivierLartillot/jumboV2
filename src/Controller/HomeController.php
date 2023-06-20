<?php

namespace App\Controller;

use App\Entity\CustomerCard;
use App\Entity\DragAndDrop;
use App\Entity\Transfer;
use App\Form\DragAndDropType;
use App\Repository\CustomerCardRepository;
use App\Repository\MeetingPointRepository;
use App\Repository\StatusRepository;
use App\Repository\TransferRepository;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use League\Csv\Reader;
use Symfony\Component\Validator\Constraints\Date;

class HomeController extends AbstractController
{

    #[Route('/', name: 'home' )]
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
                                    TransferRepository $transferRepository,
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
           dd('stop erreur token');
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
    

                // les entrées possédant ce numéro doivent être ignorée
                if (($record['Nº Vuelo/Transporte Origen'] == "XX9999") or 
                ($record['Nº Vuelo/Transporte Destino'] == "XX9999") or 
                ($record['Fecha/Hora recogida'] == "XX9999")){
                    continue;
                }
                //dd($csv);

                //! extraction de jumboNumber et reservationNumber car ils se trouvent dans la meme case dans le csv 
                $numbers = explode(", ", $record['Localizadores']);
                $jumboNumber = $numbers[0];
                $reservationNumber = $numbers[1];
                
                //! extraction du nombre d'adultes/enfants/bébés car dans la même case dans le csv
                $numeroPasajeros = explode(" ", $record['Número pasajeros']);
                $adultsNumber = $numeroPasajeros[1];
                $childrenNumber = $numeroPasajeros[3];
                $babiesNumber = $numeroPasajeros[5];

                // on essaie de récupérer la fiche pour savoir si on va create or update
                $customerCardResult = $customerCardRepository->findOneBy(['reservationNumber' => $reservationNumber]);
                // si l'enregistrement existe déja, on va le mettre a jour
                if ($customerCardResult) {
                    $customerCard = $customerCardResult;
                    // faut il mettre a jour la date du meeting en cas de changement de la date d arrivée
                    // on peut comparer si la date est la meme que celle de l'objet
                    // on vérifie que la date n'ai pas changée
                    //définir si c est une arrivée car les meetings se font a l arrivée
                    $mettreAjour = false;
                    if ($record['Nº Vuelo/Transporte Origen'] != NULL) {
                        $fechaHora = $record['Fecha/Hora Origen'];
                        
                        foreach ($customerCard->getTransfers() as $transfer) {
                            // si le transfer est arrivée ! il faut regarder si les dates correpondent (fichier et bdd)
             
                            if ( ($transfer->getNatureTransfer() == "Arrivée") and ($fechaHora != $transfer->getDateHour()->format('m/d/Y'. ' H:i')) ){ 
                                // mettre ajour le briefing
                                if ($record['Fecha/Hora Origen']) {
                                    $dateTime = explode(" ", $record['Fecha/Hora Origen']);
                                    $date = new DateTime($dateTime[0]);
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
                    
                    if ($record['Fecha/Hora Origen']) {
                        $dateTime = explode(" ", $record['Fecha/Hora Origen']);
                        $date = new DateTime($dateTime[0]);
                        $hour = '00:01';
                        $meetingAt = new DateTimeImmutable($date->format('Y-d-m') . $hour);
                        $customerCard->setMeetingAt($meetingAt);
                    }
                }

                // enregistrement des données dans la card courante
                    $customerCard->setReservationNumber($reservationNumber);
                    $customerCard->setJumboNumber($jumboNumber);
                    $customerCard->setHolder($record['Titular']);
                    $customerCard->setAgency($record['Agencia']);
                    $customerCard->setAdultsNumber($adultsNumber);
                    $customerCard->setChildrenNumber($childrenNumber);
                    $customerCard->setBabiesNumber($babiesNumber);
                    $customerCard->setStatus($status);
                    $customerCard->setStatusUpdatedAt(new DateTimeImmutable("now"));
                    $customerCard->setStatusUpdatedBy($user);
                    // meetind At, le lendemain de l'arrivée
                   
                    $customerCard->setReservationCancelled(0);
                    
                    // si la carte n existe pas deja on fait le persist sinon doctrine s'en occupe
                    if (!$customerCardResult) {
                        $manager->persist($customerCard);
                    }
                    

                //! traitement des infos de la table transfer

                //définir si c est une arrivée/depart/interHotel
                if ($record['Nº Vuelo/Transporte Origen'] != NULL) {
                    $fechaHora = $record['Fecha/Hora Origen'];
                    $natureTransfer = "Arrivée";
                    $flightNumber = $record['Nº Vuelo/Transporte Origen'];
                    $dateTime = explode(" ", $record['Fecha/Hora Origen']);
                } else if ($record['Nº Vuelo/Transporte Destino'] != NULL) {
                    $fechaHora = $record['Fecha/Hora Destino'];
                    $natureTransfer = "Départ";
                    $flightNumber = $record['Nº Vuelo/Transporte Destino'];
                    $dateTime = explode(" ", $record['Fecha/Hora Destino']);
                } else {
                    $fechaHora = $record['Fecha/Hora recogida'];
                    $natureTransfer = "Inter Hotel";
                    $flightNumber = NULL;
                    $dateTime = explode(" ", $record['Fecha/Hora recogida']);
                }


                $date = new DateTime($dateTime[0]);
                $dateTime = $date->format('Y-d-m ' .$dateTime[1]);
                $fechaHora = new DateTimeImmutable($dateTime);
                

                // on essaie de récupérer la fiche pour savoir si on va create or update
                $transferResult = $transferRepository->findOneBy(['customerCard' => $customerCard]);
                // $transfer = $this->transferRepository
                // si l'enregistrement existe déja, on va le mettre a jour
                if ($transferResult) {
                    $transfer = $transferResult;
                } 
                else // sinon on va créer un nouvel objet
                {
                    $transfer = new Transfer();
                }
                
                $transfer->setServiceNumber($record['Número Servicio']);
                $transfer->setNatureTransfer($natureTransfer);
                $transfer->setDateHour($fechaHora);
                $transfer->setFlightNumber($flightNumber);
                $transfer->setFromStart($record['Traslado desde']);
                $transfer->setToArrival($record['Traslado hasta']);
                $transfer->setPrivateCollective($record['Tipo traslado']);
                $transfer->setAdultsNumber($adultsNumber);  
                $transfer->setChildrenNumber($childrenNumber);
                $transfer->setBabiesNumber($babiesNumber);
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
