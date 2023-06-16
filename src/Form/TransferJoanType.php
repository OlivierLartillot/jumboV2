<?php

namespace App\Form;

use App\Entity\TransferJoan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferJoanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fromStartJoan')
            ->add('toArrivalJoan')
            ->add('dateHourJoan')
            ->add('flightNumberJoan')
            ->add('privateCollectiveJoan')
            ->add('pickupTime')
            ->add('transportCompany')
            ->add('vehicleNumber')
            ->add('vehicleType')
            ->add('transferArea')
            ->add('voucherNumber')
            ->add('adultsNumber')
            ->add('chuldrenNumber')
            ->add('babiesNumber')
            ->add('natureTransfer')
            ->add('customerCard')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransferJoan::class,
        ]);
    }
}
