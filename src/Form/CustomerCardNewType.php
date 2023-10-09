<?php

namespace App\Form;

use App\Entity\CustomerCard;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerCardNewType extends AbstractType
{
    private $userRepository;
    private $security;

    public function __construct(UserRepository  $userRepository, Security $security){
        $this->userRepository = $userRepository;
        $this->security = $security;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $repList = [];
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            if (in_array('ROLE_REP', $user->getRoles())) {
                $repList[] = $user;
            }
        }
        $currentUser = $this->security->getUser();

        $builder
            ->add('reservationNumber', null, [
            ] )
            ->add('jumboNumber', null, [
            ])
            ->add('holder', null, [
                'label' => 'Full Name',
            ])
            ->add('agency')
            ->add('adultsNumber', null, [
                'label' => 'Adults quantity',
            ])
            ->add('childrenNumber', null, [
                'label' => 'Children quantity',
            ])
            ->add('babiesNumber', null, [
                'label' => 'Babies quantity',
            ])
            ->add('status', null, [
                'attr' => ['hidden' => true], 
                'label' => false
            ])
            ->add('meetingPoint', null, [
                'required' => true,
                'attr' => ['hidden' => true], 
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerCard::class,
        ]);
    }
}
