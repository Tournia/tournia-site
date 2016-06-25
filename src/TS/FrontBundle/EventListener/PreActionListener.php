<?php
namespace TS\FrontBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use TS\FrontBundle\Controller\MainController;

class PreActionListener
{
    private $container;
    private $hasSetUpcomingTournaments = false;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container     = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {    
        if (!$this->hasSetUpcomingTournaments) {
            // set upcomingTournaments in twig
            $repository = $this->container->get('doctrine')
                ->getRepository('TSApiBundle:Tournament');
            $query = $repository->createQueryBuilder('t')
                ->orderBy('t.id', 'DESC')
                ->setMaxResults('3')
                ->getQuery();
            $upcomingTournaments = $query->getResult();
            $this->container->get('twig')->addGlobal('upcomingTournaments', $upcomingTournaments);
            $this->hasSetUpcomingTournaments = true;
        }
    }
}