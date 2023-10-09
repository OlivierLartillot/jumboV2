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
            ->add('serviceNumber')
            ->add('dateHour')
            ->add('flightNumber')
            ->add('isCollective')
            ->add('date')
            ->add('hour')
            ->add('customerCard')
            ->add('fromStart')
            ->add('toArrival')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransferArrival::class,
        ]);
    }
}
