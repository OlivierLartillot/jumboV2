<?php

namespace App\Form;

use App\Entity\MeetingPoint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class MeetingPointType extends AbstractType
{
    // 2. Declare a locally accesible variable
    public $translator;
    
    // 3. Autowire the translator interface and update the local value with the injected one
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add('en', null, [
                'label' => $this->translator->trans('English')
            ])
            ->add('whatsAppEn', null, [
                'label' => false,
                'help' => $this->translator->trans('Expression that appears when a representative uses the variable for WhatsApp text in English'),
            ])
            ->add('es', null, [
                'label' => $this->translator->trans('Spanish')
            ])
            ->add('whatsAppEs', null, [
                'label' => false,
                'help' => $this->translator->trans('Expression that appears when a representative uses the variable for WhatsApp text in Spanish'),
            ])
            ->add('fr', null, [
                'label' => $this->translator->trans('French')
            ])
            ->add('whatsAppFr', null, [
                'label' => false,
                'help' => $this->translator->trans('Expression that appears when a representative uses the variable for WhatsApp text in French'),
            ])
            ->add('it', null, [
                'label' => $this->translator->trans('Italian')
            ])
            ->add('whatsAppIt', null, [
                'label' => false,
                'help' => $this->translator->trans('Expression that appears when a representative uses the variable for WhatsApp text in Italian'),
            ])
            ->add('po', null, [
                'label' => $this->translator->trans('Portuguese')
            ])
            ->add('whatsAppPo', null, [
                'label' => false,
                'help' => $this->translator->trans('Expression that appears when a representative uses the variable for WhatsApp text in Portuguese'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MeetingPoint::class,
        ]);
    }
}
