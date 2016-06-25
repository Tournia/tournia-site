<?php
namespace TS\NotificationBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use TS\ApiBundle\Entity\Tournament;
use TS\NotificationBundle\Event\TournamentEvent;
use TS\NotificationBundle\EventListener\MainListener;


class TournamentListener extends MainListener {

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function onTournamentNew(TournamentEvent $event) {
        $tournament = $event->getTournament();

        $to = $tournament->getEmailFrom();
        $templateName = "TSNotificationBundle:Email:emailTournamentNew.html.twig";
        $templateArray = array(
            'tournamentContactName' => $tournament->getContactName(),
            'urlTournamentWebsite' => $this->getUrl('tournament_index', array('tournamentUrl'=>$tournament->getUrl())),
            'tournamentUrl' => $tournament->getUrl(),
            'urlTournamentSettings' => $this->getUrl('settings_index', array('tournamentUrl'=>$tournament->getUrl())),
            'urlTournamentControl' => $this->getUrl('control_index', array('tournamentUrl'=>$tournament->getUrl())),
        );

        $this->sendEmail($templateName, $templateArray, $to, $tournament);

    }


}