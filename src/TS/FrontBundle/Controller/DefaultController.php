<?php

namespace TS\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use TS\ApiBundle\Entity\Tournament;
use TS\FrontBundle\Form\Type\TournamentFilterFormType;
use TS\FrontBundle\Form\Type\StartTournamentType;
use TS\FrontBundle\Form\Type\ContactUsType;

class DefaultController extends Controller {
	
	public function indexAction(Request $request) {
        $formType = new TournamentFilterFormType();
        $form = $this->createForm($formType);
        
        $currentlyPlayingTournaments = $this->getDoctrine()
            ->getRepository('TSApiBundle:Tournament')
            ->getCurrentlyPlayingTournaments();

        $upcomingTournaments = $this->getDoctrine()
            ->getRepository('TSApiBundle:Tournament')
            ->getUpcomingTournaments();

        $earlierTournaments = $this->getDoctrine()
            ->getRepository('TSApiBundle:Tournament')
            ->getPopularEarlierTournaments();

		return $this->render('TSFrontBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
            'currentlyPlayingTournaments' => $currentlyPlayingTournaments,
			'upcomingTournaments' => $upcomingTournaments,
			'earlierTournaments' => $earlierTournaments,
        ));
	}

	public function allTournamentsAction(Request $request) {
        $formType = new TournamentFilterFormType();
        $form = $this->createForm($formType);

        if ($request->isMethod(Request::METHOD_POST) && !$request->request->has('search_tournament_navbar')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $searchFilter = $request->get("tournament_filter");
            } else {
                $searchFilter = $formType->getDefaultFilter();
            }
        } else {
            // no search query, or search from navbar
            $searchFilter = $formType->getDefaultFilter();
            if ($request->request->has('search_tournament_navbar')) {
                // search from navbar -> use keyword to query
                $searchQuery = $request->request->get('search_tournament_navbar', '');
                $searchFilter['keyword'] = $searchQuery;
                $form->get('keyword')->setData($searchQuery);
            }
        }

        $tournaments = $this->getDoctrine()
            ->getRepository('TSApiBundle:Tournament')
            ->findUsingFilters($searchFilter);

		return $this->render('TSFrontBundle:Default:allTournaments.html.twig', array(
            'form' => $form->createView(),
			'tournaments' => $tournaments,
            'numberOfResultsOptions' => $formType->getAllowedNumberOfResults()
        ));
	}
	
	public function contactAction(Request $request) {
		// Handle form
        $form = $this->createForm(new ContactUsType());
        
        if ($request->getMethod() == 'POST') {
        	$form->handleRequest($request);

        	if ($form->isValid()) {
        		$formData = $form->getData();
            	$emailAddress = $formData['email'];
				$text = "On the contact page, someone has filled in this form: ";
                $text .= "Name: ". $formData['name'] ."\n";
                $text .= "Email: ". $formData['email'] ."\n";
                $text .= "Phone: ". $formData['phone'] ."\n";
                $text .= "Message:\n";
                $text .= $formData['message'] ."\n";
                $text .= "---End of message---";
                
				$this->mail($emailAddress, $text);
                $flashMessage = $this->get('translator')->trans('flash.contact.success', array(), 'front');
		    	$this->get('session')->getFlashBag()->add('success', $flashMessage);

                // empty form data (to avoid confusing non-empty form)
                $form = $this->createForm(new ContactUsType());
        	} else {
                $flashMessage = $this->get('translator')->trans('flash.contact.error', array(), 'front');
	        	$this->get('session')->getFlashBag()->add('error', $flashMessage);
	        }
        }

		return $this->render('TSFrontBundle:Default:contact.html.twig', array(
			'form' => $form->createView(),
		));
	}

	public function aboutAction() {
		return $this->render('TSFrontBundle:Default:about.html.twig');
	}

    public function privacyStatementAction() {
        return $this->render('TSFrontBundle:Default:privacyStatement.html.twig');
    }

    public function developersAction() {
        return $this->render('TSFrontBundle:Default:developers.html.twig');
    }

    public function appAction() {
        return $this->render('TSFrontBundle:Default:app.html.twig');
    }

	/**
     * Start a new tournament
     */
    public function startTournamentAction(Request $request) {
		// Handle form
        $form = $this->createForm( new StartTournamentType() );
        
        if ($request->getMethod() == 'POST') {
        	$form->handleRequest($request);

        	if ($form->isValid()) {
        		$formData = $form->getData();
        		
        		$this->get('session')->set('tournamentName', $formData['name']);
        		
		    	return $this->redirect($this->generateUrl('settings_tournament_create'));
        	} else {
                $flashMessage = $this->get('translator')->trans('flash.starttournament.error', array(), 'front');
                $this->get('session')->getFlashBag()->add('error', $flashMessage);
	        }
        }

		return $this->render('TSFrontBundle:Default:startTournament.html.twig', array(
			'form' => $form->createView(),
		));
    }

	private function mail($from, $text) {
		$message = \Swift_Message::newInstance()
	        ->setSubject('Tournia automatic mailing')
	        ->setFrom($from)
	        ->setTo('support@tournia.net')
	        ->setBody($text)
	    ;
	    $this->get('mailer')->send($message);
	}
}
