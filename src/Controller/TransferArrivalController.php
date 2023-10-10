<?php

namespace App\Controller;

use App\Entity\CustomerCard;
use App\Entity\TransferArrival;
use App\Form\TransferArrivalType;
use App\Repository\TransferArrivalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
