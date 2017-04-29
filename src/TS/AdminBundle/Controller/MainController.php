<?php

namespace TS\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class MainController extends Controller
{

    /**
     * Check for admin access
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    protected function checkAccess() {
        // check for edit access
        if (false === $this->get('security.authorization_checker')->isGranted("ROLE_ADMIN")) {
            throw $this->createAccessDeniedException("You're not authorized to access this page!");
        }
    }
}
