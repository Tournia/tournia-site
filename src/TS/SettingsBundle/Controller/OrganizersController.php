<?php

namespace TS\SettingsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use TS\SettingsBundle\Form\Type\OrganizersType;
use TS\AccountBundle\Model\AuthorizationModel;
use Symfony\Component\Form\FormError;

class OrganizersController extends MainController
{

    /**
     * Organizers settings
     */
    public function organizersAction(Request $request) {

        $form = $this->createForm(new OrganizersType(), $this->tournament);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if (sizeof($this->tournament->getOrganizerPersons()) == 0) {
                $form->get('organizerPersons')->addError(new FormError('At least one person needs to be organizer'));
            }

            if (!$form->isValid()) {
                $flashMessage = $this->get('translator')->trans('flash.form.error', array(), 'settings');
                $this->get('session')->getFlashBag()->add('error', $flashMessage);
            } else {
                $em = $this->getDoctrine()->getManager();

                // going through organizerPersons and authorize new persons
                $authorizeEmails = array();
                foreach ($this->tournament->getOrganizerPersons() as $person) {
                    if (is_null($person->getId())) {
                        // User has added a new organizer
                        // Don't save the FormType Person in the DB, but the AuthorizationModel's Person

                        // check if Person with this email is not already organizer
                        $uniqueEmail = true;
                        foreach ($this->tournament->getOrganizerPersons() as $checkPerson) {
                            if ($checkPerson->getId() && ($checkPerson->getEmail() == $person->getEmail())) {
                                $uniqueEmail = false;
                                $flashMessage = $this->get('translator')->trans('flash.organizers.hasaccess', array('%email%'=>$person->getEmail()), 'settings');
                                $this->get('session')->getFlashBag()->add('error', $flashMessage);
                            }
                        }

                        if ($uniqueEmail) {
                            $authorizeEmails[] = $person->getEmail();
                        }
                        $this->tournament->removeOrganizerPerson($person);
                        $em->remove($person);
                    }
                }

                if (sizeof($authorizeEmails) > 0) {
                    // Authorize Person(s) for tournament
                    $model = new AuthorizationModel($this->container);
                    foreach ($authorizeEmails as $email) {
                        $modelPerson = $model->createAuthorizationTournament($this->tournament, $email);
                        $flashMessage = $this->get('translator')->trans('flash.organizers.added', array('%email%'=>$modelPerson->getEmail()), 'settings');
                        $this->get('session')->getFlashBag()->add('success', $flashMessage);
                    }
                }
                // re-create form, to remove organizer persons that were authorized
                $form = $this->createForm(new OrganizersType(), $this->tournament);

                // saving the tournament in the database;
                $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.organizers.saved', array(), 'settings');
                $this->get('session')->getFlashBag()->add('success', $flashMessage);
            }
        }
        return $this->render('TSSettingsBundle:Organizers:organizers.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
