<?php

namespace App\Controller;

use App\Entity\CustomerCard;
use App\Entity\Status;
use App\Entity\StatusHistory;
use App\Entity\TransferArrival;
use App\Form\TransferArrivalType;
use App\Repository\StatusHistoryRepository;
use App\Repository\StatusRepository;
use App\Repository\TransferArrivalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/transfer/arrival')]
class TransferArrivalController extends AbstractController
{
    #[Route('/', name: 'app_transfer_arrival_index', methods: ['GET'])]
    public function index(TransferArrivalRepository $transferArrivalRepository): Response
    {
        return $this->render('transfer_arrival/index.html.twig', [
            'transfer_arrivals' => $transferArrivalRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_transfer_arrival_new', methods: ['GET', 'POST'])]
    public function new(CustomerCard $customerCard, Request $request, EntityManagerInterface $entityManager): Response
    {
        
        $transferArrival = new TransferArrival();
        $form = $this->createForm(TransferArrivalType::class, $transferArrival);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $transferArrival->setCustomerCard($customerCard);
            $entityManager->persist($transferArrival);
            $entityManager->flush();


            return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transfer_arrival/new.html.twig', [
            'transfer_arrival' => $transferArrival,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transfer_arrival_show', methods: ['GET'])]
    public function show(TransferArrival $transferArrival): Response
    {
        return $this->render('transfer_arrival/show.html.twig', [
            'transfer_arrival' => $transferArrival,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transfer_arrival_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TransferArrival $transferArrival, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TransferArrivalType::class, $transferArrival);
        $form->handleRequest($request);

        $customerCard = $transferArrival->getCustomerCard()->getId();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();


            return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transfer_arrival/edit.html.twig', [
            'transfer_arrival' => $transferArrival,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transfer_arrival_delete', methods: ['POST'])]
    public function delete(Request $request, TransferArrival $transferArrival, EntityManagerInterface $entityManager): Response
    {

        $customerCard = $transferArrival->getCustomerCard();

        if ($this->isCsrfTokenValid('delete' . $transferArrival->getId(), $request->request->get('_token'))) {
            $entityManager->remove($transferArrival);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/maj/status/{id}/{statusId}', name: 'app_transfer_arrival_maj_status', methods: ['POST'])]
    public function majStatusAjax(TransferArrival $transferArrival, 
                                    $statusId, 
                                  
                                    StatusRepository $statusRepository, 
                                    StatusHistoryRepository $statusHistoryRepository, 
                                    EntityManagerInterface $entityManager): Response
    {

        //je fais mes traitements
        $newStatus = $statusRepository->findOneBy(['name' => $statusId]);
        $transferArrival->setStatus($newStatus);

        // On met à jour le statusHistory
        $newStatusHistory = new StatusHistory();
        $currentUser = $this->getUser();
        $newStatusHistory->setStatus($newStatus);
        $newStatusHistory->setCustomerCard( $transferArrival->getCustomerCard());
        $newStatusHistory->setUpdatedBy($currentUser);

        $entityManager->persist($newStatusHistory);





        $entityManager->flush();


        try {
            return $this->json(
                    // les données à transformer en JSON
                    $transferArrival->getStatus()->getName(),
                    // HTTP STATUS CODE
                    200,
                    // HTTP headers supplémentaires, dans notre cas : aucune
                    [],
                    // Contexte de serialisation, les groups de propriété que l'on veux serialise
                   
            );
    
         } catch (Exception $e){ // si une erreur est LANCE, je l'attrape
            // je gère l'erreur
            // par exemple si tu me file un genre ['3000'] qui n existe pas...
             return new JsonResponse("Hoouuu !! Ce qui vient d'arriver est de votre faute : JSON invalide", Response::HTTP_UNPROCESSABLE_ENTITY);
        }



    }


}
