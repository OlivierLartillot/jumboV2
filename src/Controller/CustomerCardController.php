<?php

namespace App\Controller;

use App\Entity\CustomerCard;
use App\Form\CustomerCardType;
use App\Repository\CustomerCardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/customer/card')]
class CustomerCardController extends AbstractController
{
    #[Route('/', name: 'app_customer_card_index', methods: ['GET'])]
    public function index(CustomerCardRepository $customerCardRepository): Response
    {
        return $this->render('customer_card/index.html.twig', [
            'customer_cards' => $customerCardRepository->findAll(),
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

        return $this->renderForm('customer_card/new.html.twig', [
            'customer_card' => $customerCard,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_customer_card_show', methods: ['GET'])]
    public function show(CustomerCard $customerCard): Response
    {


            











    
        return $this->render('customer_card/show.html.twig', [
            'customer_card' => $customerCard,
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
