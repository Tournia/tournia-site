<?php

namespace TS\ControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use TS\ControlBundle\Form\Type\OrganizerEmailType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;


class RegistrationsController extends MainController
{
    
    /**
	 * Email players
	 */
    public function emailAction(Request $request)
    {   
        $form = $this->createForm(new OrganizerEmailType(), null, array('tournament'=>$this->tournament));
	
	    if ($request->isMethod('POST')) {
	        $form->handleRequest($request);
	        
	        if ($form->isValid()) {
	        	$onlyContactPlayers = false;
	        	if ($this->tournament->getRegistrationGroupEnabled()) {
		        	$onlyContactPlayers = $form->get('contactPlayers')->getData() == 'onlyContactPlayers';
		        }
		        
		        $status = $form->get('status')->getData();
		        $subject = $form->get('subject')->getData();
		        $message = $form->get('message')->getData();
		        $i = 0;
		        
	            foreach ($this->tournament->getPlayers() as $player) {
	            	if (($player->getPerson() != null) && (!is_null($player->getPerson()->getEmail())) && (!$onlyContactPlayers || $player->getIsContactPlayer()) && in_array($player->getStatus(), $status)) {
		            	$env = new \Twig_Environment(new \Twig_Loader_String());
						$renderedMessage = $env->render($message, array(
							'firstName' => $player->getFirstName(),
							'lastName' => $player->getLastName()
						));
		            	
		            	$email = \Swift_Message::newInstance()
					        ->setSubject($subject)
					        ->setFrom($this->container->getParameter('email_from_email'))
					        ->setReplyTo($this->tournament->getEmailFrom())
					        ->setTo($player->getPerson()->getEmail())
					        ->setBody($renderedMessage);
	
					    $this->get('mailer')->send($email);
					    $i++;
					}
	            }

                $flashMessage = $this->get('translator')->trans('flash.email.sent', array('%count%'=> $i), 'control');
	            $this->get('session')->getFlashBag()->add('success', $flashMessage);
	            return $this->redirect($this->generateUrl('control_index', array('tournamentUrl'=>$this->tournament->getUrl())));
	        }
	    }
	    	    
	    return $this->render('TSControlBundle:Registrations:email.html.twig', array(
	        'form' => $form->createView(),
	    ));
	}
}
