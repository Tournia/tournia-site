<?php

namespace TS\SettingsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use TS\SettingsBundle\Form\Type\TournamentType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class TournamentController extends MainController
{
    
    /**
	 * Edit tournament information
	 */
    public function editAction(Request $request) {
	    $tournament = $this->tournament;
	    $currentTournamentUrl = $tournament->getUrl();
	    
    	$form = $this->createForm(new TournamentType(), $tournament, array('em'=>$this->getDoctrine()->getManager(), 'tournament'=>$tournament));
    	$isValidForm = false;
        
        if ($request->isMethod('POST')) {
        	// Create an array of the current RegistrationFormFields objects in the database (to make deletion possible)
        	$originalRegistrationFormFields = array();
		    foreach ($tournament->getRegistrationFormFields() as $field) {
		        $originalRegistrationFormFields[] = $field;
		    }

       		$form->handleRequest($request);

            // re-set status options because of problems with array shifts
            $statusOptionsArray = array();
            foreach ($this->tournament->getStatusOptions() as $statusOption) {
                $statusOptionsArray[] = $statusOption;
            }
            $this->tournament->setStatusOptions($statusOptionsArray);

       		$isValidForm = $form->isValid();
       		if (!$isValidForm) {
                $flashMessage = $this->get('translator')->trans('flash.form.error', array(), 'settings');
                $this->get('session')->getFlashBag()->add('error', $flashMessage);
	        } else {
	        	$em = $this->getDoctrine()->getManager();
	        	
	        	// Set tournament in entity RegistrationFormFields
	        	foreach ($tournament->getRegistrationFormFields() as $field) {
	        		if (is_null($field->getTournament())) {
	        			$field->setTournament($tournament);
	        		}
	        	}

	        	// find RegistrationFormFields that are no longer present
		        foreach ($tournament->getRegistrationFormFields() as $field) {
		            foreach ($originalRegistrationFormFields as $key => $toDel) {
		                if ($toDel->getId() === $field->getId()) {
		                    unset($originalRegistrationFormFields[$key]);
		                }
		            }
		        }
		        // remove the deleted RegistrationFormFields
		        foreach ($originalRegistrationFormFields as $field) {
		            if ($field->getValues()->isEmpty()) {
		            	$em->remove($field);
		            } else {
                        $flashMessage = $this->get('translator')->trans('flash.registrationFormField.delete.error', array('%name%'=>$field->getName()), 'settings');
                        $this->get('session')->getFlashBag()->add('error', $flashMessage);
		            }
		        }

                // re-set choiceOptions of RegistrationFormFields because of problems with array shifts

                foreach ($this->tournament->getRegistrationFormFields() as $field) {
                    $choiceOptionsArray = array();
                    foreach ($field->getChoiceOptions() as $option) {
                        $choiceOptionsArray[] = $option;
                    }
                    $field->setChoiceOptions($choiceOptionsArray);
                }
            
	            // saving the tournament to the database
			    $em->persist($tournament);
			    $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.tournament.saved', array(), 'settings');
			    $this->get('session')->getFlashBag()->add('success', $flashMessage);

                // workaround: when changing order of products, this is not immediately displayed in the form; on a page reload it is
                return $this->redirect($this->generateUrl('settings_tournament', array('tournamentUrl'=> $this->tournament->getUrl())));
            }
	    }

	    if ($isValidForm && $currentTournamentUrl != $tournament->getUrl()) {
	    	// changed tournament url
	    	return $this->redirect($this->generateUrl('settings_tournament', array('tournamentUrl'=>$this->tournament->getUrl())));
	    } else {
	    	$this->tournament->setUrl($currentTournamentUrl);
	    	$templateArray = array(
		        'form' => $form->createView(),
		    );
		    return $this->render('TSSettingsBundle:Tournament:tournament.html.twig', $templateArray);
	    }
	}
    
    /**
      * Delete a tournament
      */
    public function deleteAction() {	
	    // check for delete access
        if (false === $this->get('security.context')->isGranted("DELETE", $this->tournament)) {
            throw new AccessDeniedException();
        }
        
        if (sizeof($this->tournament->getPlayers()) > 0) {
        	// check if there are players in the tournament. This also prevents deletion of financial transactions, as this is checked when deleting a player
            $flashMessage = $this->get('translator')->trans('flash.tournament.delete.error', array(), 'settings');
            $this->get('session')->getFlashBag()->add('error', $flashMessage);
        } else {
		    $em = $this->getDoctrine()->getManager();

            foreach ($this->tournament->getRegistrationGroups() as $registrationGroup) {
                $em->remove($registrationGroup);
            }
            foreach ($this->tournament->getRegistrationFormFields() as $registrationFormField) {
                $em->remove($registrationFormField);
            }
            foreach ($this->tournament->getDisciplines() as $discipline) {
                $em->remove($discipline);
            }
            foreach ($this->tournament->getUpdateMessages() as $updateMessage) {
                $em->remove($updateMessage);
            }
            foreach ($this->tournament->getLocations() as $location) {
                $em->remove($location);
            }
            foreach ($this->tournament->getMatches() as $match) {
                $this->getDoctrine()
                    ->getRepository('TSApiBundle:Match')
                    ->remove($match);
            }
            foreach ($this->tournament->getProducts() as $product) {
                $em->remove($product);
            }
            foreach ($this->tournament->getBoughtProducts() as $boughtProduct) {
                $em->remove($boughtProduct);
            }
            foreach ($this->tournament->getPayOuts() as $payOut) {
                $em->remove($payOut);
            }
            $em->remove($this->tournament->getSite());

            $em->remove($this->tournament);
			$em->flush();

            $flashMessage = $this->get('translator')->trans('flash.tournament.deleted', array(), 'settings');
	        $this->get('session')->getFlashBag()->add('success', $flashMessage);
            return $this->redirect($this->generateUrl('front_index'));
	    }
        return $this->redirect($this->generateUrl('settings_tournament', array('tournamentUrl'=>$this->tournament->getUrl())));
    }
}