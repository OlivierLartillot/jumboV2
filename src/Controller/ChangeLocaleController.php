<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChangeLocaleController extends AbstractController
{
    #[Route('/change_locale/{locale}', name: 'app_change_locale')]
    public function changeLocale($locale, Request $request, EntityManagerInterface $em): Response
    {


        $currentUser = $this->getUser();
        $currentUser->setLanguage($locale);
        $em->flush($currentUser);
        
        // On stocke la langue dans la session
        $request->getSession()->set('_locale', $locale); 

        // On revient sur la page précédente
        return $this->redirect($request->headers->get('referer'));
    }
}