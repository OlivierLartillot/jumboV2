<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;



class DropzoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', DropzoneType::class, [
                'attr' => ['data-controller' => 'mydropzone'],
            ])
        ;
    }
}
