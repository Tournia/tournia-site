<?php
namespace TS\ApiBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use TS\ApiBundle\Controller\v2\ApiV2MainController as AMCv2;

class ApiPreActionListener
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container     = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure. This is not usual in Symfony2 but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }
        
        if ($controller[0] instanceof AMCv2) {
            /* @var \Symfony\Component\HttpFoundation\Request $request */
        	$request = $this->container->get('request');
            $controller[0]->setRequest($request);
            $tournamentUrl = $request->get('tournamentUrl', null);
            if ($tournamentUrl != null) {
                $controller[0]->setTournament($tournamentUrl);
                $controller[0]->checkAuthorization();
            }
        }
    }
}