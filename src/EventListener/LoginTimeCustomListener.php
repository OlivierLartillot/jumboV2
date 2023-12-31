<?php
namespace App\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginTimeCustomListener
{
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        // Vérifiez les rôles de l'utilisateur
        if ( (in_array('ROLE_REP', $user->getRoles())) or (in_array('ROLE_AIRPORT', $user->getRoles())) ) {
            // Augmentez la durée de session pour les utilisateurs avec le rôle ROLE_ADMIN
            $event->getRequest()->getSession()->setMetadata('_security_main', serialize([
                'lifetime' => 2592000, // 30 jours en secondes
            ]));
        }
    }
}