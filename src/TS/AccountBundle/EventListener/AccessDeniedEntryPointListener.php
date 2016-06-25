<?php

namespace TS\AccountBundle\EventListener;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface,
    Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AccessDeniedEntryPointListener implements AuthenticationEntryPointInterface
{
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $session = $request->getSession();
        if ($request->getRequestFormat() == "json") {
            // if the request is json, return a 403 response without a redirect (otherwise jquery ajax will not be able to recognize the 403)
            $response = new Response();
            $response->setStatusCode(403);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            $session->getFlashBag()->add('info', 'You are not authorized to view this page, please log in');
            return new RedirectResponse($this->router->generate('account_login'));
        }
    }
}