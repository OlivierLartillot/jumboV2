<?php

namespace App\Form;

use App\Entity\WhatsAppMessage;
use Eckinox\TinymceBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WhatsAppMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeTransfer')
            ->add('language')
            ->add('message', TinymceType::class, [

                "attr" => [
                    "toolbar" => "undo redo  | bold italic",
                   " plugins"=> ""
                ],]
                )
            ->add('isDefaultMessage')
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WhatsAppMessage::class,
        ]);
    }
}
