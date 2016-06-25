<?php

namespace TS\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

use TS\NotificationBundle\Event\PlayerEvent;
use TS\NotificationBundle\NotificationEvents;
use TS\SiteBundle\Model\PlayerModel;
use TS\ApiBundle\Entity\Player;


class PlayerController extends MainController
{
    public function overviewAction(Request $request)
    {
        $players = $this->getDoctrine()
        	->getRepository('TSApiBundle:Player')
        	->getAllPlayersFullInfo($this->tournament);
        	
        $templateArray = array(
        	'players' => $players,
        );
        
        if ($request->query->has('tableFilter')) {
        	// filter on player or group name
        	$templateArray['tableFilter'] = $request->query->get('tableFilter');
        }
        if ($request->query->has('tableFilterColumn')) {
        	// filter on column number
        	$templateArray['tableFilterColumn'] = $request->query->get('tableFilterColumn');
        }
                
        return $this->render('TSSiteBundle:Player:overview.html.twig', $templateArray);
    }
    
    /**
      * Create a new player
      */
    public function createAction(Request $request)
    {	
        $playerModel = new PlayerModel($this->tournament, $this->container);
        if ($playerModel->isRegistrationClosed()) {
            return $this->render('TSSiteBundle:Player:registrationClosed.html.twig', array(
                'tournament' => $this->tournament,
            ));
        }

        // check for create access
        if ((false === $this->get('security.context')->isGranted("CREATE", new Player())) && (!$request->query->has("anonymous"))) {
            // not logged in -> show registration login form
            $form = $this->createFormBuilder()
                ->add('captcha', 'captcha', array(
                    'label' => false,
                    'as_file' => true,
                ))
                ->getForm();
             $form->handleRequest($request);

            if ($form->isValid()) {
                // register for tournament without account
                return $this->redirect($this->generateUrl('player_create', array(
                    "tournamentUrl" => $this->tournament->getUrl(),
                    "anonymous" => true,
                )));
            } else {
                // show registration login form
                $targetPath = $this->generateUrl('player_create', array('tournamentUrl'=>$this->tournament->getUrl()), true);
                $this->get('session')->set('_security.main.target_path', $targetPath);

                // render registration form
                $registerResponse = $this->forward('FOSUserBundle:Registration:register', array('request' => $request));
                if ($registerResponse->isRedirection()) {
                    // redirect from registration
                    return $registerResponse;
                } else if ($request->getMethod() == 'POST') {
                    $this->get('session')->getFlashBag()->add('error', 'The registration form has errors, please scroll down and check your entered values');
                }

                return $this->render('TSSiteBundle:Player:registrationLogin.html.twig', array(
                    'tournament' => $this->tournament,
                    'form' => $form->createView(),
                    'registrationForm' => $registerResponse->getContent(),
                ));
            }
        }
        
        $playerRes = $playerModel->createPlayer($request);
        $financialProducts = $playerModel->getFinancialProductsById();
        $this->addStatisticsToTemplate();
        if ($playerRes['redirectToCart']) {
            return $this->redirect($this->generateUrl('sylius_cart_summary'));
        } else if ($playerRes['changed']) {
        	return $this->redirect($this->generateUrl('player_overview', array('tournamentUrl'=> $this->tournament->getUrl())));
        } else {
        	return $this->render('TSSiteBundle:Player:edit.html.twig', array(
		    	'player' => $playerRes['player'],
		        'form' => $playerRes['form']->createView(),
                'isOrganizer' => $this->userIsOrganizer(),
                'anonymous' => $request->query->has("anonymous"),
                'financialProducts' => $financialProducts,
		    ));
        }
    }
    
    /**
	 * Edit player information
	 */
    public function editAction($playerId, Request $request)
    {
        $playerModel = new PlayerModel($this->tournament, $this->container);
        $playerRes = $playerModel->editPlayer($playerId, $request);
        $financialProducts = $playerModel->getFinancialProductsById();

        $this->addStatisticsToTemplate();
        if ($playerRes['redirectToCart']) {
            return $this->redirect($this->generateUrl('sylius_cart_summary'));
        } else if ($playerRes['changed']) {
        	return $this->redirect($this->generateUrl('player_overview', array('tournamentUrl'=> $this->tournament->getUrl())));
        } else {
        	return $this->render('TSSiteBundle:Player:edit.html.twig', array(
		    	'player' => $playerRes['player'],
		        'form' => $playerRes['form']->createView(),
                'isOrganizer' => $this->userIsOrganizer(),
                'financialProducts' => $financialProducts,
		    ));
        }
	}

    private function userIsOrganizer() {
        return is_object($this->getUser()) && ($this->tournament->getOrganizerPersons()->contains($this->getUser()->getPerson()) || $this->getUser()->isAdmin());
    }
    
    /**
      * Delete a player
      */
    public function deleteAction($playerId)
    {	
        /** @var \TS\ApiBundle\Entity\Player $player */
        $player = $this->getDoctrine()
        	->getRepository('TSApiBundle:Player')
        	->find($playerId);
	    if (!$player) {
	        throw $this->createNotFoundException('No player found for id '.$playerId);
	    }
	    
	    // check for delete access
        if (false === $this->get('security.context')->isGranted("DELETE", $player)) {
            throw new AccessDeniedException();
        }

        $findBoughtProduct = $this->getDoctrine()
            ->getRepository('TSFinancialBundle:BoughtProduct')
            ->findOneBy(array("player" => $player));
        if ($findBoughtProduct) {
            $this->get('session')->getFlashBag()->add('error', 'This player has financial transactions, and therefore cannot be removed. An alternative is to change the player\'s status to cancelled.');
        } else {
            // Create delete player event
            $event = new PlayerEvent($player);
            $this->container->get('event_dispatcher')->dispatch(NotificationEvents::PLAYER_DELETE_BEFORE, $event);

            $em = $this->getDoctrine()->getManager();

            $cartItems = $this->getDoctrine()
                ->getRepository('TSFinancialBundle:CartItem')
                ->findBy(array("player" => $player));
            foreach ($cartItems as $cartItem) {
                $em->remove($cartItem);
            }
            foreach ($player->getDisciplinePlayers() as $disciplinePlayer) {
                $em->remove($disciplinePlayer);
            }
            foreach ($player->getRegistrationFormValues() as $formValue) {
                $em->remove($formValue);
            }
            $em->remove($player);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Player deleted');
        }

	    return $this->redirect($this->generateUrl('player_overview', array('tournamentUrl'=> $this->tournament->getUrl())));
    }
      
}
