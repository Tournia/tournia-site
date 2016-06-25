<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class MatchesPlanController extends MainController
{
	
	
	/**
	  * Show list of matches in discipline
	  */
	public function indexAction(Request $request)
	{	
		return $this->render('TSControlBundle:Matches:planMatches.html.twig');
	}
}