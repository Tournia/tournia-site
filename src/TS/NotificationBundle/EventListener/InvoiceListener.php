<?php
namespace TS\NotificationBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use TS\ApiBundle\Entity\Person;
use TS\ApiBundle\Entity\Player;
use TS\ApiBundle\Entity\Tournament;
use TS\NotificationBundle\Event\InvoiceEvent;
use TS\NotificationBundle\Event\PersonEvent;
use TS\NotificationBundle\Event\PlayerEvent;
use TS\NotificationBundle\EventListener\MainListener;


class InvoiceListener extends MainListener {

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function onPaymentNew(InvoiceEvent $event) {
        $invoice = $event->getInvoice();
/*
        $templateArray = array(
            'name' => $player->getName(false),
            'player' => $player,
            'template' => 'new',
        );

        $templateName = "TSNotificationBundle:Email:emailPlayer.html.twig";
        $this->mail($templateName, $templateArray, $player);
        $this->sendEmail($templateName, $templateArray, $player->getPerson(), $player->getTournament(), null, null, true);


        $payment = $entity;
        $subject = "New payment received for ". $payment->getPlayer()->getTournament()->getName();
        $templateName = "TSSiteBundle:Mails:paymentNew.txt.twig";
        $templateArray = array(
            'name' => $payment->getPlayer()->getName(false),
            'payment' => $payment,
        );

        if (!is_null($payment->getPlayer()->getPerson())) {
            $this->mail($subject, $payment->getPlayer()->getPerson()->getEmail(), null, $payment->getPlayer()->getTournament(), $templateName, $templateArray);
        }*/
    }

}