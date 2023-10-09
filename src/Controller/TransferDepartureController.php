<?php

namespace App\Controller;

use App\Entity\TransferDeparture;
use App\Form\TransferDepartureType;
use App\Repository\TransferDepartureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/transfer/departure')]
class TransferDepartureController extends AbstractController
{
    #[Route('/', name: 'app_transfer_departure_index', methods: ['GET'])]
    public function index(TransferDepartureRepository $transferDepartureRepository): Response
    {
        return $this->render('transfer_departure/index.html.twig', [
            'transfer_departures' => $transferDepartureRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_transfer_departure_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $transferDeparture = new TransferDeparture();
        $form = $this->createForm(TransferDepartureType::class, $transferDeparture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($transferDeparture);
            $entityManager->flush();

            return $this->redirectToRoute('app_transfer_departure_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transfer_departure/new.html.twig', [
            'transfer_departure' => $transferDeparture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transfer_departure_show', methods: ['GET'])]
    public function show(TransferDeparture $transferDeparture): Response
    {
        return $this->render('transfer_departure/show.html.twig', [
            'transfer_departure' => $transferDeparture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transfer_departure_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TransferDeparture $transferDeparture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TransferDepartureType::class, $transferDeparture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_transfer_departure_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transfer_departure/edit.html.twig', [
            'transfer_departure' => $transferDeparture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transfer_departure_delete', methods: ['POST'])]
    public function delete(Request $request, TransferDeparture $transferDeparture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transferDeparture->getId(), $request->request->get('_token'))) {
            $entityManager->remove($transferDeparture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_transfer_departure_index', [], Response::HTTP_SEE_OTHER);
    }
}
