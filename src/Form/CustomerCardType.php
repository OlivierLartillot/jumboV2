<?php

namespace App\Form;

use App\Entity\CustomerCard;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerCardType extends AbstractType
{
    private $userRepository;


    public function __construct(UserRepository  $userRepository){
        $this->userRepository = $userRepository;
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


        $builder
            ->add('reservationNumber', null, [
                'disabled' => true,
            ] )
            ->add('jumboNumber', null, [
                'disabled' => true,
            ])
            ->add('holder', null, [
                'label' => 'Full Name',
            ])
            ->add('agency')
            ->add('reservationCancelled')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerCard::class,
        ]);
    }
}
