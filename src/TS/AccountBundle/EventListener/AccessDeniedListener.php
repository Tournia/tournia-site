<?php

namespace TS\AccountBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedListener implements AccessDeniedHandlerInterface
{
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $session = $request->getSession();
        $session->getFlashBag()->add('error', 'You are not authorized to view this page, please log in with different credentials');
        return new RedirectResponse($this->router->generate('account_login'));
    }
}