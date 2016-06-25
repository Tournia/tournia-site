<?php

namespace TS\SettingsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use TS\SettingsBundle\Form\Type\ApiType;
use TS\SettingsBundle\Form\Type\FinancialType;


class ApiController extends MainController
{

    /**
     * Api settings
     */
    public function apiAction(Request $request) {
        $form = $this->createForm(new ApiType(), $this->tournament);

        if ($request->isMethod('POST')) {
            // Create an array of the current apiKeys objects in the database (to make deletion possible)
            $originalApiKeys = array();
            foreach ($this->tournament->getApiKeys() as $apiKey) {
                $originalApiKeys[] = $apiKey;
            }

            $form->handleRequest($request);

            if (!$form->isValid()) {
                $flashMessage = $this->get('translator')->trans('flash.form.error', array(), 'settings');
                $this->get('session')->getFlashBag()->add('error', $flashMessage);
            } else {
                $em = $this->getDoctrine()->getManager();

                // Set tournament in entity Product
                foreach ($this->tournament->getApiKeys() as $apiKey) {
                    if (is_null($apiKey->getTournament())) {
                        $apiKey->setTournament($this->tournament);
                    }
                }

                // find products that are no longer present
                foreach ($this->tournament->getApiKeys() as $apiKey) {
                    foreach ($originalApiKeys as $key => $toDel) {
                        if ($toDel->getId() === $apiKey->getId()) {
                            unset($originalApiKeys[$key]);
                        }
                    }
                }
                // remove the deleted apiKeys
                foreach ($originalApiKeys as $apiKey) {
                    $em->remove($apiKey);
                }

                // saving the changes to the database
                $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.api.saved', array(), 'settings');
                $this->get('session')->getFlashBag()->add('success', $flashMessage);
                return $this->redirect($this->generateUrl('settings_api', array('tournamentUrl'=>$this->tournament->getUrl())));
            }
        }

        return $this->render('TSSettingsBundle:Api:api.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
