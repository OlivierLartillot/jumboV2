<?php

namespace App\Form;

use App\Entity\Agency;
use App\Entity\CustomerCard;
use App\Entity\User;
use App\Repository\AgencyRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerCardType extends AbstractType
{
    private $agencyRepository;


    public function __construct(AgencyRepository  $agencyRepository){
        $this->agencyRepository = $agencyRepository;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $agencies = $this->agencyRepository->findBy([], ["name" => "ASC"]);


        $builder
            ->add('reservationNumber', null, [
                'disabled' => true,
            ] )
            ->add('jumboNumber', null, [
                'disabled' => true,
            ])
            ->add('holder', null, [
                'label' => 'First and last name',
            ])
            ->add('agency', EntityType::class, [
                'class' => Agency::class,
                'choices'=> $agencies,
                ])
            ->add('reservationCancelled')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerCard::class,
        ]);
    }
}
