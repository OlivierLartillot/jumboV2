<?php
// src/EventSubscriber/LoginTimeSubscriber.php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginTimeSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private ContainerInterface $container;
    private KernelInterface $kernel;
    private UrlGeneratorInterface $router;

        public function __construct(TokenStorageInterface $tokenStorage, ContainerInterface $container, UrlGeneratorInterface $router, KernelInterface $kernel)
    {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->router = $router;
        $this->kernel = $kernel;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $route = $event->getRequest()->get('_route');
        $token = $this->tokenStorage->getToken();


        // Exclure certaines routes du traitement
        /*         
        $excludedRoutes = ['_wdt', '_profiler'];
        if (in_array($request->attributes->get('_route'), $excludedRoutes)) {
            return;
        } */

        $environment = $this->kernel->getEnvironment();

        if ($environment === 'dev') {
            return;
        } 


        // Vérifiez si l'utilisateur est connecté
        if ($token && $token->getUser()) {

            $user = $token->getUser();
            // Vérifiez les rôles de l'utilisateur
            if ((in_array('ROLE_REP', $user->getRoles(), true)) or (in_array('ROLE_AIRPORT', $user->getRoles(), true))) {
                
                // Vérifiez si le moment de la connexion a été défini
                if (!$token->hasAttribute('_security_admin_login_time')) {
                    // Si non, enregistrez le moment de la connexion dans le token
                    $token->setAttribute('_security_admin_login_time', time());
                } else {
                    // Si oui, vérifiez si la durée maximale est écoulée (30 secondes dans cet exemple)
                    $maxDuration = 64800; // Durée en secondes
                    $loginTime = $token->getAttribute('_security_admin_login_time');
                    $currentTime = time();

                    if (($currentTime - $loginTime) > $maxDuration) {
                        // Si la durée maximale est dépassée, déconnectez l'utilisateur
                        //$this->tokenStorage->setToken(null);
                        // Créez une réponse de redirection vers une URL spécifique
                        $redirectUrl = $this->router->generate('app_logout');

                        // Créez une réponse de redirection vers l'URL générée
                        $response = new RedirectResponse($redirectUrl);

                        // Assignez la réponse à l'événement
                        $event->setResponse($response);                
                    }
                }
            }
        } else {

            if ($route != 'app_login') {

                if ($route == 'app_api_public_test') { return ;}
                if ($request->getRequestUri() === "/api/login_check") { return;}

                $redirectUrl = $this->router->generate('app_login');
                // Créez une réponse de redirection vers l'URL générée
                $response = new RedirectResponse($redirectUrl);

                // Assignez la réponse à l'événement
                $event->setResponse($response);  
            }
              
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
