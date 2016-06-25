<?php
namespace TS\NotificationBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use TS\ApiBundle\Entity\Person;
use TS\ApiBundle\Entity\Player;
use TS\ApiBundle\Entity\Tournament;
use TS\NotificationBundle\Event\PersonEvent;
use TS\NotificationBundle\Event\PlayerEvent;
use TS\NotificationBundle\EventListener\MainListener;


class PlayerListener extends MainListener {

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function onPlayerNew(PlayerEvent $event) {
        if (!$event->getSendPlayerNotification()) {
            return;
        }

        $player = $event->getPlayer();

        $templateArray = array(
            'name' => $player->getName(false),
            'player' => $player,
            'template' => 'new',
        );

        $templateName = "TSNotificationBundle:Email:emailPlayer.html.twig";
        $this->mail($templateName, $templateArray, $player);
    }

    public function onPlayerChange(PlayerEvent $event)
    {
        if (!$event->getSendPlayerNotification()) {
            return;
        }

        $player = $event->getPlayer();

        $oldNewPlayer = array(
            'disciplines' => array(),
            'registrationFormValues' => array(),
        );

        // Combine disciplines from old and new player
        foreach ($player->getDisciplinePlayers() as $disciplinePlayer) {
            /* @var \TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayer */
            if (is_null($disciplinePlayer->getDiscipline())) {
                continue;
            }
            $disciplineType = $disciplinePlayer->getDiscipline()->getDisciplineType();
            $oldNewPlayer['disciplines'][$disciplineType->getName()] = array(
                'new' => $disciplinePlayer->getDiscipline()->getName(),
                'old' => null
            );
            if ($disciplineType->getPartnerRegistration()) {
                $oldNewPlayer['disciplines'][$disciplineType->getName()]['partner'] = array(
                    'new' => $disciplinePlayer->getPartner(),
                    'old' => null,
                );
            }
        }
        foreach ($event->getOriginalPlayer()->getDisciplinePlayers() as $disciplinePlayer) {
            /* @var \TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayer */
            $disciplineType = $disciplinePlayer->getDiscipline()->getDisciplineType();
            if (empty($oldNewPlayer['disciplines'][$disciplineType->getName()])) {
                $oldNewPlayer['disciplines'][$disciplineType->getName()] = array('new'=>null);
            }
            $oldNewPlayer['disciplines'][$disciplineType->getName()]['old'] = $disciplinePlayer->getDiscipline()->getName();
            if ($disciplineType->getPartnerRegistration()) {
                if (empty($oldNewPlayer['disciplines'][$disciplineType->getName()]['partner'])) {
                    $oldNewPlayer['disciplines'][$disciplineType->getName()]['partner'] = array('new'=>null);
                }
                $oldNewPlayer['disciplines'][$disciplineType->getName()]['partner']['old'] = $disciplinePlayer->getPartner();
            }
        }

        // Combine registrationFormValues from old and new player
        foreach ($player->getRegistrationFormValues() as $formValue) {
            /* @var \TS\ApiBundle\Entity\RegistrationFormValue $formValue */
            if (is_null($formValue->getValue())) {
                continue;
            }
            $oldNewPlayer['registrationFormValues'][$formValue->getField()->getName()] = array(
                'new' => $formValue->getValue(),
                'old' => null,
                'type' => $formValue->getField()->getType(),
            );
        }
        foreach ($event->getOriginalPlayer()->getRegistrationFormValues() as $formValue) {
            /* @var \TS\ApiBundle\Entity\RegistrationFormValue $formValue */
            $field = $formValue->getField()->getName();
            if (empty($oldNewPlayer['registrationFormValues'][$field])) {
                $oldNewPlayer['registrationFormValues'][$field] = array(
                    'type' => $formValue->getField()->getType(),
                    'new' => null,
                );
            }
            $oldNewPlayer['registrationFormValues'][$field]['old'] = $formValue->getValue();
        }

        $templateArray = array(
            'name' => $player->getName(false),
            'newPlayer' => $player,
            'oldPlayer' => $event->getOriginalPlayer(),
            'oldNewPlayer' => $oldNewPlayer,
        );

        $templateName = "TSNotificationBundle:Email:emailPlayerChange.html.twig";
        $this->mail($templateName, $templateArray, $player);
    }

    public function onPlayerDeleteBefore(PlayerEvent $event) {
        $player = $event->getPlayer();

        $templateArray = array(
            'name' => $player->getName(false),
            'player' => $player,
            'template' => 'delete',
        );

        $templateName = "TSNotificationBundle:Email:emailPlayer.html.twig";
        $this->mail($templateName, $templateArray, $player);
    }

    private function mail($templateName, $templateArray, $player) {
        $loggedInUser = $this->container->get('security.context')->getToken()->getUser();
        if (is_object($loggedInUser) && (is_null($player->getPerson()) || !$player->getPerson()->isEqualTo($loggedInUser->getPerson()))) {
            $templateArray['changesMadeBy'] = $loggedInUser->getPerson()->getName() .' ('. $loggedInUser->getPerson()->getEmail() .')';
        }

        $contactPlayers = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('TSApiBundle:RegistrationGroup')
            ->getAllContactPlayers($player->getRegistrationGroup());
        if (sizeof($contactPlayers) > 0) {
            $mailCC = array();
            foreach ($contactPlayers as $contactPlayer) {
                if (!is_null($contactPlayer->getPerson())) {
                    $mailCC[$contactPlayer->getPerson()->getEmail()] = $contactPlayer->getPerson()->getName();
                }
            }
        } else {
            $mailCC = null;
        }

        $mailBcc = null;
        if (!empty($player->getTournament()->getOrganizationEmailOnChange())) {
            $mailBcc = $player->getTournament()->getOrganizationEmailOnChange();
        }

        $this->sendEmail($templateName, $templateArray, $player->getPerson(), $player->getTournament(), $mailCC, $mailBcc, true);
    }


}