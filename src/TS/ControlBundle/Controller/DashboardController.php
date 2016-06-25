<?php

namespace TS\ControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class DashboardController extends MainController
{
    
    /**
	 * Show statistics on dashboard
	 */
    public function dashboardAction(Request $request)
    {  
        $points = array();
        foreach ($this->tournament->getPlayers() as $player) {
        	$date = $player->getRegistrationDate()->setTime(5, 0, 0)->getTimestamp();
        	if (array_key_exists($date, $points)) {
        		$points[$date] += 1;
        	} else {
        		$points[$date] = 1;
        	}
        }
        
        $cumulativePoints = array();
        $previous = 0;
        ksort($points);
        foreach ($points as $key=>$value) {
        	$previous = $value + $previous;
        	$cumulativePoints[$key] = $previous;
        }
        
        
	    return $this->render('TSControlBundle:Dashboard:dashboard.html.twig', array(
	        'points' => $points,
	        'cumulativePoints' => $cumulativePoints,
	    ));
	}
}
