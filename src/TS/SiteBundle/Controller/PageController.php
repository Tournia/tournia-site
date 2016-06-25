<?php

namespace TS\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;


class PageController extends MainController
{
    /**
      * Show first (default) page
      */
    public function indexAction() {
        $repository = $this->getDoctrine()->getRepository('TSSiteBundle:SitePage');
        $query = $repository->createQueryBuilder('p')
            ->where('p.site = :site')
            ->setParameter('site', $this->site)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery();
        try {
            $sitePage = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            throw $this->createNotFoundException('No default page found');
        }

        return $this->redirect($this->generateUrl('website_page', array('tournamentUrl'=>$this->tournament->getUrl(), 'page'=>$sitePage->getUrl())));
    }

    public function pageAction($page)
    {
        $sitePage = $this->getDoctrine()
        	->getRepository('TSSiteBundle:SitePage')
        	->findOneBy(array('site'=>$this->site, 'url'=>$page));
	    if (!$sitePage) {
	        throw $this->createNotFoundException('No page found for url '.$page);
	    }

        if ($sitePage->getShowInfoBlock()) {
            $this->addStatisticsToTemplate();
        }

        return $this->render('TSSiteBundle:Page:page.html.twig', array(
            'sitePage' => $sitePage
        ));
    }
}
