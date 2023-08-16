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

class UserEditPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                /* 'invalid_message' => 'Les mots de passe doivent être identiques.', */
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Password*'],
                'second_options' => ['label' => 'Confirm password*'],
                'constraints' => [
                        new NotBlank([
                            'message' => 'Password is required',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Your password must contain at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 21,
                        ]),
                    ],
                ])         
            ->add('phoneNumber')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
