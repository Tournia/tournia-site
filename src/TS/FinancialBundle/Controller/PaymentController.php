<?php

namespace TS\FinancialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use JMS\Payment\CoreBundle\PluginController\Result;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Forms;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sylius\Bundle\OrderBundle\Model\OrderInterface;

use TS\FinancialBundle\Entity\BoughtProduct;
use TS\FinancialBundle\Entity\Cart;
use TS\FinancialBundle\Entity\Invoice;
use TS\FinancialBundle\Entity\Order;

class PaymentController extends Controller
{

    public function completeAction()
    {
        $cart = $this->get('sylius.cart_provider')->getCart();

        if (!is_object($cart->getPaymentInstruction())) {
            throw new \RuntimeException('No cart present, possible reload of page or hack attempt');
        }

        // set currency, based on tournament settings of first product in cart
        $tournament = $cart->getItems()[0]->getProduct()->getTournament();
        $this->get('session')->set('currency', $tournament->getPaymentCurrency());

        $paymentPluginController = $this->get('payment.plugin_controller');

        $instruction = $cart->getPaymentInstruction();
        $payment = null; /* @var \JMS\Payment\CoreBundle\Entity\Payment $payment */
        if (null === $pendingTransaction = $instruction->getPendingTransaction()) {
            $amount = ($instruction->getAmount() - $instruction->getDepositedAmount()) / 100; // fix bug that uses amount as a decimal amount
            $payment = $paymentPluginController->createPayment($instruction->getId(), $amount);
        } else {
            $payment = $pendingTransaction->getPayment();
        }

        $result = $paymentPluginController->approveAndDeposit($payment->getId(), $payment->getTargetAmount());
        if (Result::STATUS_PENDING === $result->getStatus()) {
            $ex = $result->getPluginException();

            if ($ex instanceof ActionRequiredException) {
                $action = $ex->getAction();

                if ($action instanceof VisitUrl) {
                    return new RedirectResponse($action->getUrl());
                }

                throw $ex;
            }
        } else if (Result::STATUS_SUCCESS !== $result->getStatus()) {
            $this->get('session')->getFlashBag()->add('error', 'Transaction was not successful: '.$result->getReasonCode());
            return $this->redirect($this->generateUrl('front_index'));
        }

        $cartTotal = $cart->getTotal() / 100; // fix bug that uses amount as a decimal amount
        if (($cartTotal != $payment->getApprovedAmount()) && ($cartTotal != $payment->getApprovingAmount())) {
            $this->get('session')->getFlashBag()->add('warning', "Cart total does not match payment amount. Please contact support@tournia.net to verify if your payment succeeded.");

            // send mail
            $mailText = "Cart total does not match payment amount.
            CartTotal: ". $cartTotal ."
            Approved amount: ". $payment->getApprovedAmount() ."
            Approving amount: ". $payment->getApprovingAmount() .".
            For payment ID: ". $payment->getId() ." and CartId: ". $cart->getId() .".";
            $message = \Swift_Message::newInstance()
                ->setSubject('Tournia possible hack attempt')
                ->setFrom('info@tournia.net')
                ->setTo('support@tournia.net')
                ->setBody($mailText);
            $this->get('mailer')->send($message);
            //throw new \RuntimeException('Cart total does not match payment amount, possible hack attempt');
        }

        // payment was successful, do something interesting with the order
        $cart->setState(OrderInterface::STATE_CONFIRMED);
        $this->saveSuccessfulPayment($cart);

        // empty cart
        $this->get('sylius.cart_provider')->abandonCart();

        $em = $this->getDoctrine()->getManager();
        $em->flush($cart);

        $flashMessage = $this->get('translator')->trans('flash.payment.complete.success', array(), 'financial');
        $this->get('session')->getFlashBag()->add('success', $flashMessage);
        return $this->redirect($this->generateUrl('front_index'));
    }

    public function cancelAction()
    {
        $flashMessage = $this->get('translator')->trans('flash.payment.cancelled', array(), 'financial');
        $this->get('session')->getFlashBag()->add('notice', $flashMessage);
        return $this->redirect($this->generateUrl('sylius_cart_summary'));
    }

    private function saveSuccessfulPayment(Cart $cart) {
        $logger = $this->get('logger');
        $logger->info("Successful ". $cart->getPaymentInstruction()->getPaymentSystemName() ." payment: ". $cart->getPaymentInstruction()->getCurrency() ." ". $cart->getPaymentInstruction()->getDepositedAmount());

        $em = $this->getDoctrine()->getManager();

        foreach ($cart->getItems() as $item) {
            $boughtProduct = new BoughtProduct();
            $boughtProduct->setQuantity($item->getQuantity());
            $boughtProduct->setAmount($item->getTotal());
            $boughtProduct->setPlayer($item->getPlayer());
            $tournament = $item->getProduct()->getTournament();
            $boughtProduct->setTournament($tournament);
            $boughtProduct->setName($item->getProduct()->getName());
            $boughtProduct->setCartOrder($cart);

            if ($this->get('kernel')->isDebug()) {
                //$boughtProduct->setAmount(0);
                $boughtProduct->setName("TEST: ". $boughtProduct->getName());
            }
            $em->persist($boughtProduct);
            
            // automatic status update for player
            if ($tournament->getPaymentUpdateStatus() && ($item->getPlayer()->getStatus() == $tournament->getPaymentUpdateFromStatus())) {
                $item->getPlayer()->setStatus($tournament->getPaymentUpdateToStatus());
            }
        }

        // create invoice
        $invoice = new Invoice();
        $invoice->setCartOrder($cart);
        $cart->setInvoice($invoice);
        $em->persist($invoice);

        // saving the payment to the database
        $em->flush();
    }
}
