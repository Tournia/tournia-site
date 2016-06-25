<?php

namespace TS\SiteBundle\Model;

use TS\ApiBundle\Entity\DisciplinePlayer;
use TS\ApiBundle\Entity\Player;
use TS\ApiBundle\Entity\RegistrationFormField;
use TS\ApiBundle\Entity\RegistrationFormValue;
use Symfony\Component\HttpFoundation\Request;
use TS\NotificationBundle\Event\PlayerEvent;
use TS\NotificationBundle\NotificationEvents;
use TS\SiteBundle\Form\Type\PlayerType;
use TS\AccountBundle\Model\AuthorizationModel;
use TS\FinancialBundle\Entity\CartItem;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TS\SiteBundle\EventListener\MailChanges;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Validator\Constraints\Email;


class PlayerModel
{
	/* @var \TS\ApiBundle\Entity\Tournament $tournament */
    private $tournament;
	private $container;
	private $securityContext;
	private $doctrine;
	private $formFactory;
	
    
    /**
     * Constructor
     */
    public function __construct($tournament, ContainerInterface $container)
    {
        $this->tournament = $tournament;
        $this->container = $container;
        $this->securityContext = $container->get('security.context');
        $this->doctrine = $container->get('doctrine');
        $this->formFactory = $container->get('form.factory');
    }
    

