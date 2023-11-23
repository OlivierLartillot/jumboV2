<?php

namespace App\Form;

use App\Entity\Agency;
use App\Entity\CustomerCard;
use App\Repository\AgencyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerCardNewType extends AbstractType
{

    public function __construct(private AgencyRepository $agencyRepository) 
    {
        $this->$agencyRepository = $agencyRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $agencies = $this->agencyRepository->findBy([], ['name'=> 'ASC']);


        $builder
            ->add('reservationNumber', null, [
            ] )
            ->add('jumboNumber', null, [
            ])
            ->add('holder', null, [
                'label' => 'Full Name',
            ])
            ->add('agency', EntityType::class, [
                'class' => Agency::class,
                'choices'=> $agencies,
                ])
            ->add('adultsNumber', null, [
                'label' => 'Adults quantity',
            ])
            ->add('childrenNumber', null, [
                'label' => 'Children quantity',
            ])
            ->add('babiesNumber', null, [
                'label' => 'Babies quantity',
            ])
            ->add('status', null, [
                'attr' => ['hidden' => true], 
                'label' => false
            ])
            ->add('meetingPoint', null, [
                'required' => true,
                'attr' => ['hidden' => true], 
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerCard::class,
        ]);
    }
}
