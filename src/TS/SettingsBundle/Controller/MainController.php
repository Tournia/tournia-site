<?php

namespace TS\SettingsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TS\ApiBundle\Entity\Tournament;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class MainController extends Controller
{
    /** @var \TS\ApiBundle\Entity\Tournament $tournament */
    protected $tournament;
    
    public function setTournament($tournamentUrl) {
    	$newTournament = $tournamentUrl == "new";
        if ($newTournament) {
            $tournament = new Tournament();
            $tournament->setUrl('new');
        } else {
            $tournament = $this->getDoctrine()
            	->getRepository('TSApiBundle:Tournament')
            	->findOneByUrl($tournamentUrl);
    	    if (!$tournament) {
    	        throw $this->createNotFoundException('No tournament found for url '.$tournamentUrl);
    	    }
        }
	    
	    // check for edit access
        if (!$newTournament && (false === $this->get('security.authorization_checker')->isGranted("EDIT", $tournament))) {
            throw new AccessDeniedException();
        }

        $this->tournament = $tournament;
        $this->get('twig')->addGlobal('tournament', $this->tournament);
        $this->get('twig')->addGlobal('newTournament', $newTournament);

        // setting currency of payments
        $this->get('session')->set('currency', $this->tournament->getPaymentCurrency());
    }
}
