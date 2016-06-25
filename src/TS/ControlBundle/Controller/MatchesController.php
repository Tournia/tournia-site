<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class MatchesController extends MainController
{
	
    
    /**
      * Show list to plan matches
      */
    public function planMatchesAction(Request $request)
    {    
	    return $this->render('TSControlBundle:Matches:planMatches.html.twig');
    }
    
    /**
      * Show list of current matches
      */
    public function currentMatchesAction(Request $request)
    {    
	    return $this->render('TSControlBundle:Matches:currentMatches.html.twig');
    }
    
    /**
      * Show list of all matches
      */
    public function allMatchesAction(Request $request)
    {    
	    return $this->render('TSControlBundle:Matches:allMatches.html.twig');
    }
}