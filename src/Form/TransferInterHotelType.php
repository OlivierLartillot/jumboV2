<?php

namespace App\Form;

use App\Entity\AirportHotel;
use App\Entity\TransferInterHotel;
use App\Repository\AirportHotelRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferInterHotelType extends AbstractType
{

    public function __construct(private AirportHotelRepository $airportHotelRepository) 
    {
        $this->airportHotelRepository = $airportHotelRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $hotels = $this->airportHotelRepository->findBy(['isAirport' => false]);

        $builder
            ->add('serviceNumber')
            ->add('date')
            ->add('hour')
            ->add('fromStart', EntityType::class, [
                'class' => AirportHotel::class,
                'choices' => $hotels
            ])
            ->add('toArrival', EntityType::class, [
                'class' => AirportHotel::class,
                'choices' => $hotels
            ])
            ->add('isCollective')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransferInterHotel::class,
        ]);
    }
}
