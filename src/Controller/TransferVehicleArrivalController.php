<?php

namespace App\Controller;

use App\Entity\TransferVehicleArrival;
use App\Form\TransferVehicleArrivalType;
use App\Repository\TransferArrivalRepository;
use App\Repository\TransferVehicleArrivalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/transfer/vehicle/arrival')]
class TransferVehicleArrivalController extends AbstractController
{
    #[Route('/', name: 'app_transfer_vehicle_arrival_index', methods: ['GET'])]
    public function index(TransferVehicleArrivalRepository $transferVehicleArrivalRepository): Response
    {
        return $this->render('transfer_vehicle_arrival/index.html.twig', [
            'transfer_vehicle_arrivals' => $transferVehicleArrivalRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_transfer_vehicle_arrival_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TransferArrivalRepository $transferArrivalRepository): Response
    {
        $transferVehicleArrival = new TransferVehicleArrival();
        $form = $this->createForm(TransferVehicleArrivalType::class, $transferVehicleArrival);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $arrival = $transferArrivalRepository->find($request->get('id'));
   
            $transferVehicleArrival->setTransferArrival($arrival);
            $entityManager->persist($transferVehicleArrival);
            $entityManager->flush();

            return $this->redirectToRoute('app_customer_card_show', ['id' => $transferVehicleArrival->getTransferArrival()->getCustomerCard()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transfer_vehicle_arrival/new.html.twig', [
            'transfer_vehicle_arrival' => $transferVehicleArrival,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transfer_vehicle_arrival_show', methods: ['GET'])]
    public function show(TransferVehicleArrival $transferVehicleArrival): Response
    {
        return $this->render('transfer_vehicle_arrival/show.html.twig', [
            'transfer_vehicle_arrival' => $transferVehicleArrival,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transfer_vehicle_arrival_edit', methods: ['GET', 'POST'])]
    public function edit(TransferVehicleArrival $transferVehicleArrival, Request $request, EntityManagerInterface $entityManager): Response
    {



        $form = $this->createForm(TransferVehicleArrivalType::class, $transferVehicleArrival);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_customer_card_show', ['id' => $transferVehicleArrival->getTransferArrival()->getCustomerCard()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transfer_vehicle_arrival/edit.html.twig', [
            'transfer_vehicle_arrival' => $transferVehicleArrival,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transfer_vehicle_arrival_delete', methods: ['POST'])]
    public function delete(Request $request, TransferVehicleArrival $transferVehicleArrival, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transferVehicleArrival->getId(), $request->request->get('_token'))) {
            $entityManager->remove($transferVehicleArrival);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_customer_card_show', ['id' => $transferVehicleArrival->getTransferArrival()->getCustomerCard()->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/airport/isChecked/{id}', name: 'app_transfer_vehicle_arrival_isChecked', methods: ['POST'])]
    public function isChecked(Request $request, TransferVehicleArrival $transferVehicleArrival, EntityManagerInterface $entityManager): Response
    {

        $transferVehicleArrival->setIsChecked(!$transferVehicleArrival->isIsChecked());
        $entityManager->flush();

        try {
            return $this->json(
                    // les données à transformer en JSON
                    [
                        'id' => $transferVehicleArrival->getId(), 
                        'isChecked' => $transferVehicleArrival->isIsChecked()
                    ], 
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