    /**
      * Check whether registration is closed
      * @return boolean Whether the registration is closed. If user has edit rights, the registration is always open
      */
    public function isRegistrationClosed() {
    	return !$this->tournament->getAuthorization()->isCreateRegistrationAllowed() && (false === $this->securityContext->isGranted("EDIT", $this->tournament));
    }
	
    
    /**
      * Create a new player
      * @return array(
      *		player => player info
      *		form => form elements
      * 	changed => true/false whether the player has been changed
      *		closed => true/fales whether registration is closed
      *     redirectToCart => true/false whether to redirect to cart to pay
      *	)
      */
    public function createPlayer(Request $request) {
    	$res = array("changed"=>false, "redirectToCart"=>false);

    	$player = new Player();
        
        $tournament = $this->tournament;
        
        $player->setTournament($tournament);
        $player->setStatus($tournament->getNewPlayerStatus());
        
        // set registration form fields
        foreach ($tournament->getRegistrationFormFields() as $field) {
        	if (!$field->getIsHidden()) {
                $formValue = new RegistrationFormValue();
                $formValue->setPlayer($player);
                $formValue->setField($field);
                $player->addRegistrationFormValue($formValue);
            }
        }
        
        $loggedinPerson = null;
        if (is_object($this->getUser())) {
        	$loggedinPerson = $this->getUser()->getPerson();
        }
        // see if person has already registered himself -> if so, than this registration will be for someone else
        $repository = $this->doctrine->getRepository('TSApiBundle:Player');

		$playerFound = $repository->findOneBy(array('tournament' => $tournament, 'person' => $loggedinPerson));
		$registrationForValue = ($playerFound) ? "else" : "me";
        $registrationEmailValue = is_null($player->getPerson()) ? '' : $player->getPerson()->getEmail();
        
        $form = $this->formFactory->create(new PlayerType($tournament, $this->securityContext, $registrationForValue, $registrationEmailValue), $player, array('em'=>$this->doctrine->getManager(), 'player'=>$player, 'tournament'=>$this->tournament));
        
        if ($request->isMethod('POST')) {
       		$form->handleRequest($request);

            // setting disciplines
            $nrDisciplines = 0;
            foreach ($tournament->getDisciplineTypes() as $disciplineType) {
                $selectedDiscipline = $form->get('discipline-'. $disciplineType->getId())->getData();
                if ($selectedDiscipline) {
                    $disciplinePlayer = new DisciplinePlayer();
                    $disciplinePlayer->setDiscipline($selectedDiscipline);
                    $disciplinePlayer->setPlayer($player);

                    if ($disciplineType->getPartnerRegistration()) {
                        $partner = $form->get('disciplinePartner-'. $disciplineType->getId())->getData();
                        $disciplinePlayer->setPartner($partner);
                    }

                    $player->addDisciplinePlayer($disciplinePlayer);
                    $nrDisciplines++;
                }
            }

       		// max number of playing disciplines (except for organizer and admin)
       		if (($player->getTournament()->getMaxRegistrationDisciplines()!=0) && $nrDisciplines > $player->getTournament()->getMaxRegistrationDisciplines() && !$player->getTournament()->getOrganizerPersons()->contains($loggedinPerson) && !$this->userIsAdmin() ) {
       			$form->addError(new FormError('Exceeded maximum of disciplines allowed'));
       		}

            if (!$form->isValid()) {
                $this->container->get('session')->getFlashBag()->add('error', $this->trans('form.missing'));
            } else {
       			// setting values of form fields
       			foreach ($player->getRegistrationFormValues() as $formValue) {
	       			$field = $formValue->getField();
	       			$value = $form->get('formValue-'. $field->getId())->getData();
	       			$formValue->setValue($value);
	       		}
       			
	       		$em = $this->doctrine->getManager();
	       		$registrationFor = $form->get('registrationFor')->getData();
	       		$registrationEmail = $form->get('registrationEmail')->getData();
	       		if ($registrationFor == "else") {
	       			// save player for other person
	       			if ($registrationEmail == "") {
		       			// unknown other person
		       			$player->setPerson(null);
		       		} else {
		       			// Authorize Person for Player
                        $model = new AuthorizationModel($this->container);
                        $person = $model->createAuthorizationPlayer($player, $registrationEmail);
		       		}
	       		} else {
	       			// save player for logged in person
	       			$player->setPerson($loggedinPerson);
	       		}
	       		
	       		if (!is_null($player->getRegistrationGroup())) {
		        	if (sizeof($player->getRegistrationGroup()->getPlayers()) == 0) {
		        		// no players yet in registration group -> make contact player
		        		$player->setIsContactPlayer(true);
		        	}
		        }
	        	
	            // saving the player to the database
			    $em->persist($player);
			    $em->flush();

                // Create new player event
                $event = new PlayerEvent($player);
                $sendPlayerNotification = (!$form->has('sendPlayerNotification') || $form->get('sendPlayerNotification')->getData());
                $event->setSendPlayerNotification($sendPlayerNotification);
                $this->container->get('event_dispatcher')->dispatch(NotificationEvents::PLAYER_NEW, $event);
			    
			    $this->container->get('session')->getFlashBag()->add('success', $this->trans('player.created'));
	            $res['changed'] = true;

                // check for new payments, and add to cart
                $res['redirectToCart'] = $this->checkForPayments($form, $player);
	        }
	    } else {
	    	// new registration -> set default values of person
	    	// but only for logged in user
	    	if (is_object($this->getUser()) && is_object($this->getUser()->getPerson())) {
		    	$person = $this->getUser()->getPerson();
		    	$form->get('firstName')->setData($person->getFirstName());
		    	$form->get('lastName')->setData($person->getLastName());
		    	$form->get('gender')->setData($person->getGender());
		    }
	    }

	    $res['player'] = $player;
	    $res['form'] = $form;
	    return $res;
    }

