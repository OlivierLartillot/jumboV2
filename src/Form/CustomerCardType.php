<?php

namespace App\Form;

use App\Entity\CustomerCard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reservationNumber', null, [
                'disabled' => true,
            ] )
            ->add('jumboNumber', null, [
                'disabled' => true,
            ])
            ->add('holder')
            ->add('agency')
            ->add('adultsNumber')
            ->add('childrenNumber')
            ->add('babiesNumber')
            ->add('meetingAt', null, [
                'widget' => 'single_text',
            ])
            ->add('reservationCancelled')
            ->add('status')
            ->add('meetingPoint')
            ->add('staff')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerCard::class,
        ]);
    }
}
