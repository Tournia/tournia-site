<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class TeamsController extends MainController
{
	
	/**
      * Shows all players in disciplines and teams
      */
    public function indexAction()
    {   
	    return $this->render('TSControlBundle:Teams:teams.html.twig');
    }
}
