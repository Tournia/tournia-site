<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AnnouncementsController extends MainController
{
	
	
	/**
	  * Show list to announcements
	  */
	public function indexAction(Request $request)
	{	
		return $this->render('TSControlBundle:Announcements:announcements.html.twig');
	}
}