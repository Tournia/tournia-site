<?php
namespace TS\FinancialBundle\Controller;

use Sylius\Bundle\CartBundle\Controller\CartController as BaseCartController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use TS\FinancialBundle\Entity\Order;
use TS\FinancialBundle\Entity\PaymentAdjustment;
use Symfony\Component\Validator\Constraints\NotBlank;
use TS\FinancialBundle\Form\Type\CartAddItemType;
use TS\FinancialBundle\Model\FinancialModel;
use Doctrine\ORM\EntityRepository;
use TS\SiteBundle\Model\PlayerModel;

class CartController extends BaseCartController
{

    /**
     * Displays Cart summary, and the possibility to pay with different payment options
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function summaryAction()
    {
        $request = $this->getRequest();
        $cart = $this->getCurrentCart();

        // define tournament for addItemForm
        $addItemTournament = null;

        // set using currency
        $currency = 'EUR';
        $tournament = null; /* @var \TS\ApiBundle\Entity\Tournament $tournament */
        if (!$cart->isEmpty()) {
            // set currency, based on tournament settings of first product in cart
            $tournament = $cart->getItems()[0]->getProduct()->getTournament();
            $currency = $tournament->getPaymentCurrency();
            $this->get('session')->set('currency', $currency);
            $addItemTournament = $tournament;
        }

        if ($request->query->has('tournament')) {
            // tournament url defined in url
            $addItemTournament = $this->getDoctrine()
                ->getRepository('TSApiBundle:Tournament')
                ->findOneByUrl($request->query->get('tournament'));
        }
        if (($addItemTournament != null) && $addItemTournament->getFinancialEnabled() && (sizeof($addItemTournament->getProducts()) > 0)) {
            $addItemForm = $this->createForm(new CartAddItemType(), null, array('em'=>$this->getDoctrine()->getManager(), 'tournament'=>$addItemTournament));

            // handle addItemForm submit
            if ('POST' === $request->getMethod() && $this->get('request')->request->has('addItemFormSubmit')) {
                $addItemForm->handleRequest($request);

                if ($addItemForm->isValid()) {
                    $addItemPlayer = $addItemForm->get('player')->getData();
                    $playerModel = new PlayerModel($addItemTournament, $this->container);
                    $playerModel->checkForPayments($addItemForm, $addItemPlayer);
                    return $this->redirect($this->generateUrl('sylius_cart_summary'));
                }
            }
            $addItemForm = $addItemForm->createView();
        } else {
            $addItemForm = null;
        }



        if (!is_object($cart->getExecPerson()) && is_object($this->getUser())){
            $cart->setExecPerson($this->getUser()->getPerson());
        }

        // finding selected payment method
        $selectedVatCountry = $this->getDoctrine()
            ->getRepository('TSFinancialBundle:VatCountry')
            ->findOneByCountryCode("");
        $selectUserVatCountry = true; // select in template the user's country based on IP address
        $selectedPaymentMethod = "paypal_express_checkout";
        if ($this->get('request')->request->has('jms_choose_payment_method')) {
            $requestPaymentMethodForm = $this->get('request')->request->get('jms_choose_payment_method');
            $selectedPaymentMethod = $requestPaymentMethodForm['method'];
            $dbVatCountry = $this->getDoctrine()
                ->getRepository('TSFinancialBundle:VatCountry')
                ->findOneByCountryCode($requestPaymentMethodForm['vatCountry']);
            if (is_object($dbVatCountry)) {
                $selectedVatCountry = $dbVatCountry;
            }
            $selectUserVatCountry = false;
        }

        // remove all existing adjustments, to prevent double fees
        foreach ($cart->getAdjustments() as $adjustment) {
            $cart->removeAdjustment($adjustment);
        }
        $cart->calculateTotal();

        // adding transaction fee
        $transactionFeeAdjustment = new PaymentAdjustment();
        $transactionFeeAdjustment->setOrder($cart);
        $transactionFeeAdjustment->setAmount($this->calculatePaymentFee($selectedPaymentMethod, $cart->getTotal()));
        $transLabel = $this->get('translator')->trans('cart.adjustment.transaction.label', array(), 'financial');
        $transactionFeeAdjustment->setLabel($transLabel);
        $transDescription = $this->get('translator')->trans('cart.adjustment.transaction.description', array(), 'financial');
        $transactionFeeAdjustment->setDescription($transDescription);
        $cart->addAdjustment($transactionFeeAdjustment);

        // Adding service fee, but only if there is a tournament, and the organization doesn't pay the service fee
        if (($tournament != null ) && (!$tournament->getOrganizationPaysServiceFee())) {
            $serviceFeeAdjustment = new PaymentAdjustment();
            $serviceFeeAdjustment->setOrder($cart);
            $serviceFeeAdjustment->setAmount($this->calculateServiceFee($tournament));
            $transLabel = $this->get('translator')->trans('cart.adjustment.service.label', array(), 'financial');
            $serviceFeeAdjustment->setLabel($transLabel);
            $transDescription = $this->get('translator')->trans('cart.adjustment.service.description', array(), 'financial');
            $serviceFeeAdjustment->setDescription($transDescription);
            $cart->addAdjustment($serviceFeeAdjustment);
        }

