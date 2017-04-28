<?php
namespace TS\NotificationBundle\EventListener;

use RMS\PushNotificationsBundle\Message\AndroidMessage;
use RMS\PushNotificationsBundle\Message\iOSMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TS\ApiBundle\Entity\Match;
use TS\ApiBundle\Entity\Person;
use TS\ApiBundle\Entity\Player;
use TS\ApiBundle\Entity\Tournament;
use TS\ApiBundle\Model\MatchListModel;
use TS\NotificationBundle\Entity\NotificationLog;
use TS\NotificationBundle\Event\MatchEvent;
use TS\NotificationBundle\Event\PersonEvent;
use TS\NotificationBundle\EventListener\MainListener;


class MatchListener extends MainListener {

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function onMatchNew(MatchEvent $event) {
        $match = $event->getMatch();

        // look for notification subscriptions
        $query = $this->getMatchQuery($match)
            ->andWhere('ns.newMatchEnabled = true')
            ->getQuery();
        $notificationSubscriptions = $query->getResult();

        foreach ($notificationSubscriptions as $notificationSubscription) {
            $messageText = 'New match '. $match->getLocalId() .': '. $match->getTeam1()->getName() .' vs. '. $match->getTeam2()->getName();
            $this->sendMessage($notificationSubscription, $messageText, $match->getTournament());
        }
    }

    public function onMatchScore(MatchEvent $event) {
        $match = $event->getMatch();

        // look for notification subscriptions
        $query = $this->getMatchQuery($match)
            ->andWhere('ns.scoreMatchEnabled = true')
            ->getQuery();
        $notificationSubscriptions = $query->getResult();

        foreach ($notificationSubscriptions as $notificationSubscription) {
            $messageText = 'Score of match '. $match->getLocalId() .': '. $match->getScoreTextual(true) .' ('. $match->getTeam1()->getName() .' vs. '. $match->getTeam2()->getName() .')';
            $this->sendMessage($notificationSubscription, $messageText, $match->getTournament());
        }
    }

    public function onMatchStatus(MatchEvent $event) {
        $match = $event->getMatch();

        if ($match->getStatus() == Match::STATUS_PLAYING) {
            // look for notification subscriptions
            $query = $this->getMatchQuery($match)
                ->andWhere('ns.startMatchEnabled = true')
                ->getQuery();
            $notificationSubscriptions = $query->getResult();

            foreach ($notificationSubscriptions as $notificationSubscription) {
                $locationName = is_null($match->getLocation()) ? "undefined location" : $match->getLocation()->getName();
                $messageText = 'Start match '. $match->getLocalId() .' on '. $locationName .' ('. $match->getTeam1()->getName() .' vs. '. $match->getTeam2()->getName() .')';
                $this->sendMessage($notificationSubscription, $messageText, $match->getTournament());
            }
        }

        // check for upcomingMatches notifications
        $this->container->get('doctrine')->getManager()->flush();
        $matchListModel = new MatchListModel($this->container->get('doctrine'), $match->getTournament());
        $upcomingMatches = $matchListModel->getStatusMatchesData(array(Match::STATUS_READY), 5);
        $upcomingMatchIndex = 1;
        foreach ($upcomingMatches as $upcomingMatch) {
            // look for notification subscriptions
            $query = $this->getMatchQuery($upcomingMatch)
                ->andWhere('ns.upcomingMatchPeriod = :upcomingMatchIndex')
                ->setParameter('upcomingMatchIndex', $upcomingMatchIndex)
                ->getQuery();
            $notificationSubscriptions = $query->getResult();

            foreach ($notificationSubscriptions as $notificationSubscription) {
                $messageText = 'Match '. $upcomingMatch->getLocalId() .' starts in '. $upcomingMatchIndex .' matches ('. $upcomingMatch->getTeam1()->getName() .' vs. '. $upcomingMatch->getTeam2()->getName() .')';
                $this->sendMessage($notificationSubscription, $messageText, $upcomingMatch->getTournament());
            }

            $upcomingMatchIndex++;
        }

    }

    private function getMatchQuery($match) {
        $repository = $this->getDoctrine()
            ->getRepository('TSNotificationBundle:NotificationSubscription');
        $query = $repository->createQueryBuilder('ns')
            ->select('ns')
            ->where('(team1Matches = :match OR team2Matches = :match) AND ((team1Persons = ns.person) OR (team2Persons = ns.person))')
            ->andWhere('ns.enabled = true')
            ->setParameter('match', $match)
            ->leftJoin('ns.person', 'team1Persons')
            ->leftJoin('ns.person', 'team2Persons')
            ->leftJoin('team1Persons.players', 'team1Players')
            ->leftJoin('team2Persons.players', 'team2Players')
            ->leftJoin('team1Players.teams', 'team1Teams')
            ->leftJoin('team2Players.teams', 'team2Teams')
            ->leftJoin('team1Teams.matches1', 'team1Matches')
            ->leftJoin('team2Teams.matches2', 'team2Matches')
            ->groupBy('ns');
        return $query;
    }

    /**
     * Send the notification
     * @param \TS\NotificationBundle\Entity\NotificationSubscription $notificationSubscription
     * @param String $messageText
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     */
    private function sendMessage($notificationSubscription, $messageText, $tournament) {
        return; // TODO messages disabled
        $notificationSubscription->getDeviceToken();
        if ($notificationSubscription->getPlatform() == 'ios') {
            $message = new iOSMessage();
            $message->setAPSSound('default');
            $message->setAPSBadge(1);
        } else {
            $message = new AndroidMessage();
            $message->setGCM(true);
        }

        $message->setMessage($messageText);
        $message->setDeviceIdentifier($notificationSubscription->getDeviceToken());

        $this->container->get('rms_push_notifications')->send($message);

        //$logger = $this->container->get('logger');
        //$logger->info('Sending message '. $message .' to : '. $notificationSubscription->getDeviceToken());

        // Save notification in log
        $notificationLog = new NotificationLog();
        $notificationLog->setType('mobilePush');
        $notificationLog->setPerson($notificationSubscription->getPerson());
        $notificationLog->setDeviceToken($notificationSubscription->getDeviceToken());
        $notificationLog->setMessage($messageText);
        $notificationLog->setPlatform($notificationSubscription->getPlatform());
        $notificationLog->setTournament($tournament);

        $em = $this->container->get('doctrine')->getManager();
        $em->persist($notificationLog);
        $em->flush();
    }

}