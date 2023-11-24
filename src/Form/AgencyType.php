<?php

namespace App\Form;

use App\Entity\Agency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgencyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null , [
              'disabled' => true,  
            ])
            ->add('language', ChoiceType::class, [
                'choices' => [
                    'English' => 'en',
                    'Spanish' => 'es',
                    'French' => 'fr',
                    'Italian' => 'it',
                    'Portuguese' => 'po',
                ],
                'multiple' => false,
                'expanded' => true
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agency::class,
        ]);
    }
}
