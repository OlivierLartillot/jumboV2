<?php

namespace App\Controller;

use App\Entity\CustomerCard;
use App\Entity\TransferInterHotel;
use App\Form\TransferInterHotelType;
use App\Repository\TransferInterHotelRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/transfer/interhotel')]
class TransferInterHotelController extends AbstractController
{
    #[Route('/', name: 'app_transfer_inter_hotel_index', methods: ['GET'])]
    public function index(TransferInterHotelRepository $transferInterHotelRepository): Response
    {
        return $this->render('transfer_inter_hotel/index.html.twig', [
            'transfer_inter_hotels' => $transferInterHotelRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_transfer_inter_hotel_new', methods: ['GET', 'POST'])]
    public function new(CustomerCard $customerCard, Request $request, EntityManagerInterface $entityManager): Response
    {
        $transferInterHotel = new TransferInterHotel();
        $form = $this->createForm(TransferInterHotelType::class, $transferInterHotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $date =$transferInterHotel->getDate()->format('Y-m-d');
            $hour = $transferInterHotel->getHour()->format('H:i');

            $dateHour = new DateTimeImmutable($date . ' '. $hour);

            $transferInterHotel->setDateHour($dateHour);
            $transferInterHotel->setCustomerCard($customerCard);

            $entityManager->persist($transferInterHotel);
            $entityManager->flush();

            return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transfer_inter_hotel/new.html.twig', [
            'transfer_inter_hotel' => $transferInterHotel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transfer_inter_hotel_show', methods: ['GET'])]
    public function show(TransferInterHotel $transferInterHotel): Response
    {
        return $this->render('transfer_inter_hotel/show.html.twig', [
            'transfer_inter_hotel' => $transferInterHotel,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transfer_inter_hotel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TransferInterHotel $transferInterHotel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TransferInterHotelType::class, $transferInterHotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $date =$transferInterHotel->getDate()->format('Y-m-d');
            $hour = $transferInterHotel->getHour()->format('H:i');
            $dateHour = new DateTimeImmutable($date . ' '. $hour);

            $transferInterHotel->setDateHour($dateHour);
            $entityManager->flush();

            return $this->redirectToRoute('app_customer_card_show', [
                'id' => $transferInterHotel->getCustomerCard()->getId()
                ], 
                Response::HTTP_SEE_OTHER);
        }

        return $this->render('transfer_inter_hotel/edit.html.twig', [
            'transfer_inter_hotel' => $transferInterHotel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transfer_inter_hotel_delete', methods: ['POST'])]
    public function delete(Request $request, TransferInterHotel $transferInterHotel, EntityManagerInterface $entityManager): Response
    {
        $customerCard = $transferInterHotel->getCustomerCard();
        if ($this->isCsrfTokenValid('delete'.$transferInterHotel->getId(), $request->request->get('_token'))) {
            $entityManager->remove($transferInterHotel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_customer_card_show', ['id' => $customerCard->getId()], Response::HTTP_SEE_OTHER);
    }
}