        // Adding VAT
        $financialModel = new FinancialModel($this->container);
        $vatAdjustment = new PaymentAdjustment();
        $vatAdjustment->setOrder($cart);
        $vatAdjustment->setAmount($financialModel->calculateVat($cart, $selectedVatCountry->getVatPercentage()));
        $vatAdjustment->setLabel($selectedVatCountry->getInvoiceDescription());
        $transDescription = $this->get('translator')->trans('cart.adjustment.vat.description', array(), 'financial');
        $vatAdjustment->setDescription($transDescription);
        $cart->addAdjustment($vatAdjustment);

        $cart->calculateTotal();
        $cartForm = $this->createForm('sylius_cart', $cart);

        $formFactory = $this->get('form.factory');
        $router = $this->get('router');
        $em = $this->getDoctrine()->getManager();
        $paymentPluginController = $this->get('payment.plugin_controller');

        if (!$cart->isEmpty()) {
            $em->flush($cart);
        }

        $paymentForm = $this->createForm('jms_choose_payment_method', null, array(
            'amount' => $cart->getTotal(),
            'currency' => $currency,
            'default_method' => $selectedPaymentMethod, // Optional
            'predefined_data' => array(
                'paypal_express_checkout' => array(
                    'return_url' => $router->generate('financial_payment_complete', array(), true),
                    'cancel_url' => $router->generate('financial_payment_cancel', array(), true)
                ),
                'mollie_ideal' => array(
                    'return_url' => $this->generateUrl('financial_payment_complete', array(), true),
                    'description' => "Tournia.net payment, ref. ". $cart->getId(),
                ),
                'mollie_creditcard' => array(
                    'return_url' => $this->generateUrl('financial_payment_complete', array(), true),
                    'description' => "Tournia.net payment, ref. ". $cart->getId(),
                ),
            ),
        ));

        // add conditions element to payment form
        $paymentForm->add('conditions', 'checkbox', array(
            'label' => 'Conditions',
            'required' => true,
            'constraints' => new NotBlank(array('message' => 'cart.conditions.notblank')),
            'attr' => array('formComment' => "cart.conditions.formComment"),
            'translation_domain' => 'financial',
        ));

        // add VAT country to payment form
        $paymentForm->add('vatCountry', 'choice', array(
            'label' => 'Country',
            'required' => true,
            'expanded' => false,
            'multiple' => false,
            'choices' => $financialModel->getVatCountryChoices(),
            'data' => $selectedVatCountry->getCountryCode(),
            'attr' => array(
                'info' => "cart.vatCountry.info",
                'formComment' => "cart.vatCountry.formComment",
            ),
            'translation_domain' => 'financial',
        ));

        if (!$this->get('kernel')->isDebug()) {
            // not debug -> remove option to make test payment
            $paymentForm->get("method")->remove(0);
        }
        if ($currency != "EUR") {
            // remove iDeal payment method, because it's not in euros
            $paymentForm->get("method")->remove(2);
            // $paymentForm->get("method")->remove(3);
        }
        // mollie creditcard not yet enabled
        $paymentForm->get("method")->remove(3);

        if ($selectedPaymentMethod != "mollie_ideal") {
            // not necessary to select a bank when paymentMethod is not iDeal
            $paymentForm->remove("data_mollie_ideal");
        }
        $paymentForm->remove("data_mollie_creditcard");

        if ('POST' === $request->getMethod() && ($this->get('request')->request->get('paymentFormPay', "") != "")) {
            $paymentForm->handleRequest($request);

            if ($paymentForm->isValid()) {
                $paymentPluginController->createPaymentInstruction($instruction = $paymentForm->getData());

                $cart->setPaymentInstruction($instruction);
                $em->flush($cart);

                return new RedirectResponse($router->generate('financial_payment_complete'));
            }
        }

        return $this->render("TSFinancialBundle:Cart:summary.html.twig", array(
            'cart' => $cart,
            'cartForm' => $cartForm->createView(),
            'paymentForm' => $paymentForm->createView(),
            'addItemForm' => $addItemForm,
            'addItemTournament' => $addItemTournament,
            'selectUserVatCountry' => $selectUserVatCountry,
        ));
    }

    /**
     * Calculates the payment fee. This is dependent on the selected payment method and order amount
     * @param string $paymentMethod
     * @param int $amount
     * @return int
     */
    private function calculatePaymentFee($paymentMethod, $cartAmount) {
        if ($paymentMethod == "paypal_express_checkout") {
            $amountPaypal = ($cartAmount + 35) / 0.966;
            $fee = $amountPaypal - $cartAmount;
        } else if ($paymentMethod == "mollie_ideal") {
            $fee = 45;
        } else if ($paymentMethod == "mollie_creditcard") {
            $amountCreditcard = ($cartAmount + 25) / 0.972;
            $fee = $amountCreditcard - $cartAmount;
        } else if ($paymentMethod == "test_payment") {
            $fee = 123;
        } else {
            // possible hack attempt to select non-existing payment method -> return highest amount
            $fee = 9999;
        }

        return round($fee);
    }

    /**
     * Calculates the service fee. This is dependent on the tournament settings
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return int
     */
    private function calculateServiceFee() {
        return 50;
    }


}