<?php

namespace TS\FinancialBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Plugin\ErrorBuilder;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Listener for CartItems
 */
class TestPaymentPlugin extends AbstractPlugin
{

    public function checkPaymentInstruction(PaymentInstructionInterface $instruction)
    {
        $errorBuilder = new ErrorBuilder();
        $data = $instruction->getExtendedData();

        /*if (!$data->get('holder')) {
            $errorBuilder->addDataError('holder', 'form.error.required');
        }
        if ($errorBuilder->hasErrors()) {
            throw $errorBuilder->getException();
        }*/

        if (!$this->debug) {
            throw new RuntimeException("Test payment only allowed in debug");
        }
    }

    public function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        //throw new FunctionNotSupportedException('approveAndDeposit() is not supported by this plugin.');
        //$transaction->setReferenceNumber($response->getTransactionReference());
        $transaction->setProcessedAmount($transaction->getPayment()->getDepositingAmount());
        $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
        $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
    }

    public function processes($name)
    {
        return 'test_payment' === $name;
    }
}
