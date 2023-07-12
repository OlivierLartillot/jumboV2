<?php

namespace App\Controller;

use App\Entity\TransferDeparture;
use App\Repository\TransferDepartureRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepController extends AbstractController
{
    #[Route('/rep/salidas', name: 'app_rep')]
    public function index(UserRepository $userRepository, TransferDepartureRepository $transferDepartureRepository): Response
    {

        
        // si le formulaire a été envoyé


        if (( !empty($_GET['date']) )) {
  
        $date = $_GET['date'];

        // TODO : recupérer le rep actif
        $user= $userRepository->find(3);
        
        //recupérer tous les  clients de ce rep pour departure a cette date
        $allCustomersDeparture = $transferDepartureRepository->findByUserAndDate($user, $date);
        return $this->render('rep/index.html.twig', [
            'allCustomersDeparture' => $allCustomersDeparture
        ]);
        }




        return $this->render('rep/index.html.twig', [
            
        ]);
    }
}
