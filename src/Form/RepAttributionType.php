<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepAttributionType extends AbstractType
{

    private $userRepository;

    public function __construct(UserRepository  $userRepository){
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $repList = [];
        // trouve le skip pour le mettre en premier
        $skip =  $this->userRepository->findOneBy(['username' => 'skip']);
        if ($skip != null) {
            $repList[] = $skip; 
        }
        $users = $this->userRepository->findBy([], ['username' => 'ASC']);
        foreach ($users as $user) {
            if (in_array('ROLE_REP', $user->getRoles())) {
                // mais si $user = skip tu le sautes !!!
                if ($user->getUsername() != 'skip' ) { 
                    $repList[] = $user;
                }
            }
        }
   

        $builder
        ->add('staff', EntityType::class, [
            'label' => "Reps",
            'class' => User::class,
            'autocomplete' =>false,
            'choices' => $repList,
            'data' => $repList[0]
         
        ] )
        ->add('validate', SubmitType::class, [
            'attr' => ['class' => 'btn btn-primary'],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
