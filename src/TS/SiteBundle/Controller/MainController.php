<?php

namespace TS\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TS\ApiBundle\Entity\Tournament;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class MainController extends Controller
{
    
    /* @var \TS\ApiBundle\Entity\Tournament $tournament */
    protected $tournament;
    protected $site;
    
    public function setTournament($tournamentUrl) {
    	$tournament = $this->getDoctrine()
        	->getRepository('TSApiBundle:Tournament')
        	->findOneByUrl($tournamentUrl);
	    if (!$tournament) {
	        throw $this->createNotFoundException('No tournament found for url '.$tournamentUrl);
	    }
	    
	    // check for view access
        if (false === $this->get('security.context')->isGranted("VIEW", $tournament)) {
            throw new AccessDeniedException();
        }

        $this->tournament = $tournament;
        $this->site = $tournament->getSite();
        $this->get('twig')->addGlobal('tournament', $this->tournament);
        $this->get('twig')->addGlobal('site', $this->site);

        // setting currency of payments
        $this->get('session')->set('currency', $this->tournament->getPaymentCurrency());

        // check for missing site
        if (is_null($this->tournament->getSite())) {
            throw $this->createNotFoundException('The tournament '. $this->tournament->getName() .' has no website');
        }
        // check for unpublished site
        if (!$this->tournament->getSite()->getIsPublished()) {
            if (false === $this->get('security.context')->isGranted("EDIT", $tournament)) {
                throw $this->createNotFoundException('The tournament '. $this->tournament->getName() .' is not online yet');
            } else {
                $publishUrl = $this->generateUrl('settings_site_publish', array('tournamentUrl'=>$this->tournament->getUrl()));
                $this->get('session')->getFlashBag()->add('error', 'This website is unpublished and therefore not visible for other people. <a href="'. $publishUrl .'">Publish</a>');
            }
        }

    }

    /**
     * Add statistics data to template, which is necessary for showInfoBlock
     */
    public function addStatisticsToTemplate() {
        $this->container->get('twig')->addGlobal('statistics', $this->getStatistics($this->tournament));
    }

    // generate array with statistics data
    private function getStatistics($tournament) {
        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Player');
        $queryObject = $repository->createQueryBuilder('p')
            ->select("COUNT(p.gender), p.gender")
            ->groupBy("p.gender")
            ->where("p.tournament = :tournament")
            ->setParameter("tournament", $tournament)
            ->getQuery();
        $playerGenders = $queryObject->getResult();

        $res = array(
            "unique" => array(
                "male" => 0,
                "female" => 0,
                "total" => 0,
            )
        );
        foreach ($playerGenders as $gender) {
            if ($gender['gender'] == 'M') {
                $res['unique']['male'] = $gender[1];
            } else {
                $res['unique']['female'] = $gender[1];
            }
        }
        $res['unique']['total'] = $res['unique']['male'] + $res['unique']['female'];
        return $res;
    }
}
