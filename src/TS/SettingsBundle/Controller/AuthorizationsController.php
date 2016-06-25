<?php

namespace TS\SettingsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use TS\SettingsBundle\Form\Type\AuthorizationType;
use TS\SettingsBundle\Form\Type\OrganizersType;
use TS\AccountBundle\Model\AuthorizationModel;
use Symfony\Component\Form\FormError;
use TS\SettingsBundle\Form\Type\TournamentType;

class AuthorizationsController extends MainController
{

    /**
     * Authorizations settings
     */
    public function authorizationsAction(Request $request) {

        $form = $this->createForm(new AuthorizationType(), $this->tournament->getAuthorization());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            // Check for endDate after startDate
            $dateTimeObjects = array(
                "createRegistrationStart" => "createRegistrationEnd",
                "changeRegistrationStart" => "changeRegistrationEnd",
                "apiStart" => "apiEnd",
            );
            $errorString = $this->container->get('translator')->trans("authorizations.error.startBeforeEndDate", array(), 'settings');
            foreach ($dateTimeObjects as $start=>$end) {
                if (
                    !is_null($form->get($start)->getData()) &&
                    !is_null($form->get($end)->getData()) &&
                    $form->get($start)->getData() > $form->get($end)->getData()
                ) {
                    $form->get($start)->addError(new FormError($errorString));
                }
            }

            if (!$form->isValid()) {
                $flashMessage = $this->get('translator')->trans('flash.form.error', array(), 'settings');
                $this->get('session')->getFlashBag()->add('error', $flashMessage);
            } else {
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.authorizations.saved', array(), 'settings');
                $this->get('session')->getFlashBag()->add('success', $flashMessage);
            }
        }
        return $this->render('TSSettingsBundle:Authorizations:authorizations.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
