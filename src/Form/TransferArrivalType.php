<?php

namespace App\Form;

use App\Entity\TransferArrival;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferArrivalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder            
            ->add('customerCard')
            ->add('serviceNumber')
            ->add('flightNumber')
            ->add('date')
            ->add('hour')
            ->add('fromStart')
            ->add('toArrival')
            ->add('isCollective')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransferArrival::class,
        ]);
    }
}
