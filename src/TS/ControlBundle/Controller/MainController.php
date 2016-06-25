<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use TS\ApiBundle\Entity\Tournament;

abstract class MainController extends Controller
{
    /** @var Tournament $tournament */
    protected $tournament;
    
    public function setTournament($tournamentUrl) {
    	$tournament = $this->getDoctrine()
        	->getRepository('TSApiBundle:Tournament')
        	->findOneByUrl($tournamentUrl);
	    if (!$tournament) {
	        throw $this->createNotFoundException('No tournament found for url '.$tournamentUrl);
	    }
	    
	    // check for edit access
        if (false === $this->get('security.context')->isGranted("EDIT", $tournament)) {
            throw new AccessDeniedException();
        }

        $this->tournament = $tournament;
        $this->get('twig')->addGlobal('tournament', $this->tournament);

        // setting currency of payments
        $this->get('session')->set('currency', $this->tournament->getPaymentCurrency());
    }
}
