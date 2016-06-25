<?php
namespace TS\NotificationBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use TS\ApiBundle\Entity\Person;
use TS\ApiBundle\Entity\Player;
use TS\ApiBundle\Entity\Tournament;
use TS\NotificationBundle\Event\PersonEvent;
use TS\NotificationBundle\EventListener\MainListener;


class PersonListener extends MainListener {

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function onPersonNew(PersonEvent $event) {
        $person = $event->getPerson();

        /* @var \TS\ApiBundle\Entity\LoginAccount $loginAccount */
        $loginAccount = $person->getLoginAccounts()[0];

        $authorizationType = null;
        $authorizationTo = null;
        $tournament = null;
        if ($event->getAddedAuthorization() instanceof Player) {
            $authorizationType = "player";
            $player = $event->getAddedAuthorization();
            $tournament = $player->getTournament();
            $transForTournament =  $this->container->get('translator')->trans('email.person.forTournament', array(), 'notificationEmail');
            $authorizationTo = $player->getName() .' '. $transForTournament .' '. $tournament->getName();
        } else if ($event->getAddedAuthorization() instanceof Tournament) {
            $authorizationType = "tournament";
            $tournament = $event->getAddedAuthorization();
            $authorizationTo = $tournament->getName();
        }

        $templateName = "TSNotificationBundle:Email:emailPersonNew.html.twig";
        $templateArray = array(
            'username' => $loginAccount->getUsername(),
            'password' => $loginAccount->getPlainPassword(),
            'authorizationObject' => $event->getAddedAuthorization(),
            'authorizationType' => $authorizationType,
            'authorizationTo' => $authorizationTo,
        );

        $this->sendEmail($templateName, $templateArray, $person, $tournament);
    }

    public function onPersonAuthorized(PersonEvent $event) {
        $person = $event->getPerson();

        $authorizationType = null;
        $authorizationTo = null;
        $tournament = null;
        if ($event->getAddedAuthorization() instanceof Player) {
            $authorizationType = "player";
            $player = $event->getAddedAuthorization();
            $tournament = $player->getTournament();
            $transForTournament =  $this->container->get('translator')->trans('email.person.forTournament', array(), 'notificationEmail');
            $authorizationTo = $player->getName() .' '. $transForTournament .' '. $tournament->getName();
        } else if ($event->getAddedAuthorization() instanceof Tournament) {
            $authorizationType = "tournament";
            $tournament = $event->getAddedAuthorization();
            $authorizationTo = $tournament->getName();
        }

        $templateName = "TSNotificationBundle:Email:emailPersonAuthorized.html.twig";
        $templateArray = array(
            'authorizationObject' => $event->getAddedAuthorization(),
            'authorizationType' => $authorizationType,
            'authorizationTo' => $authorizationTo,
        );

        $this->sendEmail($templateName, $templateArray, $person, $tournament);
    }


}