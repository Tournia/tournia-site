<?php

namespace TS\ControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use TS\SiteBundle\Form\DataTransformer\MultipleStatusToNumberTransformer;
use Symfony\Component\Validator\Constraints\NotNull;
use TS\SiteBundle\Model\PlayerModel;


class PlayerController extends MainController
{

    /**
     * Show list of players
     */
    public function overviewAction(Request $request)
    {
        return $this->render('TSControlBundle:Player:playersOverview.html.twig');
    }
    
    /**
      * Show list of players including the information which discipline is registered and which pool is playing
      */
    public function poolsAction(Request $request)
    {
        $formBuilder = $this->createFormBuilder();
		$transformer = new MultipleStatusToNumberTransformer($this->tournament);
        $formBuilder->add(
	        	$formBuilder->create('status', 'choice', array(
		        	'choices'   => $this->tournament->getStatusOptions(),
		        	'label' => 'Status',
		        	'required' => true,
		        	'expanded' => true,
		        	'multiple' => true,
		        	'mapped' => false,
		        	'constraints' => new NotNull(array('message' => 'Select at least one status')),
		        ))->addModelTransformer($transformer)
		    );
		$form = $formBuilder->getForm();
	    	    
	    return $this->render('TSControlBundle:Player:playersInPools.html.twig', array(
	        'form' => $form->createView(),
	    ));
    }

    /**
	 * Show and edit player information
	 */
    public function infoAction($playerId, Request $request)
    {   
        $playerModel = new PlayerModel($this->tournament, $this->container);
        $playerRes = $playerModel->editPlayer($playerId, $request);
        $financialProducts = $playerModel->getFinancialProductsById();
        $registrationGroupContactPlayers = $this->getDoctrine()
        	->getRepository('TSApiBundle:RegistrationGroup')
        	->getAllContactPlayers($playerRes['player']->getRegistrationGroup());

        if ($playerRes['redirectToCart']) {
            return $this->redirect($this->generateUrl('sylius_cart_summary'));
        } else {
            return $this->render('TSControlBundle:Player:playerInfo.html.twig', array(
                'player' => $playerRes['player'],
                'registrationGroupContactPlayers' => $registrationGroupContactPlayers,
                'form' => $playerRes['form']->createView(),
                'financialProducts' => $financialProducts,
            ));
        }
	}

    /**
     * Create a new player
     */
    public function createAction(Request $request)
    {
        $playerModel = new PlayerModel($this->tournament, $this->container);
        $playerRes = $playerModel->createPlayer($request);
        $financialProducts = $playerModel->getFinancialProductsById();

        if ($playerRes['changed']) {
            return $this->redirect($this->generateUrl('control_player_overview', array('tournamentUrl'=> $this->tournament->getUrl())));
        } else {
            return $this->render('TSControlBundle:Player:playerCreate.html.twig', array(
                'form' => $playerRes['form']->createView(),
                'player' => $playerRes['player'],
                'financialProducts' => $financialProducts,
            ));
        }
    }
    
}
