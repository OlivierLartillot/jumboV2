<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\CustomerCard;
use App\Form\CommentType;
use App\Form\CustomerCardType;
use App\Repository\AgencyRepository;
use App\Repository\AirportHotelRepository;
use App\Repository\CommentRepository;
use App\Repository\CustomerCardRepository;
use App\Repository\StatusRepository;
use App\Repository\TransferJoanRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/customer/card')]
class CustomerCardController extends AbstractController
{
    #[Route('/', name: 'app_customer_card_index', methods: ['GET'])]
    public function index(Request $request, 
                          CustomerCardRepository $customerCardRepository, 
                          StatusRepository $statusRepository, 
                          UserRepository $userRepository,
                          AgencyRepository $agencyRepository,
                          AirportHotelRepository $airportHotelRepository): Response
    {

            //Listes des informations a afficher dans les tris
        $agencies = $agencyRepository->findAllAsc();
        $hotels = $airportHotelRepository->findAllAsc();
   
        
        $statusList = $statusRepository->findAll();
        $users = $userRepository->findAll();
        $reps = [];
        foreach ($users as $user) {
            if (in_array("ROLE_REP", $user->getRoles() )) {
                $reps[] = $user;
            }
        }
        
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
                //dd(checkdate(01, 13, 2019));
                
                // todo  : alors on peut récupérer les données et les filtrer
                

                $customerPresence = $request->query->get('customerPresence');




                // si tout va bien  on envoie la dql 
                $dateStart = $request->query->get('dateStart');
                $dateEnd = $request->query->get('dateEnd');
                $dateStart = ($dateStart != "") ? New DateTimeImmutable($dateStart . '00:00:00') : null ;
                $dateEnd = ($dateEnd != "") ? $dateEnd = New DateTimeImmutable($dateEnd . '23:59:59') : null;
                $rep = $request->query->get('reps');
                $natureTransfer = $request->query->get('natureTransfer');
                $status = $request->query->get('status');

                //! hotels
                $hotel = $request->query->get('hotel');
                $agency = $request->query->get('agency');
                $flightNumber = $request->query->get('flightNumber');
                $search = $request->query->get('search');
                    
                $flightNumber = ($flightNumber == "") ? "all" : $flightNumber;


                dd('jusque la tout va bien ? ');
                // la requete qui execute la recherche
                //$results = $customerCardRepository->customerCardPageSearch($dateStart, $dateEnd, $customerPresence, $rep, $status, $agency, $hotel, $search, $natureTransfer, $flightNumber);

                //dd($results);
                // et on envoi la nouvelle page 
                return $this->render('customer_card/index.html.twig', [
                    'customer_cards' => $results,
                    'agencies' => $agencies,
                    'hotels' => $hotels,
                    'statusList' => $statusList,
                    'reps' => $reps
                ]);
                
                
                // sinon renvoyer la page de base
                

            }
            // sinon on renvoie la page de base 
            // todo ? peut etre un message flash ?
        }


        // quand on arrive sur la page on récupere les mouvements du jour
        $findAllByNow = $customerCardRepository->findByNow();

        return $this->render('customer_card/index.html.twig', [
            'customer_cards' => $findAllByNow,
            'agencies' => $agencies,
            'hotels' => $hotels,
            'statusList' => $statusList,
            'reps' => $reps
        ]);
    }

    #[Route('/search', name: 'app_customer_card_search', methods: ['GET', 'POST'])]
    public function search(Request $request, CustomerCardRepository $customerCardRepository): Response
    {
        $results = $customerCardRepository->search($request->request->get('search'));

        return $this->render('customer_card/search.html.twig', [
            'customer_cards' => $results
        ]); 

    }

    #[Route('/pax', name: 'app_customer_card_pax', methods: ['GET', 'POST'])]
    public function pax(Request $request, CustomerCardRepository $customerCardRepository, UserRepository $userRepository): Response
    { 

        $users = $userRepository->findAll();
        $reps = [];
        foreach ($users as $user) {
            if (in_array("ROLE_REP", $user->getRoles() )) {
                $reps[] = $user;
            }
        }

        return $this->render('customer_card/calcul_pax_rep.html.twig', [
            'reps' => $reps
        ]); 
    }

    #[Route('/pax/rep/{id}', name: 'app_customer_card_pax_par_rep', methods: ['GET', 'POST'])]
    public function paxParRep(Request $request, CustomerCardRepository $customerCardRepository, UserRepository $userRepository): Response
    { 

        //! Attention si l id est différent du user courant, pas le droit


        $users = $userRepository->findAll();
        $reps = [];
        foreach ($users as $user) {
            if (in_array("ROLE_REP", $user->getRoles() )) {
                $reps[] = $user;
            }
        }

        return $this->render('customer_card/calcul_pax_par_rep.html.twig', [
            'reps' => $reps
        ]); 
    }

    #[Route('/transportation/management', name: 'app_customer_card_transportation_management', methods: ['GET', 'POST'])]
    public function transportationManagement(Request $request, TransferJoanRepository $transferJoanRepository, CustomerCardRepository $customerCardRepository, UserRepository $userRepository): Response
    {


        $transportCompanies = $transferJoanRepository->transportCompanyList();

        return $this->render('customer_card/transportation_management.html.twig', [
            'transportCompanies' => $transportCompanies
        ]);
    }

    #[Route('/airport', name: 'app_customer_card_airport', methods: ['GET', 'POST'])]
    public function airport(Request $request, TransferJoanRepository $transferJoanRepository, CustomerCardRepository $customerCardRepository, UserRepository $userRepository): Response
    {

        return $this->render('customer_card/airport.html.twig', [

        ]);

    }


    #[Route('/new', name: 'app_customer_card_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CustomerCardRepository $customerCardRepository): Response
    {
        $customerCard = new CustomerCard();
        $form = $this->createForm(CustomerCardType::class, $customerCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerCardRepository->save($customerCard, true);

            return $this->redirectToRoute('app_customer_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer_card/new.html.twig', [
            'customer_card' => $customerCard,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_card_show', methods: ['GET' , 'POST'])]
    public function show(CustomerCard $customerCard, Request $request, CommentRepository $commentRepository, UserRepository $userRepository): Response
    {

        $user = $userRepository->find(3);
        $comments = $commentRepository->findAll();

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            


            $file = $form['media']->getData();
            $comment = new Comment;

            if ( ($file == null ) and ($form['content']->getData() == null) and ($form['predefinedCommentsMessages']->getData() == null) ) {
                return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard->getId()], Response::HTTP_SEE_OTHER);
            }

            if ($file !== null) {
                $someNewFilename = $user->getUsername() . '_report_' . date("dmYgi");
                $directory = 'images/comments_medias/';
                $extension = $file->guessExtension();
                $file->move($directory, $someNewFilename.'.'.$extension);
                $comment->setMedia($someNewFilename.'.'.$extension);
            } 
            if ($form['content']->getData() !== null) {
                $comment->setContent($form['content']->getData()); 
            }
            if ($form['predefinedCommentsMessages']->getData() !== null) {
                $comment->setpredefinedCommentsMessages($form['predefinedCommentsMessages']->getData());
            }

            $comment->setCreatedBy($user);
            $comment->setCustomerCard($customerCard);


            $commentRepository->save($comment, true);
            return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard->getId()], Response::HTTP_SEE_OTHER);
        }

 
/*         if (($request->request->get('message') !== null)) {

            $comment = new Comment;
            $comment->setCreatedBy($user);
            $comment->setContent($request->request->get('message'));
            $comment->setCustomerCard($customerCard);
            $commentRepository->save($comment, true);

            return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard->getId()], Response::HTTP_SEE_OTHER);

        } */


        return $this->render('customer_card/show.html.twig', [
            'customer_card' => $customerCard,
            'comments' => $comments,
            'form' => $form
        ]);
    }

    #[Route('/{id}/edit', name: 'app_customer_card_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CustomerCard $customerCard, CustomerCardRepository $customerCardRepository): Response
    {
        $form = $this->createForm(CustomerCardType::class, $customerCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerCardRepository->save($customerCard, true);

            return $this->redirectToRoute('app_customer_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('customer_card/edit.html.twig', [
            'customer_card' => $customerCard,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_card_delete', methods: ['POST'])]
    public function delete(Request $request, CustomerCard $customerCard, CustomerCardRepository $customerCardRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customerCard->getId(), $request->request->get('_token'))) {
            $customerCardRepository->remove($customerCard, true);
        }

        return $this->redirectToRoute('app_customer_card_index', [], Response::HTTP_SEE_OTHER);
    }

}
