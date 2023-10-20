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
                'label' => 'Role',
                'choices' => [
                    'Admin' => 'ROLE_SUPERMAN',
                    'Rep' => 'ROLE_REP',
                    'Airport' => 'ROLE_AIRPORT',
                    'Operations' => 'ROLE_OPERATIONS',
                    'Import' => 'ROLE_IMPORT',
                    'Briefings' => 'ROLE_BRIEFING',
                ],
                'multiple' => true,
                'expanded' => true
            ])         
            ->add('phoneNumber')
            ->add('area', EntityType::class, [
                'class' => Area::class,
                'placeholder' => 'Choose your area',
                'autocomplete' => false,
            ])
            ->add('deactivate') 
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
