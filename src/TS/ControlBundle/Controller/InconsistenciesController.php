<?php

namespace TS\ControlBundle\Controller;


class InconsistenciesController extends MainController
{

    /**
     * Shows all players in disciplines and teams
     */
    public function inconsistenciesAction()
    {
        return $this->render('TSControlBundle:Inconsistencies:inconsistencies.html.twig');
    }
}