    /**
      * Edit player information
      * @return array(
      *		player => player info
      *		form => form elements
      * 	changed => true/false whether the player has been changed
      *	)
      */
    public function editPlayer($playerId, Request $request) {
        $res = array("changed"=>false, "redirectToCart"=>false);

    	$player = $this->doctrine
        	->getRepository('TSApiBundle:Player')
        	->findOneBy(array("tournament"=>$this->tournament, "id" => $playerId));
	    if (!$player) {
	        throw new NotFoundHttpException('No player found for id '.$playerId);
	    }
	    
	    // check for view access
        if (false === $this->securityContext->isGranted("VIEW", $player)) {
            throw new AccessDeniedException();
        }
        if (false === $this->securityContext->isGranted("EDIT", $player)) {
            $this->container->get('session')->getFlashBag()->add('info', $this->trans('player.changeRegistrationAllowed'));
        }

	    $tournament = $this->tournament;

        $event = new PlayerEvent($player);
        $event->saveOriginalPlayer();
	    
	    // check for new registration form fields (that were created after player was created)
	    $checkArray = $tournament->getRegistrationFormFields();
        foreach ($player->getRegistrationFormValues() as $formValue) {
        	$checkArray->removeElement($formValue->getField());
        }
        foreach ($checkArray as $checkField) {
        	// adding form field to player
            if (!$checkField->getIsHidden()) {
                $formValue = new RegistrationFormValue();
                $formValue->setPlayer($player);
                $formValue->setField($checkField);
                $player->addRegistrationFormValue($formValue);
            }
        }
        
        $form = $this->formFactory->create(new PlayerType($tournament, $this->securityContext, "else", ''), $player, array('em'=>$this->doctrine->getManager(), 'player'=>$player, 'tournament'=>$this->tournament));
        $form->remove('registrationFor');
        $form->remove('registrationEmail');
        $form->add('newPersonEmail', 'email', array(
        	'mapped' => false,
        	'label' => $this->trans('form.newPersonEmail.label'),
        	'required' => false,
        	'attr' => array(
        		'placeholder' => $this->trans('form.newPersonEmail.placeholder'),
                'info' => $this->trans('form.newPersonEmail.info'),
        	),
        	'constraints' => new Email(array(
        		'message' => $this->trans('form.newPersonEmail.invalid'),
        		'checkMX' => true,
        	)),
        ));
        $form->get('conditions')->setData(true);

        if ($request->isMethod('POST')) {
            // check for edit access
            if (false === $this->securityContext->isGranted("EDIT", $player)) {
                throw new AccessDeniedException();
            }

            $form->handleRequest($request);

       		$loggedinPerson = null;
	        if (is_object($this->getUser())) {
	        	$loggedinPerson = $this->getUser()->getPerson();
	        }

            // setting disciplines
            $nrDisciplines = 0;
            foreach ($tournament->getDisciplineTypes() as $disciplineType) {
                // find existing DisciplinePlayer
                $disciplinePlayerRepository = $this->doctrine
                    ->getRepository('TSApiBundle:DisciplinePlayer');
                $queryObject = $disciplinePlayerRepository->createQueryBuilder('dp')
                    ->andWhere('dp.player = :player')
                    ->setParameter('player', $player)
                    ->leftJoin('dp.discipline', 'discipline')
                    ->andWhere('discipline.disciplineType = :disciplineType')
                    ->setParameter('disciplineType', $disciplineType)
                    ->getQuery();
                try {
                    $disciplinePlayer = $queryObject->getSingleResult();
                } catch(\Doctrine\ORM\NoResultException $e) {
                    $disciplinePlayer = new DisciplinePlayer();
                    $player->addDisciplinePlayer($disciplinePlayer);
                }

                $selectedDiscipline = $form->get('discipline-'. $disciplineType->getId())->getData();
                if ($selectedDiscipline) {
                    $disciplinePlayer->setDiscipline($selectedDiscipline);
                    $disciplinePlayer->setPlayer($player);

                    if ($disciplineType->getPartnerRegistration()) {
                        $partner = $form->get('disciplinePartner-'. $disciplineType->getId())->getData();
                        $disciplinePlayer->setPartner($partner);
                    }
                    $nrDisciplines++;
                } else {
                    // remove DisciplinePlayer
                    $this->doctrine->getManager()->remove($disciplinePlayer);
                }
            }

            // max number of playing disciplines (except for organizer and admin)
            if (($player->getTournament()->getMaxRegistrationDisciplines()!=0) && $nrDisciplines > $player->getTournament()->getMaxRegistrationDisciplines() && !$player->getTournament()->getOrganizerPersons()->contains($loggedinPerson) && !$this->userIsAdmin() ) {
                $form->addError(new FormError('Exceeded maximum of disciplines allowed'));
            }

            if (!$form->isValid()) {
                $this->container->get('session')->getFlashBag()->add('error', $this->trans('form.missing'));
            } else {
	        	$em = $this->doctrine->getManager();
	        	$newPersonEmail = $form->get('newPersonEmail')->getData();
	        	if (!empty($newPersonEmail)) {
	        		// Authorization Person for Player
                    $model = new AuthorizationModel($this->container);
                    $person = $model->createAuthorizationPlayer($player, $newPersonEmail);
	        		$this->container->get('session')->getFlashBag()->add('success', $this->trans('access.granted', array('%email%'=>$newPersonEmail)));
	        	}

       			// setting values of form fields
       			foreach ($player->getRegistrationFormValues() as $formValue) {
	       			$field = $formValue->getField();
	       			$value = $form->get('formValue-'. $field->getId())->getData();
	       			$formValue->setValue($value);
	       		}
	       		
	       		if (!is_null($player->getRegistrationGroup())) {
		        	if (sizeof($player->getRegistrationGroup()->getPlayers()) == 0) {
		        		// no players yet in group -> make contact player
		        		$player->setIsContactPlayer(true);
		        	}
		        }
	        	
	            // saving the player to the database
			    $em->flush();

                // Create changed player event
                $sendPlayerNotification = (!$form->has('sendPlayerNotification') || $form->get('sendPlayerNotification')->getData());
                $event->setSendPlayerNotification($sendPlayerNotification);
                $this->container->get('event_dispatcher')->dispatch(NotificationEvents::PLAYER_CHANGE, $event);
			    
			    $this->container->get('session')->getFlashBag()->add('success', $this->trans('player.updated'));
	            $res['changed'] = true;

                // check for new payments, and add to cart
                $res['redirectToCart'] = $this->checkForPayments($form, $player);
	        }
	    }
	    
	    $res['player'] = $player;
	    $res['form'] = $form;
	    return $res;
	}

