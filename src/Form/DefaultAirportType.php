<?php

namespace App\Form;

use App\Entity\AirportHotel;
use App\Entity\User;
use App\Repository\AirportHotelRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class DefaultAirportType extends AbstractType
{
    private AirportHotelRepository $airportHotelRepository;
    public function __construct(AirportHotelRepository $airportHotelRepository,) {
        $this->airportHotelRepository = $airportHotelRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
 
        $airports = $this->airportHotelRepository->findBy(['isAirport' => true],['name'=> 'ASC']);
        $builder
         ->add('airport', EntityType::class,[
            'label' => "Airports",
            'placeholder' => 'All Airports',
            'class' => AirportHotel::class,
            'autocomplete' =>false,
            'choices' => $airports,
            'attr' => [
                'novalidate' => 'novalidate'
            ],
            'required' => false
        ]) 
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}