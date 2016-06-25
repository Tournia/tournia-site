<?php
namespace TS\NotificationBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use TS\NotificationBundle\Event\RegistrationGroupEvent;


class RegistrationGroupListener extends MainListener {

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function onRegistrationGroupChange(RegistrationGroupEvent $event) {
        $registrationGroup = $event->getRegistrationGroup();

        $templateArray = array(
            'newGroup' => $registrationGroup,
            'oldGroup' => $event->getOriginalRegistrationGroup(),
        );

        $loggedInUser = $this->container->get('security.context')->getToken()->getUser();
        if (is_object($loggedInUser)) {
            $templateArray['changesMadeBy'] = $loggedInUser->getPerson()->getName() .' ('. $loggedInUser->getPerson()->getEmail() .')';
        }

        $contactPlayerNames = array();
        $contactPlayers = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('TSApiBundle:RegistrationGroup')
            ->getAllContactPlayers($registrationGroup);
        if (sizeof($contactPlayers) > 0) {
            $mailTo = array();
            foreach ($contactPlayers as $contactPlayer) {
                if (!is_null($contactPlayer->getPerson())) {
                    $mailTo[$contactPlayer->getPerson()->getEmail()] = $contactPlayer->getPerson()->getName();
                }
                $contactPlayerNames[] = $contactPlayer->getName(false);
            }
        } else {
            $mailTo = null;
        }

        $templateArray['newGroupContactPlayers'] = $contactPlayerNames;
        $templateArray['oldGroupContactPlayers'] = $event->getOriginalContactPlayerNames();

        $templateName = "TSNotificationBundle:Email:emailRegistrationGroupChange.html.twig";
        $this->sendEmail($templateName, $templateArray, $mailTo, $registrationGroup->getTournament(), null, null, true);
    }


}