    /**
     * Returns the (financial) products of a tournament, with the ID in the key
     * @return array
     */
    public function getFinancialProductsById() {
        $res = array();

        foreach ($this->tournament->getProducts() as $product) {
            $res[$product->getId()] = $product;
        }

        return $res;
    }

    private function getUser() {
		return $this->securityContext->getToken()->getUser();
    }

    private function userIsAdmin() {
    	return is_object($this->getUser()) && $this->getUser()->isAdmin();
    }

    /**
     * Check for payments, and if there are new payments, add these to the basket.
     * @param mixed $form The form in which additional products for payment are selected
     * @param \TS\ApiBundle\Entity\Player $player
     * @return boolean Whether to redirect to cart
     */
    public function checkForPayments($form, $player) {
        $redirectToCart = false;
        $addProductForm = ($form->has('addProduct')) ? $form->get('addProduct')->getData() : null;
        if (sizeof($addProductForm) > 0) {
            $em = $this->doctrine->getManager();
            $cart = $this->container->get('sylius.cart_provider')->getCart();
            foreach ($addProductForm as $product) { /* @var \TS\FinancialBundle\Entity\Product $product */
                $cartItem = new CartItem();
                $cartItem->setQuantity(1);
                $cartItem->setProduct($product);
                $cartItem->setPlayer($player);
                $cartItem->setOrder($cart);
                $cartItem->setUnitPrice($product->getPrice());

                $cart->addItem($cartItem);
            }

            if (!is_object($cart->getExecPerson()) && is_object($this->getUser())){
                $cart->setExecPerson($this->getUser()->getPerson());
            }
            $em->persist($cart);
            $em->flush();

            $this->container->get('sylius.cart_provider')->setCart($cart);
            $this->container->get('session')->getFlashBag()->add('success', $this->trans('basket.added'));
            $redirectToCart = true;
        }
        return $redirectToCart;
    }

    /**
     * Translate a string
     * @param String $str translatable string / variable
     * @param array $variables Will be passed to translatable string
     * @return String Translated string
     */
    private function trans($str, $variables = array()) {
        return $this->container->get('translator')->trans($str, $variables, 'player');
    }
}