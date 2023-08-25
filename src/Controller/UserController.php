<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditPasswordType;
use App\Form\UserEditType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('')]
class UserController extends AbstractController
{
    #[Route('team-manager/user/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('team-manager/user/list', name: 'app_user_list', methods: ['GET'])]
    public function list(UserRepository $userRepository): Response
    {

        return $this->render('user/list.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('team-manager/user/disabled-users-list/', name: 'app_user_list_deactivate', methods: ['GET'])]
    public function listDeactivateUsers(UserRepository $userRepository): Response
    {

        return $this->render('user/list-disabled-users.html.twig', [
            'users' => $userRepository->findBy([
                'deactivate' => true
            ]),
        ]);
    }

    #[Route('team-manager/user/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $hashedPassword = $hasher->hashPassword(
                $user, 
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
   

            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('team-manager/user/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('team-manager/user/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/edit-my-profile/{id}', name: 'app_user_edit_my_profile', methods: ['GET', 'POST'])]
    public function editmyProfile(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $hasher): Response
    {

        // si la personne qui essaie de modifier le profil n'est pas la personne connectée alors tu émet une erreur
        if ($user != $this->getUser()) {
            return throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserEditPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            $newHashedPassword = $hasher->hashPassword(
                $user, 
                $user->getPassword()
            ); 
    
            $userRepository->upgradePassword($user,  $newHashedPassword);



            $userRepository->save($user, true);

            $this->addFlash(
                'success',
                'Your changes were saved !'
            );


            return $this->redirectToRoute('app_user_edit_my_profile', ['id' =>  $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit_my_profile.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('team-manager/user/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
