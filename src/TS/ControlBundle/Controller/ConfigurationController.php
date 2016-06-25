<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class ConfigurationController extends MainController
{
	
    
    /**
      * Show list of messages
      */
    public function messagesAction(Request $request)
    {    
	    return $this->render('TSControlBundle:Configuration:messages.html.twig');
    }
    
    /**
      * Show list of locations
      */
    public function locationsAction(Request $request)
    {    
	    return $this->render('TSControlBundle:Configuration:locations.html.twig');
    }

    /**
      * Show financial configuration
      */
    public function financialAction(Request $request)
    {    
        $form = $this->get('form.factory')->create('sylius_product');
        return $this->render('TSControlBundle:Configuration:financial.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
