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

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe*'],
                'second_options' => ['label' => 'Confirmez le mot de passe*'],
                'constraints' => [
                        new NotBlank([
                            'message' => 'Un mot de passe est nécessaire',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Etes-vous ?',
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
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
