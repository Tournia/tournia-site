<?php

namespace TS\AccountBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

/**
 * Listener responsible to redirect new social media accounts to edit profile page
 */
class SocialLoginListener implements AuthenticationSuccessHandlerInterface
{
    
    private $router;
    private $container;

    public function __construct(UrlGeneratorInterface $router, Container $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        $user = $token->getUser();
        $this->container->get('session')->getFlashBag()->add('success', 'You are now logged in, '. $user->getPerson()->getName());
        $newSocialUser = $this->container->get('session')->get('newSocialUser', false);
        if ($newSocialUser) {
            $this->container->get('session')->remove('newSocialUser');
        }
        if (!is_null($user) && $newSocialUser && ($user->getMethod() != 'email')) {
            // if the user is a new social user -> show edit profile page
            $this->container->get('session')->getFlashBag()->add('info', 'Please check your name and email address in order to receive tournament information');
            return new RedirectResponse($this->router->generate('account_settings_profile'));
        } else if ($targetPath = $request->getSession()->get('_security.main.target_path')) {
            return new RedirectResponse($targetPath);
        } else {
            // return default
            return new RedirectResponse($this->router->generate('front_index'));
        }

    }
}