<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class PoolsController extends MainController
{

    /**
     * Show list of pools
     */
    public function poolsAction(Request $request)
    {
        return $this->render('TSControlBundle:Pools:pools.html.twig');
    }
}
