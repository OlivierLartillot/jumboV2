<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DefaultAirportType;
use App\Repository\AirportHotelRepository;
use App\Repository\StatusRepository;
use App\Repository\TransferArrivalRepository;
use App\Repository\UserRepository;
use App\Services\DefineQueryDate;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AirportController extends AbstractController
{

    #[Route('/airport', name: 'app_customer_card_airport', methods: ['GET'])]
    public function airport(Request $request, 
                            TransferArrivalRepository $transferArrivalRepository, 
                            AirportHotelRepository $airportHotelRepository, 
                            StatusRepository $statusRepository,
                            DefineQueryDate $defineQueryDate): Response
    {

        $airports = $airportHotelRepository->findBy(['isAirport' => true]);
        $status = $statusRepository->findAll();

        
        // récupère tous les arrivées du jour et de l'aéroport par défaut
        $day =  $defineQueryDate->returnDay($request);

        $date = new DateTimeImmutable($day);
        $defaultAirport = $this->getUser()->getAirport();
        $flightNumbers = [];

        //dd($request->query);
        // si on a cliqué sur envoyé
        if (count($request->query) > 0) {
            $empty = true;
            //on vérifie si on a envoyé au moins un élément de tri
            foreach ($request->query as $param) {
                if ($param != null) {
                    $empty = false;
                    break;
                }
            }
            // si y a au moins un élément envoyé au tri
            if ($empty == false) { 
                $date= htmlspecialchars($request->query->get('date'));
                $date = new DateTimeImmutable($date);
                $airport= trim(htmlspecialchars($request->query->get('airports')));
                $flightNumber =  trim(htmlspecialchars($request->query->get('flightNumber')));
                $voucherNumber =  trim(htmlspecialchars($request->query->get('voucherNumber')));
                
                $results = $transferArrivalRepository->findByDateAirportFlightNumberVoucherNumber($date, $airport, $flightNumber, $voucherNumber);
           
                foreach ($results as $arrival) {
                    $flightNumber = strtoupper($arrival->getFlightNumber());
                    if (!in_array($flightNumber, $flightNumbers)) {
                        $flightNumbers[] = $flightNumber;
                    }
                }
                return $this->render('airport/airport.html.twig', [
                    'results' => $results,
                    'airports' => $airports,
                    'airport' => $airport,
                    'status' =>  $status,
                    'flightNumbers' => $flightNumbers
                ]);                
            }
        }

        $date = new DateTimeImmutable('now');
        $results = $transferArrivalRepository->findBy([
            'date' => $date,
            'fromStart' =>  $defaultAirport
        ], ['hour' => 'ASC']);
        foreach ($results as $arrival) {
            $flightNumber = strtoupper($arrival->getFlightNumber());
            if (!in_array($flightNumber, $flightNumbers)) {
                $flightNumbers[] = $flightNumber;
            }
        }

        return $this->render('airport/airport.html.twig', [
            'results' => $results,
            'airports' => $airports,
            'status' =>  $status,
            'flightNumbers' => $flightNumbers
        ]);

    }


    #[Route('/airport/default/{id}/edit', name: 'app_user_airport_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $hasher): Response
    {

        $form = $this->createForm(DefaultAirportType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
     
            $user = $form->getData();
            //$user->setAirport(null);
            //dd($user);
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_customer_card_airport', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('airport/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

}