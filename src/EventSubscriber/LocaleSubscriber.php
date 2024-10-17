<?php
// src/EventSubscriber/LocaleSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;

    public function __construct(string $defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }
       
        // On vérifie si la langue est passée en paramètre de l'URL
        if ($locale = $request->query->get('_locale')) {
           $request->setLocale($locale);
        } else if ($request->getSession()->get('_locale') != null){
            // Sinon on utilise celle de la session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        } else {
            $request->setLocale( $this->defaultLocale);
        }
    }

    public static function getSubscribedEvents():array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}