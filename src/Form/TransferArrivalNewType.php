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
use Symfony\Component\Translation\TranslatableMessage;

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
                'label' => new TranslatableMessage('First and last name'),
                'mapped' => false
            ])
            ->add('reservationNumber', TextType::class,[
                'mapped' => false
            ])
            ->add('jumboNumber', TextType::class,[
                'mapped' => false,
                'label' => new TranslatableMessage('Jumbo Number')
            ])
            ->add('agency', EntityType::class, [
                'mapped' => false,
                'label' => "Agencies",
                'placeholder' =>  new TranslatableMessage('Choose an agency'),
                'class' => Agency::class,
                'autocomplete' =>false,
                'choices' => $agencies,
            ] )    
            ->add('adultsNumber', null, [
                'label' => new TranslatableMessage('Adults Number')
            ])   
            ->add('childrenNumber', null, [
                'label' => new TranslatableMessage('Children Number')
            ])   
            ->add('babiesNumber', null, [
                'label' => new TranslatableMessage('Babies Number')
            ])   
            ->add('serviceNumber', null, [
                'label' => new TranslatableMessage('Service Number')
            ])
            ->add('flightNumber', null, [
                'label' => new TranslatableMessage('Flight Number')
            ])
            ->add('date',  DateType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime_immutable',
                'label' => new TranslatableMessage('Arrival Date')
            ])
                
            ->add('hour')
            ->add('fromStart', EntityType::class,[
            'label' =>  new TranslatableMessage('Airports'),
            'placeholder' =>  new TranslatableMessage('Choose an Airport'),
            'class' => AirportHotel::class,
            'autocomplete' =>false,
            'choices' => $airports,
            ])
            ->add('toArrival', EntityType::class,[
                'label' =>  new TranslatableMessage('Hotels'),
                'placeholder' => new TranslatableMessage('Choose an Hotel'),
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
