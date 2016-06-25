<?php

namespace TS\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;


class LiveController extends MainController
{
    
    /**
	 * Show index of live
	 */
    public function indexAction(Request $request)
    {
        if (!$this->tournament->getAuthorization()->isApiAllowed() && (false === $this->get('security.context')->isGranted("EDIT", $this->tournament))) {
        	// live is closed (although it is never closed for organizers)
        	return $this->render('TSSiteBundle:Live:closed.html.twig');
        }
        
        // check if password has been given
        $session = $request->getSession();
        $livePassword = $this->tournament->getAuthorization()->getLivePassword();
        
        $formPassword = $request->request->get('livePassword', '');
        if ($formPassword != '') {
            // attempted login
	        if ($formPassword == $livePassword) {
				// live password correct
                $session->set('hasLiveAccess', $this->tournament->getUrl());
			} else {
				// incorrect password
				$this->get('session')->getFlashBag()->add('error', 'Incorrect password');
			}
		}

        if ($session->get('hasLiveAccess', false) != $this->tournament->getUrl()) {
            // no Live access (yet)
            if (is_object($this->getUser())) {
                // user is logged in -> has Live access
                $session->set('hasLiveAccess', $this->tournament->getUrl());
            } else if ($livePassword == '') {
                // no Live password -> allow Live access
                $session->set('hasLiveAccess', $this->tournament->getUrl());
            }
		}

        if ($session->get('hasLiveAccess', false) == $this->tournament->getUrl()) {
			// Live is allowed -> show current matches (first tab)
            if ($this->isMobileDevice($request) && $this->mobileWebsiteIsWanted($request)) {
                // Forward to mobile website
                return $this->forward('TSLiveBundle:Default:mobile', array('tournamentUrl' => $this->tournament->getUrl()));
            } else {
                // Redirect to desktop website
    			return $this->redirect($this->generateUrl('live_overview', array('tournamentUrl' => $this->tournament->getUrl())));
            }
		} else {
            // ask for password
            return $this->render('TSSiteBundle:Live:login.html.twig');
        }
	}
	
	/**
      * Show overview of live options
      */
    public function overviewAction(Request $request)
    {    
	    return $this->render('TSSiteBundle:Live:overview.html.twig');
    }
	
    /**
      * Show list of current matches
      */
    public function currentMatchesAction(Request $request)
    {    
	    return $this->render('TSSiteBundle:Live:currentMatches.html.twig');
    }
    
    /**
      * Show list of upcoming matches
      */
    public function upcomingMatchesAction(Request $request)
    {    
	    return $this->render('TSSiteBundle:Live:upcomingMatches.html.twig');
    }
    
    /**
      * Show list of all matches
      */
    public function allMatchesAction(Request $request)
    {    
	    return $this->render('TSSiteBundle:Live:allMatches.html.twig');
    }
    
    /**
      * Show list ranking of pool
      */
    public function rankingPoolAction()
    {    
	    return $this->render('TSSiteBundle:Live:rankingPool.html.twig');
    }
    
    /**
      * Show list ranking of players
      */
    public function rankingPlayersAction()
    {    
	    return $this->render('TSSiteBundle:Live:rankingPlayers.html.twig');
    }
    
    /**
      * Show list ranking of groups
      */
    public function rankingGroupsAction()
    {    
	    return $this->render('TSSiteBundle:Live:rankingGroups.html.twig');
    }
    
    /**
      * Show results of a player
      */
    public function playerAction(Request $request)
    {    
	    return $this->render('TSSiteBundle:Live:player.html.twig');
    }
    
    /**
      * Check based on header whether it's a mobile device
      * @return boolean 
      */
    private function isMobileDevice(Request $request)
    {
        if($request->get('_route') == "live_index")
        {
            if (preg_match('/(android|blackberry|iphone|ipad|phone|playbook|mobile)/i', $request->headers->get('user-agent')))
            {
                //ONLY AFFECT HTML REQUESTS
                //THIS ENSURES THAT YOUR JSON REQUESTS TO E.G. REST API, DO NOT GET SERVED TEXT/HTML CONTENT-TYPE
                if ($request->getRequestFormat() == "html")
                {
                    $request->setRequestFormat('mobile');
                }

                return true;                
            }
        }

        return false;                
    }

    /**
      * Detect whether mobile version is requested
      * @return boolean
      */
    private function mobileWebsiteIsWanted(Request $request)
    {
        return !$request->query->has('desktop');             
    }
}
