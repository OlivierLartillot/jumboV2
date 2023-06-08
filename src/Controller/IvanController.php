<?php

namespace App\Controller;

use App\Entity\MeetingPoint;
use App\Entity\User;
use App\Form\RepAttributionType;
use App\Repository\CustomerCardRepository;
use App\Repository\MeetingPointRepository;
use App\Repository\UserRepository;
use App\Services\DefineQueryDate;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IvanController extends AbstractController
{


    // route qui affiche tous les rep a attribuer en fonction de la date
    #[Route('/ivan', name: 'app_admin_ivan',methods:["POST", "GET"])]
    public function index(CustomerCardRepository $customerCardRepository, Request $request, DefineQueryDate $defineQueryDate): Response
    {

        // utilisation du service qui définit si on utilise la query ou la session
        $day =  $defineQueryDate->returnDay($request);


        $date = new DateTimeImmutable($day . '00:01:00');
        // Récupérer tous les customerCards qui n'ont pas de staff id et qui colle avec la date
        $firstClient = $customerCardRepository->findOneBy(
            [
                'staff' => NULL,
                'meetingAt' => $date
            ]);
        $countNonAttributedClients = count($customerCardRepository->findBy(
            [
                'staff' => NULL,
                'meetingAt' => $date
            ]
        
        ));


        if ($firstClient != NULL) {

            $form = $this->createForm(RepAttributionType::class, $firstClient);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                
                $customerCardRepository->save($firstClient, true);                
                return $this->redirect($this->generateUrl('app_admin_ivan'));

            }
            
            return $this->render('ivan/attributionRepresentants.html.twig', [
                'firstClient' => $firstClient,
                'form' => $form,
                'controller_name' => 'IvanController',
                'countNonAttributedClients' => $countNonAttributedClients,
                'date' => $date
            ]);
        }  

        else {
            return $this->render('ivan/attributionRepresentants.html.twig', [
                'notClient' => true,
                'controller_name' => 'IvanController',
                'date' => $date
            ]); 
        } 


    }

    // route qui affiche la liste des rep - client en fonction de la date
    // la liste doit comporter le nombre de client par rep 
    #[Route('/ivan/replist', name: 'app_admin_ivan_replist',methods:["POST", "GET"])]
    public function repList(CustomerCardRepository $customerCardRepository, UserRepository $userRepository, Request $request,DefineQueryDate $defineQueryDate): Response 
    {
        // utilisation du service qui définit si on utilise la query ou la session
        $day =  $defineQueryDate->returnDay($request);

        // on fixe la date que l'on va utiliser dans le filtre
        $date = new DateTimeImmutable($day . '00:01:00');

        // récupération de tous les utilisateurs du site (pas nombreux a ne pas etre rep donc on checkera apres)
        $repUsers = $userRepository->findAll();


        // nombre de clients sans attributions
        $countNonAssignedClient = $customerCardRepository->countNumberNonAttributedMeetingsByDate($date);

        //initialisation du tableau des résultats
        $clientsListByRepAndDate = [];

        // pour chaque rep, recupere la liste des gens attribués ce jour la par rep et date
        foreach($repUsers as $user) {
            if(in_array('ROLE_REP', $user->getRoles())){ 
                $attributionClientsByRepAndDate = $customerCardRepository->findByStaffAndMeetingDate($user, $date);
                $clientsListByRepAndDate[$user->getUsername()] = $attributionClientsByRepAndDate;
            }
        }

        dump($clientsListByRepAndDate);

        return $this->render('ivan/repList.html.twig', [
            'date' => $date,
            'clientsListByRepAndDate' => $clientsListByRepAndDate,
            'countNonAssignedClient' => $countNonAssignedClient
        ]);

    } 

    // route qui affiche la fiche d un rep et ses assignations de clients pou un jour donné
    // la fiche doit permettre de changer la date du mmeting comme de rep
    #[Route('/ivan/fiche/{user}/date', name: 'app_admin_ivan_fiche_par_date',methods:["POST", "GET"])]
    public function ficheRepParDate(User $user, CustomerCardRepository $customerCardRepository, MeetingPointRepository $meetingPointRepository,  EntityManagerInterface $manager, Request $request,DefineQueryDate $defineQueryDate): Response 
    {

        $day =  $defineQueryDate->returnDay($request);
        $date = new DateTimeImmutable($day . '00:01:00');

        // attraper la liste des objets correpsondants au representant et au jour 
        $attributionClientsByRepAndDate = $customerCardRepository->findByStaffAndMeetingDate($user, $date);
        $meetingPoints = $meetingPointRepository->findAll();


        if (!empty($_POST) and $request->getMethod() == "POST") { 

           // dd($request);
            foreach ($request->request as $key => $currentRequest) {
                /* dump($key . ' - ' .$currentRequest); */
                // convertir la clé en tableau
                $keyTab = explode("_", $key);
                // récupérer l'objet correspondant a l id
                $currentCustommerCard = $customerCardRepository->find($keyTab[1]);
                // si c est heure set l objet avec l heure
                if ($keyTab[0] == 'hour') {
                    $dateTimeImmutable = new DateTimeImmutable($day . ' '. $currentRequest);
                    $currentCustommerCard->setMeetingAt($dateTimeImmutable);
                }
                // si c est l'endroit convertir l objet avec l endroit 
                else if ($keyTab[0] == 'meetingPoint') {
                    $meetingPoint = $meetingPointRepository->find($currentRequest);
                    $currentCustommerCard->setMeetingPoint($meetingPoint);
                }
            }

            $manager->flush();
            return $this->redirect($this->generateUrl('app_admin_ivan_replist'));
        }



/*         dump($attributionClientsByRepAndDate);



        dump($day);
        dump($user);
 */

        return $this->render('ivan/attributionMeetings.html.twig', [
            "date" => $date,
            "attributionClientsByRepAndDate" => $attributionClientsByRepAndDate,
            "meetingPoints" => $meetingPoints, 
            "user" => $user
        ]);
    }



        // route qui affiche la fiche d un rep et ses assignations de clients pou un jour donné
    // la fiche doit permettre de changer la date du mmeting comme de rep
    #[Route('/ivan/stickers',name: 'app_admin_stickers_par_date',methods:["POST", "GET"])]
    public function stickersParDate(CustomerCardRepository $customerCardRepository, MeetingPointRepository $meetingPointRepository,  EntityManagerInterface $manager, Request $request,DefineQueryDate $defineQueryDate): Response 
    {

        $day =  $defineQueryDate->returnDay($request);
        $date = new DateTimeImmutable($day . '00:01:00');

        // récupérer les cutomerCard correspondant à la meeting date
        $meetings = $customerCardRepository->findByMeetingDate($date);


        return $this->render('ivan/stickers.html.twig', [
            "date" => $date,
            "meetings" => $meetings,
   
/*             "attributionClientsByRepAndDate" => $attributionClientsByRepAndDate,
            "meetingPoints" => $meetingPoints, 
            "user" => $user */
        ]);


    }


}
