<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('roles', ChoiceType::class, [
                'label' => 'Etes-vous ?',
                'choices' => [
                    'Admin' => 'ROLE_SUPERMAN',
                    'Rep' => 'ROLE_REP',
                    'Aeroport' => 'ROLE_AIRPORT',
                    'Opérations' => 'ROLE_OPERATIONS'
                ],
                'multiple' => true,
                'expanded' => true
            ])         
            ->add('phoneNumber')
            ->add('area', EntityType::class, [
                'class' => Area::class,
                'placeholder' => 'Choose your area',
                'autocomplete' => true,
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
