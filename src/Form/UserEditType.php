<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                    'Airport supervisor' => 'ROLE_AIRPORT_SUPERVISOR',
                    'Rep' => 'ROLE_REP',
                    'Airport' => 'ROLE_AIRPORT',
                    'Reservations' => 'ROLE_RESERVATIONS',
                    'Transfers' => 'ROLE_TRANSFERS',
                    'Briefings' => 'ROLE_BRIEFING',
                ],
                'multiple' => true,
                'expanded' => true,
                'constraints' => [
                        new NotBlank([
                            'message' => 'Select at least one choice.',
                        ]), 
                ],
            ])         
            ->add('phoneNumber')
            ->add('area', EntityType::class, [
                'class' => Area::class,
                'placeholder' => 'Choose your area',
                'autocomplete' => false,
            ])            
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required'=>false,
                /* 'invalid_message' => 'Les mots de passe doivent être identiques.', */
                'options' => ['attr' => ['class' => 'password-field'], 'required' => false ],
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm password'],
                
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
