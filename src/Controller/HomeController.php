<?php

namespace App\Controller;

use App\Entity\CustomerCard;
use App\Entity\Transfer;
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
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use League\Csv\Reader;
use League\Csv\Statement;

class HomeController extends AbstractController
{
/*     public function __construct(Environment $twig)
    {
        $this->loader = $twig->getLoader();
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    } */

/*     #[Route('/{path}')]
    public function root($path)
    {
        if ($this->loader->exists($path.'.html.twig')) {
            if ($path == '/' || $path == 'home') {
                die('Home');
            }
            return $this->render($path.'.html.twig');
        }
        throw $this->createNotFoundException('The Requested Page Not Found.');
    } */

    #[Route('/', name: 'home' )]
    public function test(UserRepository $userRepository)
    {
        return $this->render('index.html.twig', [
        ]);

    }


    #[Route('/traitement_csv', name: 'admin_traitement_csv')]
    public function traitement_csv(HttpFoundationRequest $request, EntityManagerInterface $manager, 
                                    StatusRepository $statusRepository, 
                                    MeetingPointRepository $meetingPointRepository, 
                                    UserRepository $userRepository,
                                    CustomerCardRepository $customerCardRepository,
                                    TransferRepository $transferRepository): Response
    {

        // test des données recues
            

            // infos sur le csv
            if ($_FILES["fileToUpload"]["error"] > 0) {
                die("Erreur lors de l'upload du fichier. Code d'erreur : " . $_FILES["fileToUpload"]["error"]);
            }
            
            // Vérifier si le fichier a été correctement téléchargé
            if (!file_exists($_FILES["fileToUpload"]["tmp_name"])) {
                die("Fichier non trouvé.");
            }
            
            // Vérifier le type de fichier
            if ($_FILES["fileToUpload"]['type'] != "text/csv") {
                die("l extension du fichier n est pas bonne !");
            }

            // récupération du token
            $submittedToken = $request->request->get('token');
            
            
            // 'add-csv' si le token est valide, on peut commencer les traitements !
            if ($this->isCsrfTokenValid('add-csv', $submittedToken)) {
            // a faire dans le traitement
            //load the CSV document from a stream
            /*  $stream = fopen('csv/servicios.csv', 'r'); */
            $csv = Reader::createFromStream(fopen($_FILES["fileToUpload"]["tmp_name"], 'r+'));
            //$csv = Reader::createFromPath($_FILES["fileToUpload"]["tmp_name"], 'r');
            $csv->setDelimiter('|');
            $csv->setHeaderOffset(0);

            //dump($csv->getHeader());
            //build a statement
            $stmt = Statement::create() /* ->offset(10)->limit(20) */ ;

            //query your records from the document
            $records = $stmt->process($csv);
            
            // les entités par défaut
            $status = $statusRepository->find(1);
            $user = $userRepository->find(1);
            $meetingPoint = $meetingPointRepository->find(1);
            
            // début de l'extraction des données du csv
            foreach ($records as $record) {
                // les entrées possédant ce numéro doivent être ignorée
                if (($record['Nº Vuelo/Transporte Origen'] == "XX9999") or 
                    ($record['Nº Vuelo/Transporte Destino'] == "XX9999") or 
                    ($record['Fecha/Hora recogida'] == "XX9999")){
                    continue;
                }

                // extraction de jumboNumber et reservationNumber car ils se trouvent dans la meme case dans le csv 
                $numbers = explode(", ", $record['Localizadores']);
                $jumboNumber = $numbers[0];
                $reservationNumber = $numbers[1];
                
                // extraction du nombre d'adultes/enfants/bébés car dans la même case dans le csv
                $numeroPasajeros = explode(" ", $record['Número pasajeros']);
                $adultsNumber = $numeroPasajeros[1];
                $childrenNumber = $numeroPasajeros[3];
                $babiesNumber = $numeroPasajeros[5];

                // on essaie de récupérer la fiche pour savoir si on va create or update
                $customerCardResult = $customerCardRepository->findOneBy(['reservationNumber' => $reservationNumber]);
                // si l'enregistrement existe déja, on va le mettre a jour
                if ($customerCardResult) {
                    $customerCard = $customerCardResult;
                } 
                else // sinon on va créer un nouvel objet
                {
                    $customerCard = new CustomerCard();
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
                    $customerCard->setMeetingPoint($meetingPoint);
                    // meetind At, le lendemain de l'arrivée
                    if ($record['Fecha/Hora Origen']) {
                        $dateTime = explode(" ", $record['Fecha/Hora Origen']);
                        $date = new DateTime($dateTime[0]);
                        $hour = '00:01';
                        $meetingAt = new DateTimeImmutable($date->format('Y-d-m') . $hour);
                        $customerCard->setMeetingAt($meetingAt);
                    }
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
                } else if ($record['Nº Vuelo/Transporte Destino'] != NULL) {
                    $fechaHora = $record['Fecha/Hora Destino'];
                    $natureTransfer = "Départ";
                    $flightNumber = $record['Nº Vuelo/Transporte Destino'];
                } else {
                    $fechaHora = $record['Fecha/Hora recogida'];
                    $natureTransfer = "Inter Hotel";
                    $flightNumber = NULL;
                }

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
                $transfer->setDateHour(new DateTimeImmutable($fechaHora));
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
            


            return $this->redirectToRoute('home');
        }
        
        else {
            dd('erreur de token');
        }
    }





}
