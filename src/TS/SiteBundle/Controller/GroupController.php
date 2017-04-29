<?php

namespace TS\SiteBundle\Controller;

use TS\ApiBundle\Entity\RegistrationGroup;
use Symfony\Component\HttpFoundation\Request;
use TS\NotificationBundle\Event\RegistrationGroupEvent;
use TS\NotificationBundle\NotificationEvents;
use TS\SiteBundle\Form\Type\RegistrationGroupType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

use TS\SiteBundle\EventListener\MailChanges;


class GroupController extends MainController
{
    public function overviewAction()
    {
        $groups = $this->getDoctrine()
        	->getRepository('TSApiBundle:RegistrationGroup')
        	->getAllRegistrationGroups($this->tournament);
        
        return $this->render('TSSiteBundle:Group:overview.html.twig', array(
        	'groups' => $groups,
        ));
    }
    
    /**
	 * Edit group information
	 */
    public function editAction($groupId, Request $request)
    {   
        $group = $this->getDoctrine()
        	->getRepository('TSApiBundle:RegistrationGroup')
        	->findOneById($groupId);
        if (!$group) {
	        throw $this->createNotFoundException('No group found for id '.$groupId);
	    }
        
        // check for edit access
        if (false === $this->get('security.authorization_checker')->isGranted("EDIT", $group)) {
            throw new AccessDeniedException();
        }

		$event = new RegistrationGroupEvent($group);
		$event->saveOriginalRegistrationGroup();
	    
    	$form = $this->createForm(new RegistrationGroupType($group), $group);
        
        if ($request->isMethod('POST')) {
       		$form->handleRequest($request);
       		
       		if (sizeof($form->get('contactPlayers')->getData()) == 0) {
       			$form->get('contactPlayers')->addError(new FormError('Select minimal one contact person'));
       		}

	        if ($form->isValid()) {
	            // now changing contact player
			    foreach ($group->getPlayers() as $player) {
			    	$player->setIsContactPlayer(false);
			    }
			    foreach ($form->get('contactPlayers')->getData() as $newContactPlayer) {
			    	$newContactPlayer->setIsContactPlayer(true);
			    }
	            
	            // saving the registration group to the database
	            $em = $this->getDoctrine()->getManager();
			    $em->flush();

				// Create changed RegistrationGroup event
				$this->container->get('event_dispatcher')->dispatch(NotificationEvents::REGISTRATIONGROUP_CHANGE, $event);
			    
			    $this->get('session')->getFlashBag()->add('success', 'Group updated');
	            return $this->redirect($this->generateUrl('group_overview', array('tournamentUrl'=> $this->tournament->getUrl())));
	        }
	    }
	    
	    $formView = $form->createView();
	    
	    return $this->render('TSSiteBundle:Group:edit.html.twig', array(
	        'group' => $group,
	        'form' => $formView,
	        'players' => $group->getPlayers(),
	    ));
	}
    
    /**
      * Create a new group
      */
    public function createAction(Request $request)
    {	
        $group = new RegistrationGroup();
        
        // check for create access
        if (false === $this->get('security.authorization_checker')->isGranted("CREATE", $group)) {
            throw new AccessDeniedException();
        }
        
        $group->setTournament($this->tournament);

        $form = $this->createForm(new RegistrationGroupType(null), $group);
        
        if ($request->isMethod('POST')) {
       		$form->handleRequest($request);

	        if ($form->isValid()) {
	            // saving the registration group to the database
	            $em = $this->getDoctrine()->getManager();
			    $em->persist($group);
			    $em->flush();

			    $mailChanges = new MailChanges($this->container);
			    $mailChanges->emailAfterContactPlayerChanges();
			    
			    $this->get('session')->getFlashBag()->add('success', 'New group created');
	            return $this->redirect($this->generateUrl('group_overview', array('tournamentUrl'=> $this->tournament->getUrl())));
	        }
	    }
	    
	    return $this->render('TSSiteBundle:Group:edit.html.twig', array(
	    	'group' => $group,
	        'form' => $form->createView(),
	    ));
    }
    
    /**
      * Delete a group
      */
    public function deleteAction($groupId)
    {	
        $group = $this->getDoctrine()
        	->getRepository('TSApiBundle:RegistrationGroup')
        	->find($groupId);
	    if (!$group) {
	        throw $this->createNotFoundException('No group found for id '. $groupId);
	    }
	    
	    // check for delete access
        if (false === $this->get('security.authorization_checker')->isGranted("DELETE", $group)) {
            throw new AccessDeniedException();
        }
        
        if (sizeof($group->getPlayers()) > 0) {
        	$this->get('session')->getFlashBag()->add('error', 'There are players in this group that need to be deleted first');
        } else {
		    $em = $this->getDoctrine()->getManager();
		    $em->remove($group);
			$em->flush();

			$mailChanges = new MailChanges($this->container);
			$mailChanges->emailAfterContactPlayerChanges();
		    
	        $this->get('session')->getFlashBag()->add('success', 'Group deleted');
	    }
	    return $this->redirect($this->generateUrl('group_overview', array('tournamentUrl' => $this->tournament->getUrl())));
    }
}
