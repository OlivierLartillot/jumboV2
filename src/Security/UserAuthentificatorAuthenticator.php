<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class UserAuthentificatorAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public UserRepository $userRepository;
    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(Request $request): Passport
    {

        $username = $request->request->get('username', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new RememberMeBadge(),
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $this->userRepository->findOneBy([
            'username' => $request->request->get('username', '') 
        ]);

        $request->getSession()->set('_locale', $user->getLanguage());

        if ($user->isDeactivate()) {
            return new RedirectResponse($this->urlGenerator->generate('app_logout'));
        } ;

        // For example:
        if ((in_array('ROLE_HULK', $user->getRoles())) or 
        (in_array('ROLE_SUPERMAN', $user->getRoles())) or 
        (in_array('ROLE_AIRPORT_SUPERVISOR', $user->getRoles())) or
        (in_array('ROLE_API', $user->getRoles()))
        ) {
            return new RedirectResponse($this->urlGenerator->generate('home'));
        } elseif (in_array('ROLE_AIRPORT', $user->getRoles())){
            return new RedirectResponse($this->urlGenerator->generate('app_customer_card_airport'));
        } elseif (in_array('ROLE_REP', $user->getRoles())){
            return new RedirectResponse($this->urlGenerator->generate('app_admin_rep_replist'));
        } 
        return new RedirectResponse($this->urlGenerator->generate('app_customer_card_index'));
        
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
