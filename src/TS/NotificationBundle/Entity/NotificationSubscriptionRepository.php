<?php

namespace TS\NotificationBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * NotificationSubscriptionRepository
 */
class NotificationSubscriptionRepository extends EntityRepository
{

    /**
     * Create a NotificationSubscription based on a deviceToken
     * @param \TS\ApiBundle\Entity\Person $person
     * @param string $deviceToken
     * @param string $platform
     * @return \TS\NotificationBundle\Entity\NotificationSubscription
     */
    public function create($person, $deviceToken, $platform) {
        $subscription = new NotificationSubscription();
        $subscription->setDeviceToken($deviceToken);
        $subscription->setPlatform($platform);
        $subscription->setPerson($person);
        $subscription->setEnabled(true);
        $subscription->setNewMatchEnabled(true);
        $subscription->setUpcomingMatchPeriod(3);
        $subscription->setScoreMatchEnabled(true);
        $subscription->setStartMatchEnabled(true);

        $this->getEntityManager()->persist($subscription);
        return $subscription;
    }


}
