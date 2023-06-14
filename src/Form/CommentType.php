<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\PredefinedCommentsMessages;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('media', FileType::class, [
                'label' => 'Add Media',
                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,
            ])
            ->add('content', TextareaType::class , [
                'required' => false,
                'attr' => ['style' => 'height: 200px']
            ])
            ->add('predefinedCommentsMessages', EntityType::class, [
                'class' => PredefinedCommentsMessages::class,
                'placeholder' => 'Choose an option',
                'expanded' => false,
                'required' => false,
                'multiple'=> false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
