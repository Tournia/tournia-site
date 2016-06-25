<?php

namespace TS\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use TS\NotificationBundle\Event\PersonEvent;
use TS\NotificationBundle\NotificationEvents;

use TS\ApiBundle\Entity\Person;

class AuthenticationController extends Controller {
	
	public function loginAction(Request $request) {
        $session = $request->getSession();

        // setting target path for page to go to after logging in
        $targetPathSession = $session->get('_security.main.target_path');
        $referer = $request->headers->get("referer");
        if (is_null($referer)) {
            // there is no previous page (could mean it is a redirect for logging in)
            if (!is_null($targetPathSession)) {
                // use for logging in set targetPath
                $targetPath = $targetPathSession;
            } else {
                // use index
                $targetPath = $this->generateUrl('front_index', array(), true);
            }
        } else if ($referer != $this->generateUrl('account_login', array(), true)) {
            // there is a previous page, which is not the login page
            $targetPath = $referer;
        } else {
            // there is a previous page, which is the login page -> use previous set targetPath
            if (!is_null($targetPathSession)) {
                // use previous set targetPath
                $targetPath = $targetPathSession;
            } else {
                // use index
                $targetPath = $this->generateUrl('front_index', array(), true);
            }
        }
        $session->set('_security.main.target_path', $targetPath);

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        if ($error != null) {
            $flashMessage = $this->get('translator')->trans('flash.authentication.login.error', array(), 'account');
            $this->get('session')->getFlashBag()->add('error', $flashMessage);
        }

        $registerResponse = $this->forward('FOSUserBundle:Registration:register', array('request' => $request));
        if ($registerResponse->isRedirection()) {
            // redirect from registration
            return $registerResponse;
        } else if ($request->getMethod() == 'POST') {
            $flashMessage = $this->get('translator')->trans('flash.authentication.registration.error', array(), 'account');
            $this->get('session')->getFlashBag()->add('error', $flashMessage);
        }

        if (is_object($this->getUser())) {
            $flashMessage = $this->get('translator')->trans('flash.authentication.alreadyLoggedin', array(), 'account');
            $this->get('session')->getFlashBag()->add('success', $flashMessage);
            return $this->redirect($this->generateUrl('front_index'));
        }

        return $this->render(
            'TSAccountBundle:Authentication:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'registrationForm' => $registerResponse->getContent(),
            )
        );
	}

    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction(Request $request)
    {
        $loginAccount = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($loginAccount)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if (is_null($loginAccount->getPerson())) {
            // create new person for new LoginAccount
            $person = new Person();
            $person->setName($loginAccount->getRegistrationName());
            $person->setEmail($loginAccount->getEmail());
            $loginAccount->setPerson($person);
            $person->addLoginAccount($loginAccount);

            $em = $this->getDoctrine()->getManager();
            $em->persist($loginAccount);
            $em->flush();

            // inform Person of new account
            $event = new PersonEvent($person);
            $this->get('event_dispatcher')->dispatch(NotificationEvents::PERSON_NEW, $event);
        }

        $targetPathSession = $request->getSession()->get('_security.main.target_path');
        if (!is_null($targetPathSession)) {
            return $this->redirect($targetPathSession);
        }

        return $this->render('TSAccountBundle:Registration:confirmed.html.twig', array(
            'user' => $loginAccount,
        ));
    }

    public function impersonateAction(Request $request) {
        if ($request->query->has('changed')) {
            $this->get('session')->getFlashBag()->add('success', 'You are now logged in as '. $this->getUser() .' ('. $this->getUser()->getPerson()->getName() .').');
            return $this->redirect($this->generateUrl('front_index'));
        }

        if (!$this->getUser()->isAdmin()) {
            throw new AccessDeniedException();
        }

        // Create form
        $form = $this->createFormBuilder()
            ->add('query', 'text')
            ->getForm();

        $users = array();
        $query = '';
        
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $query = $form->get('query')->getData();

            // find users
            $repository = $this->getDoctrine()
                ->getRepository('TSApiBundle:LoginAccount');
            $queryObject = $repository->createQueryBuilder('l')
                ->orWhere('l.username LIKE :query')
                ->orWhere('person.name LIKE :query')
                ->orWhere('person.email LIKE :query')
                ->setParameter('query', '%'. $query .'%')
                ->leftJoin('l.person', 'person')
                ->orderBy('l.username', 'ASC')
                ->setMaxResults(100)
                ->getQuery();
            $users = $queryObject->getResult();
        }

        return $this->render('TSAccountBundle:Authentication:impersonate.html.twig', array(
            'form' => $form->createView(),
            'users' => $users,
            'query' => $query,
        ));
    }
}
