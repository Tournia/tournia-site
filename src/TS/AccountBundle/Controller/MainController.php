<?php

namespace TS\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class MainController extends Controller
{
    /** @var \TS\ApiBundle\Entity\Person $person */
    protected $person;

    public function setPerson() {
        // check for access
        if (!is_object($this->getUser()) || !is_object($this->getUser()->getPerson())) {
            throw new AccessDeniedException();
        }

        $this->person = $this->getUser()->getPerson();
        $this->get('twig')->addGlobal('person', $this->person);
    }
}
