<?php

namespace App\Form;

use App\Entity\AirportHotel;
use App\Entity\TransferInterHotel;
use App\Repository\AirportHotelRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Factory\Cache\ChoiceLabel;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
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
        $hotels = $this->airportHotelRepository->findBy(['isAirport' => false], ['name'=> 'ASC']);

        $builder
        
        ->add('fromStart', EntityType::class, [
            'class' => AirportHotel::class,
            'choices' => $hotels,
            ])
        ->add('toArrival', EntityType::class, [
            'class' => AirportHotel::class,
            'choices' => $hotels
            ])
        ->add('adultsNumber')
        ->add('childrenNumber')
        ->add('babiesNumber')
        ->add('transportCompany')
        ->add('date')
        ->add('pickUp')
        ->add('isCollective')
        ->add('vehicleNumber')
        ->add('vehicleType')
        ->add('voucherNumber')
        ->add('area')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransferInterHotel::class,
        ]);
    }
}
