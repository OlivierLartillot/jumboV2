<?php

namespace App\Form;

use App\Entity\AirportHotel;
use App\Entity\MeetingPoint;
use App\Entity\TransferArrival;
use App\Entity\User;
use App\Repository\AgencyRepository;
use App\Repository\AirportHotelRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferArrivalType extends AbstractType
{


    private $agencyRepository;
    private $airPortHotelRepository;
    private $userRepository;

    public function __construct(AgencyRepository $agencyRepository, AirportHotelRepository $airPortHotelRepository, UserRepository $userRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->airPortHotelRepository = $airPortHotelRepository;
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $repList = [];
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            if (in_array('ROLE_REP', $user->getRoles())) {
                $repList[] = $user;
            }
        }

        $airports = $this->airPortHotelRepository->findBy(['isAirport' => true],['name'=> 'ASC']);
        $hotels = $this->airPortHotelRepository->findBy(['isAirport' => false],['name'=> 'ASC']);

        $builder         
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
        ->add('staff', EntityType::class, [
            'label' => "Reps",
            'placeholder' => 'Choose a Rep',
            'class' => User::class,
            'choices' => $repList,
            'required' => false,
        ] )
        ->add('meetingPoint', EntityType::class, [
            'label' => "Meeting Point",
            'placeholder' => 'Choose a meeting point',
            'class' => MeetingPoint::class,
            'required' => false,

            
        ] )
        ->add('meetingAt', DateTimeType::class, [
            'widget' => 'single_text',
            'input'  => 'datetime_immutable'
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransferArrival::class,
        ]);
    }
}
