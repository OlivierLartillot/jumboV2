<?php

namespace App\Form;

use App\Entity\AirportHotel;
use App\Entity\TransferDeparture;
use App\Repository\AirportHotelRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferDepartureType extends AbstractType
{
    public function __construct(private AirportHotelRepository $airportHotelRepository) 
    {
        $this->airportHotelRepository = $airportHotelRepository;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $hotels = $this->airportHotelRepository->findBy(['isAirport' => false]);
        $airports = $this->airportHotelRepository->findBy(['isAirport' => true]);
        $builder
            ->add('flightNumber')
            ->add('date')
            ->add('hour')
            ->add('fromStart', EntityType::class, [
                'class' => AirportHotel::class,
                'choices' => $hotels
            ])
            ->add('toArrival', EntityType::class, [
                'class' => AirportHotel::class,
                'choices' => $airports
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransferDeparture::class,
        ]);
    }
}
