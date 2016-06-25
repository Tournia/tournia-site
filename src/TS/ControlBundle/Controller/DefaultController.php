<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends MainController
{
    public function indexAction()
    {
        return $this->render('TSControlBundle::index.html.twig', array('tournament'=> $this->tournament));
    }
}
