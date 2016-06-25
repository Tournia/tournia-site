<?php

namespace TS\ControlBundle\Controller;

use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use Symfony\Component\Config\Definition\Exception\Exception;
use TS\ApiBundle\Entity\Tournament;
use TS\ControlBundle\Form\Type\ManualPaymentType;
use TS\FinancialBundle\Entity\BoughtProduct;
use Symfony\Component\HttpFoundation\Request;
use TS\FinancialBundle\Entity\Cart;

class PaymentController extends MainController
{
    
    public function overviewAction(Request $request)
    {
        $boughtProductRepository = $this->getDoctrine()
        	->getRepository('TSFinancialBundle:BoughtProduct');
        $boughtProducts = $boughtProductRepository->getAllBoughtProducts($this->tournament);
        $totalAmount = $boughtProductRepository->getTotalAmount($this->tournament);
        $outstandingAmount = $boughtProductRepository->getOutstandingAmount($this->tournament);
        
        return $this->render('TSControlBundle:Payment:overviewPayment.html.twig', array(
        	'boughtProducts' => $boughtProducts,
        	'totalAmount' => $totalAmount,
            'outstandingAmount' => $outstandingAmount,
            'resetUrl' => $request->query->has('resetUrl'),
        ));
    }
    
    /**
      * Create a new manual payment
      */
    public function createAction(Request $request)
    {	
        $boughtProduct = new BoughtProduct();
        $form = $this->createForm(new ManualPaymentType(), $boughtProduct, array('em'=>$this->getDoctrine()->getManager(), 'tournament'=>$this->tournament));
        
        if ($request->isMethod('POST')) {
       		$form->handleRequest($request);
       		
	        if ($form->isValid()) {
                $boughtProduct->setPaidoutAmount($boughtProduct->getAmount());
                $boughtProduct->setTournament($this->tournament);

                $cartOrder = new Cart();
                $cartOrder->setExecPerson($this->getUser()->getPerson());

                $paymentInstruction = new PaymentInstruction($boughtProduct->getAmount(), $this->tournament->getPaymentCurrency(), "manual");
                $cartOrder->setPaymentInstruction($paymentInstruction);
                $boughtProduct->setCartOrder($cartOrder);

	            $player = $boughtProduct->getPlayer();
	            $player->addBoughtProduct($boughtProduct);
	            
	            // automatic status update for player
	            if ($this->tournament->getPaymentUpdateStatus() && ($boughtProduct->getPlayer()->getStatus() == $this->tournament->getPaymentUpdateFromStatus())) {
					$player->setStatus($this->tournament->getPaymentUpdateToStatus());
				}
	            
	            // saving the payment to the database
	            $em = $this->getDoctrine()->getManager();
			    $em->persist($boughtProduct);
                $em->persist($cartOrder);
                $em->persist($paymentInstruction);
			    $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.payment.created', array(), 'control');
			    $this->get('session')->getFlashBag()->add('success', $flashMessage);
	            return $this->redirect($this->generateUrl('control_payment_overview', array(
                    'tournamentUrl'=> $this->tournament->getUrl(),
                    'resetUrl' => true,
                )));
	        }
	    }
	    
	    return $this->render('TSControlBundle:Payment:editManualPayment.html.twig', array(
	    	'boughtProduct' => $boughtProduct,
	        'form' => $form->createView(),
	    ));
    }
    
    /**
	 * Edit manual payment
	 */
    public function editAction($boughtProductId, Request $request)
    {   
        $boughtProduct = $this->getBoughtProduct($boughtProductId);
        $form = $this->createForm(new ManualPaymentType(), $boughtProduct, array('em'=>$this->getDoctrine()->getManager(), 'tournament'=>$this->tournament));
        
        if ($request->isMethod('POST')) {
       		$form->handleRequest($request);
       		
	        if ($form->isValid()) {
                $boughtProduct->setPaidoutAmount($boughtProduct->getAmount());

	            // saving the payment to the database
	            $em = $this->getDoctrine()->getManager();
			    $em->flush();

                $flashMessage = $this->get('translator')->trans('flash.payment.updated', array(), 'control');
			    $this->get('session')->getFlashBag()->add('success', $flashMessage);
	            return $this->redirect($this->generateUrl('control_payment_overview', array(
                    'tournamentUrl'=> $this->tournament->getUrl(),
                    'resetUrl' => true,
                )));
	        }
	    }
	    
	    return $this->render('TSControlBundle:Payment:editManualPayment.html.twig', array(
	        'boughtProduct' => $boughtProduct,
	        'form' => $form->createView(),
	    ));
	}
    
    /**
      * Delete a manual payment
      */
    public function deleteAction($boughtProductId)
    {	
        $boughtProduct = $this->getBoughtProduct($boughtProductId);
	    
	    $em = $this->getDoctrine()->getManager();
	    $em->remove($boughtProduct);
		$em->flush();

        $flashMessage = $this->get('translator')->trans('flash.payment.deleted', array(), 'control');
        $this->get('session')->getFlashBag()->add('success', $flashMessage);
	    return $this->redirect($this->generateUrl('control_payment_overview', array(
            'tournamentUrl'=> $this->tournament->getUrl(),
            'resetUrl' => true,
        )));
    }

    private function getBoughtProduct($boughtProductId) {
    	/* @var \TS\FinancialBundle\Entity\BoughtProduct $boughtProduct */
        $boughtProduct = $this->getDoctrine()
        	->getRepository('TSFinancialBundle:BoughtProduct')
        	->find($boughtProductId);
	    if (!$boughtProduct) {
	        throw $this->createNotFoundException('No manual payment found for id '. $boughtProductId);
	    }

        if ($boughtProduct->getCartOrder()->getPaymentInstruction()->getPaymentSystemName() != "manual") {
            throw new AccessDeniedException();
        }

	    return $boughtProduct;
    }
      
}
