<?php

namespace TS\LiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    protected $tournament;
    protected $site;

    public function mobileAction()
    {
        $tournamentUrl = $this->container->get('request')->get('tournamentUrl', '');
        return $this->redirect($this->generateUrl('ts_root') .'live/t/'. $tournamentUrl .'/matches');
    }

    public function presentationScreenAction()
    {
        $tournamentUrl = $this->container->get('request')->get('tournamentUrl', '');
        $this->setTournament($tournamentUrl);

        return $this->render('TSLiveBundle:Default:presentationscreen.html.twig');
    }

    public function setTournament($tournamentUrl) {
    	$tournament = $this->getDoctrine()
        	->getRepository('TSApiBundle:Tournament')
        	->findOneByUrl($tournamentUrl);
	    if (!$tournament) {
	        throw $this->createNotFoundException('No tournament found for url '.$tournamentUrl);
	    }
	    
	    // check for view access
        if (false === $this->get('security.authorization_checker')->isGranted("VIEW", $tournament)) {
            throw new AccessDeniedException();
        }

        $this->tournament = $tournament;
        $this->site = $tournament->getSite();
        $this->get('twig')->addGlobal('tournament', $this->tournament);
        $this->get('twig')->addGlobal('site', $this->site);
    }					

}
