<?php

namespace App\Controller\api;

use App\Repository\BusVoucherMappingRepository;
use App\Repository\CustomerCardRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

class ApiController extends AbstractController
{

    // !!! modification du LoginTimeSubscriber pour permettre à cette route ublique de ne pas être
    // renvoyée vers le login form
    #[Route('/api/public/test', name: 'app_api_public_test', methods: ['GET'])]
    public function bus(BusVoucherMappingRepository $busVoucherMappingRepository): Response
    {

        return $this->json(
            $busVoucherMappingRepository->findAll(),
            200,
            [],
            ['groups' => 'api_public_test']   
        );
    }

    #[Route('/admin/api/bus', name: 'app_api_bus_admin', methods: ['GET'])]
    public function busAdmin(BusVoucherMappingRepository $busVoucherMappingRepository): Response
    {

        return $this->json(
            $busVoucherMappingRepository->findAll(),
            200,
            [],
            ['groups' => 'bus_correspondence_admin']
            
        );
    }

    #[Route('/api/users', name: 'app_api', methods: ['GET'])]
    public function users(UserRepository $userRepository): Response
    {


        $context = (new ObjectNormalizerContextBuilder())
                    ->withGroups('show_product')
                    ->toArray();

        return $this->json(
            $userRepository->findAll(),
            200,
            [],
            ['groups' => 'user']
        );
    }

    #[Route('/api/customers/{flightNumber}/{arrivalDate}', name: 'app_customers', methods: ['GET'])]
    public function customersArrival(Request $request, CustomerCardRepository $customerCardRepository , $flightNumber, $arrivalDate): Response
    {
        //dd($flightNumber);
        // param de base de la pagination
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20); 
        

        $customerCardsList = $customerCardRepository->findCustomerCardsByFlightAndDate($flightNumber, $arrivalDate);

        //$customerCardsList = $customerCardRepository->findAllWithPagination($page, $limit);

        return $this->json(
            $customerCardsList,
            200,
            [],
            ['groups' => 'customersArrival']
        );
    }

}
