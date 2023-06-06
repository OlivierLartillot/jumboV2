<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class LanguageController extends AbstractController
{
    /**
     * @Route("/lang/{_locale}", requirements={
     *     "_locale"="en|es|de|it|ru"
     * })
    */
    public function index(Request $request) {
        $locale = $request->getLocale();
        $request->setLocale($locale);
        $request->getSession()->set('_locale', $locale);
        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }
}
