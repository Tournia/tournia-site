<?php

namespace TS\SettingsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use TS\SettingsBundle\Form\Type\FinancialType;


class FinancialController extends MainController
{

    /**
     * Financial settings
     */
    public function financialAction(Request $request) {
        $form = $this->createForm(new FinancialType(), $this->tournament, array(
            'em'=>$this->getDoctrine()->getManager(),
            'tournament'=>$this->tournament,
        ));

        if ($request->isMethod('POST')) {
            // Create an array of the current Product objects in the database (to make deletion possible)
            $originalProducts = array();
            foreach ($this->tournament->getProducts() as $product) {
                $originalProducts[] = $product;
            }

            $form->handleRequest($request);

            if (!$form->isValid()) {
                $flashMessage = $this->get('translator')->trans('flash.form.error', array(), 'settings');
                $this->get('session')->getFlashBag()->add('error', $flashMessage);
            } else {
                $em = $this->container->get('sylius.manager.product');

                // Set tournament in entity Product
                foreach ($this->tournament->getProducts() as $product) {
                    if (is_null($product->getTournament())) {
                        $product->setTournament($this->tournament);
                    }
                }

                // find products that are no longer present
                foreach ($this->tournament->getProducts() as $product) {
                    foreach ($originalProducts as $key => $toDel) {
                        if ($toDel->getId() === $product->getId()) {
                            unset($originalProducts[$key]);
                        }
                    }
                }
                // remove the deleted products
                foreach ($originalProducts as $product) {
                    $cartItemRepository = $this->getDoctrine()
                        ->getRepository('TSFinancialBundle:CartItem');
                    $cartItems = $cartItemRepository->findBy(array("product"=>$product));

                    if (sizeof($cartItems) == 0) {
                        $em->remove($product);
                    } else {
                        $flashMessage = $this->get('translator')->trans('flash.product.delete.error', array('%name%'=>$product->getName()), 'settings');
                        $this->get('session')->getFlashBag()->add('error', $flashMessage);
                    }
                }

                // saving the changes to the database
                $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.financial.saved', array(), 'settings');
                $this->get('session')->getFlashBag()->add('success', $flashMessage);

                // workaround: when changing order of products, this is not immediately displayed in the form; on a page reload it is
                return $this->redirect($this->generateUrl('settings_financial', array('tournamentUrl'=> $this->tournament->getUrl())));
            }
        }
        $boughtProductRepository = $this->getDoctrine()
            ->getRepository('TSFinancialBundle:BoughtProduct');
        $outstandingAmount = $boughtProductRepository->getOutstandingAmount($this->tournament);

        return $this->render('TSSettingsBundle:Financial:financial.html.twig', array(
            'form' => $form->createView(),
            'outstandingAmount' => $outstandingAmount,
        ));
    }
}
