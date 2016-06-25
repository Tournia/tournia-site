<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RankingController extends MainController
{
	
    
    /**
      * Show ranking of teams in pool
      */
    public function poolAction()
    {    
	    return $this->render('TSControlBundle:Ranking:poolRanking.html.twig');
    }
    
    /**
      * Show ranking of all players
      */
    public function playersAction()
    {    
	    return $this->render('TSControlBundle:Ranking:playersRanking.html.twig');
    }
    
    /**
      * Show ranking of all groups
      */
    public function groupsAction()
    {    
	    return $this->render('TSControlBundle:Ranking:groupsRanking.html.twig');
    }
	
	/**
      * Show ranking of teams in pool
      */
    public function poolWinnersAction()
    {    
	    return $this->render('TSControlBundle:Ranking:poolWinners.html.twig');
    }
}