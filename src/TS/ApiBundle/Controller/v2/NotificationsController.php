<?php

namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpKernel\Exception\HttpException;
use TS\ApiBundle\Model\RankingModel;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class NotificationsController extends ApiV2MainController
{


    /**
     * Get notifications settings of all devices
     *
     * @ApiDoc(
     *  views="mobile",
     *  section="Notifications",
     *  description="Notifications.list",
     *  resource="private"
     * )
     */
    public function listAction() {
        $notificationSubscriptions = $this->getDoctrine()
            ->getRepository('TSNotificationBundle:NotificationSubscription')
            ->findBy(array('person' => $this->getPerson()));

        $res = array();
        foreach ($notificationSubscriptions as $subscription) {
            $res[] = $this->generateGetData($subscription);
        }
        return $this->handleResponse($res);
    }

    /**
     * Get notifications settings of a device
     *
     * @ApiDoc(
     *  views="mobile",
     *  description="Notifications.get",
     *  section="Notifications",
     *  requirements = {
     *      {"name"="deviceToken", "dataType"="string", "description"="The device token"},
     *  },
     * )
     */
    public function getAction($deviceToken) {
        $notificationSubscription = $this->getNotificationSubscription($deviceToken);

        $res = $this->generateGetData($notificationSubscription);
        return $this->handleResponse($res);
    }

    /**
     * Create a notification subscription
     *
     * @ApiDoc(
     *  views="mobile",
     *  description="Notifications.post",
     *  section="Notifications",
     *  requirements = {
     *      {"name"="deviceToken", "dataType"="string", "description"="The device token"},
     *  },
     *  filters = {
     *		{"name"="platform", "required"="true", "type"="string", "description"="Platform for notifications, can be iOS or Android", "default"="iOS"},
     *  }
     * )
     */
    public function postAction($deviceToken) {
        $platform = strtolower($this->getParam('platform'));
        /* @var \TS\NotificationBundle\Entity\NotificationSubscriptionRepository $notifRepos */
        $notifRepos = $this->getDoctrine()
            ->getRepository('TSNotificationBundle:NotificationSubscription');
        /* @var \TS\NotificationBundle\Entity\NotificationSubscription $notificationSubscription */
        $notificationSubscription = $notifRepos->findOneBy(array('deviceToken' => $deviceToken));
        if ($notificationSubscription) {
            // Prevent other NotificationSubscriptions with same deviceToken. Device has a new user.
            $notificationSubscription->setPerson($this->getPerson());
            // Save (possibly new) platform
            $notificationSubscription->setPlatform($platform);
        } else {
            $notificationSubscription = $notifRepos->create($this->getPerson(), $deviceToken, $platform);
        }

        $res = $this->generateGetData($notificationSubscription);
        return $this->handleResponse($res);
    }

    /**
     * @param \TS\NotificationBundle\Entity\NotificationSubscription $notificationSubscription
     * @return array
     */
    private function generateGetData($notificationSubscription) {
        return array(
            "deviceToken" => $notificationSubscription->getDeviceToken(),
            "platform" => $notificationSubscription->getPlatform(),
            "enabled" => $notificationSubscription->getEnabled(),
            "upcomingMatch" => $notificationSubscription->getUpcomingMatchPeriod(),
            "newMatch" => $notificationSubscription->getNewMatchEnabled(),
            "startMatch" => $notificationSubscription->getStartMatchEnabled(),
            "scoreMatch" => $notificationSubscription->getScoreMatchEnabled(),
        );
    }

    /**
     * Enable/disable notifications for a user
     *
     * @ApiDoc(
     *  views="mobile",
     *  description="Notifications.enabled",
     *  section="Notifications",
     *  requirements = {
     *      {"name"="deviceToken", "dataType"="string", "description"="The device token"},
     *  },
     *  filters = {
     *		{"name"="enabled", "required"="true", "type"="boolean", "description"="Whether notifications are enabled/disabled"}
     *  }
     * )
     */
    public function enabledAction($deviceToken) {
        $subscription = $this->getNotificationSubscription($deviceToken);
        $enabled = $this->getParam('enabled');

        $subscription->setEnabled($enabled);
        $enabledTxt = $enabled ? "enabled" : "disabled";
        $res = "Notifications ". $enabledTxt ." on ". $subscription->getPlatform() ." for ". $deviceToken;
        return $this->handleResponse($res);
    }

    /**
     * Set period for notifications for upcoming match
     *
     * @ApiDoc(
     *  views="mobile",
     *  description="Notifications.upcomingMatch",
     *  section="Notifications",
     *  requirements = {
     *      {"name"="deviceToken", "dataType"="string", "description"="The device token"},
     *  },
     *  filters = {
     *		{"name"="period", "required"="true", "type"="integer", "description"="When the notification must be pushed, -1 for never"}
     *  }
     * )
     */
    public function upcomingMatchAction($deviceToken) {
        $subscription = $this->getNotificationSubscription($deviceToken);
        $period = $this->getParam('period');

        $subscription->setUpcomingMatchPeriod($period);
        $res = "Notifications upcoming match ". $period ." on ". $subscription->getPlatform() ." for ". $deviceToken;
        return $this->handleResponse($res);
    }

    /**
     * Enable notifications for new match
     *
     * @ApiDoc(
     *  views="mobile",
     *  description="Notifications.newMatch",
     *  section="Notifications",
     *  requirements = {
     *      {"name"="deviceToken", "dataType"="string", "description"="The device token"},
     *  },
     *  filters = {
     *		{"name"="enabled", "required"="true", "type"="boolean", "description"="Whether the notification is enabled"}
     *  }
     * )
     */
    public function newMatchAction($deviceToken) {
        $subscription = $this->getNotificationSubscription($deviceToken);
        $enabled = $this->getParam('enabled');

        $subscription->setNewMatchEnabled($enabled);
        $enabledTxt = $enabled ? "enabled" : "disabled";
        $res = "Notifications new match ". $enabledTxt ." on ". $subscription->getPlatform() ." for ". $deviceToken;
        return $this->handleResponse($res);
    }

    /**
     * Enable notifications for start of match
     *
     * @ApiDoc(
     *  views="mobile",
     *  description="Notifications.startMatch",
     *  section="Notifications",
     *  requirements = {
     *      {"name"="deviceToken", "dataType"="string", "description"="The device token"},
     *  },
     *  filters = {
     *		{"name"="enabled", "required"="true", "type"="boolean", "description"="Whether the notification is enabled"}
     *  }
     * )
     */
    public function startMatchAction($deviceToken) {
        $subscription = $this->getNotificationSubscription($deviceToken);
        $enabled = $this->getParam('enabled');

        $subscription->setStartMatchEnabled($enabled);
        $enabledTxt = $enabled ? "enabled" : "disabled";
        $res = "Notifications start match ". $enabledTxt ." on ". $subscription->getPlatform() ." for ". $deviceToken;
        return $this->handleResponse($res);
    }

    /**
     * Enable notifications for scoring of a match
     *
     * @ApiDoc(
     *  views="mobile",
     *  description="Notifications.scoreMatch",
     *  section="Notifications",
     *  requirements = {
     *      {"name"="deviceToken", "dataType"="string", "description"="The device token"},
     *  },
     *  filters = {
     *		{"name"="enabled", "required"="true", "type"="boolean", "description"="Whether the notification is enabled"}
     *  }
     * )
     */
    public function scoreMatchAction($deviceToken) {
        $subscription = $this->getNotificationSubscription($deviceToken);
        $enabled = $this->getParam('enabled');

        $subscription->setScoreMatchEnabled($enabled);
        $enabledTxt = $enabled ? "enabled" : "disabled";
        $res = "Notifications scoring of match ". $enabledTxt ." on ". $subscription->getPlatform() ." for ". $deviceToken;
        return $this->handleResponse($res);
    }


    private function mail($text) {
        $message = \Swift_Message::newInstance()
            ->setSubject('Tournia notification message')
            ->setFrom("info@tournia.net")
            ->setTo('app@tournia.net')
            ->setBody($text)
        ;
        $this->get('mailer')->send($message);
    }

    /**
     * @return \TS\ApiBundle\Entity\Person
     * @throws Exception when not logged in
     */
    private function getPerson() {
        if (!$this->getUser()) {
            throw new AccessDeniedException();
        }
        return $this->getUser()->getPerson();
    }

    /**
     * Get NotificationSubscription based on deviceToken and $this->getPerson()
     * @param $deviceToken
     * @return \TS\NotificationBundle\Entity\NotificationSubscription
     * @throws Exception When not found
     */
    private function getNotificationSubscription($deviceToken) {
        /* @var \TS\NotificationBundle\Entity\NotificationSubscription $notificationSubscription */
        $notificationSubscription = $this->getDoctrine()
            ->getRepository('TSNotificationBundle:NotificationSubscription')
            ->findOneBy(array('person' => $this->getPerson(), 'deviceToken' => $deviceToken));
        if (!$notificationSubscription) {
            throw $this->throwError('No subscription found for deviceToken '. $deviceToken, self::$ERROR_NOT_FOUND);
        }
        return $notificationSubscription;
    }
}