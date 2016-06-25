<?php

namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use TS\ApiBundle\Controller\v2\ApiV2MainController;
use TS\ApiBundle\Entity\Announcement;
use TS\ApiBundle\Model\MatchListModel;

class AnnouncementsController extends ApiV2MainController
{


    /**
     * Get list of announcements
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Announcements",
     *  description="Announcements.list"
     * )
     */
    public function listAction() {
        $resArray = array(
            'secondCall' => $this->getMatchesData('secondCall'),
            'newMatches' => $this->getMatchesData('newMatch'),
        );

        return $this->handleResponse($resArray);
    }

    // get data of all matches that have to be announced
    private function getMatchesData($type) {
        $matchListModel = new MatchListModel($this->getDoctrine(), $this->tournament);

        $res = array();

        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Announcement');
        $query = $repository->createQueryBuilder('a')
            ->andWhere('a.tournament = :tournament')
            ->setParameter('tournament', $this->tournament)
            ->andWhere('a.type = :type')
            ->setParameter('type', $type)
            ->leftJoin('a.match', 'match')
            ->orderBy('a.dateTime', 'ASC')
            ->getQuery();
        $announcements = $query->getResult();

        foreach ($announcements as $announcement) { /* @var \TS\ApiBundle\Entity\Announcement $announcement */
            $announcementArray = array();
            $announcementArray['announcementId'] = $announcement->getId();
            $announcementArray['playerIds'] = $announcement->getPlayerIds();

            $match = $announcement->getMatch();
            if (!is_null($match)) {
                $location = $match->getLocation();
                if (!is_null($location)) {
                    $announcementArray['locationId'] = $location->getId();
                    $announcementArray['location'] = $location->getName();
                    $announcementArray['locationOnHold'] = $location->getOnHold();
                }
                $matchData = $matchListModel->matchData($match);
                $announcementArray = array_merge($announcementArray, $matchData);
            }
            $res[] = $announcementArray;
        }

        return $res;
    }

    /**
     * Remove announcement because it has been called
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Announcements",
     *  description="Announcements.remove",
     *  requirements = {
     *		{"name"="announcementId", "required"="true", "type"="integer", "description"="Announcement ID"}
     *  }
     * )
     */
    public function removeAction($announcementId) {
        $announcement = $this->getAnnouncement($announcementId);
        $message = 'called announcement';
        $match = $announcement->getMatch();
        if (!is_null($match) && ($announcement->getType() == 'newMatch')) {
            $message = 'called match '. $match->getLocalId();
        } else if (!is_null($match) && ($announcement->getType() == 'secondCall')) {
            $message = 'called second call for match '. $match->getLocalId();
        }

        $this->newMessage('success', 'Announcement removed', $message);
        $this->em()->remove($announcement);

        return $this->handleResponse($message);
    }
}