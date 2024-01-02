<?php
// src/EventSubscriber/LoginTimeSubscriber.php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginTimeSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private ContainerInterface $container;

    public function __construct(TokenStorageInterface $tokenStorage, ContainerInterface $container)
    {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        

        // Vérifiez si l'utilisateur est connecté
        if ($token && $token->getUser()) {
            $user = $token->getUser();
            // Vérifiez les rôles de l'utilisateur
            if (in_array('ROLE_REP', $user->getRoles(), true)) {
                
                // Vérifiez si le moment de la connexion a été défini
                if (!$token->hasAttribute('_security_admin_login_time')) {
                    // Si non, enregistrez le moment de la connexion dans le token
                    $token->setAttribute('_security_admin_login_time', time());
                } else {
                    // Si oui, vérifiez si la durée maximale est écoulée (30 secondes dans cet exemple)
                    $maxDuration = 05; // Durée en secondes
                    $loginTime = $token->getAttribute('_security_admin_login_time');
                    $currentTime = time();

                    if (($currentTime - $loginTime) > $maxDuration) {
                        // Si la durée maximale est dépassée, déconnectez l'utilisateur
                        $this->tokenStorage->setToken(null);
                    }
                }

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
