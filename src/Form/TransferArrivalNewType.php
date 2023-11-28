<?php

namespace App\Form;

use App\Entity\Agency;
use App\Entity\AirportHotel;
use App\Entity\TransferArrival;
use App\Repository\AgencyRepository;
use App\Repository\AirportHotelRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferArrivalNewType extends AbstractType
{

    private $agencyRepository;
    private $airPortHotelRepository;

    public function __construct(AgencyRepository $agencyRepository, AirportHotelRepository $airPortHotelRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->airPortHotelRepository = $airPortHotelRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $agencies = $this->agencyRepository->findBy([],['name' => 'ASC']); 
        $airports = $this->airPortHotelRepository->findBy(['isAirport' => true],['name'=> 'ASC']);
        $hotels = $this->airPortHotelRepository->findBy(['isAirport' => false],['name'=> 'ASC']);

        $builder    
            ->add('fullName', TextType::class,[
                'mapped' => false
            ])
            ->add('reservationNumber', TextType::class,[
                'mapped' => false
            ])
            ->add('jumboNumber', TextType::class,[
                'mapped' => false
            ])
            ->add('agency', EntityType::class, [
                'mapped' => false,
                'label' => "Agencies",
                'placeholder' => 'Choose an agency',
                'class' => Agency::class,
                'autocomplete' =>false,
                'choices' => $agencies,
            ] )    
            ->add('adultsNumber')   
            ->add('childrenNumber')   
            ->add('babiesNumber')   
            ->add('serviceNumber')
            ->add('flightNumber')
            ->add('date',  DateType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime_immutable'
            ])
                
            ->add('hour')
            ->add('fromStart', EntityType::class,[
            'label' => "Airports",
            'placeholder' => 'Choose an Airport',
            'class' => AirportHotel::class,
            'autocomplete' =>false,
            'choices' => $airports,
            ])
            ->add('toArrival', EntityType::class,[
                'label' => "Hotels",
                'placeholder' => 'Choose an Hotel',
                'class' => AirportHotel::class,
                'autocomplete' =>false,
                'choices' => $hotels,
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btn-success'],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransferArrival::class,
        ]);
    }
}
