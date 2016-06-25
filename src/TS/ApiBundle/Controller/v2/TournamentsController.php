<?php

namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TS\ApiBundle\Entity\Tournament;
use TS\ApiBundle\Model\TeamModel;
use TS\ApiBundle\Entity\Match;

class TournamentsController extends ApiV2MainController
{


    /**
     * Get list of tournaments
     *
     * @ApiDoc(
     *  views="mobile",
     *  section="Tournaments",
     *  description="Tournaments.list"
     * )
     */
    public function listAction() {
        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Tournament');
        $query = $repository->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC')
            ->where('site.isPublished = true')
            ->leftJoin('t.site', 'site')
            ->orderBy('t.startDateTime', 'DESC')
            ->getQuery();
        $tournaments = $query->getResult();

        $res = $this->formatTournaments($tournaments);
        return $this->handleResponse($res);
    }

    /**
     * Get list of my tournaments
     *
     * @ApiDoc(
     *  views="mobile",
     *  section="Tournaments",
     *  description="Tournaments.myList"
     * )
     */
    public function myListAction() {
        /* @var \TS\ApiBundle\Entity\OauthAccessToken $securityToken */
        $securityToken = $this->container->get('security.context')->getToken();
        $loginAccount = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($loginAccount)) {
            throw new AccessDeniedException();
        }

        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Tournament');
        $query = $repository->createQueryBuilder('t')
            ->leftJoin('t.players', 'players')
            ->leftJoin('players.person', 'person')
            ->andWhere('person = :person')
            ->setParameter('person', $loginAccount->getPerson())
            ->orderBy('t.startDateTime', 'DESC')
            ->getQuery();
        $tournaments = $query->getResult();

        $res = $this->formatTournaments($tournaments);
        return $this->handleResponse($res);
    }

    /**
     * Put tournament data in array to be returned
     * @param array $tournaments
     * @return array
     */
    private function formatTournaments($tournaments) {
        $res = array();
        foreach ($tournaments as $tournament) {
            /* @var \TS\ApiBundle\Entity\Tournament $tournament */
            $res[] = $this->formatTournament($tournament);
        }
        return $res;
    }

    /**
     * Return formatted tournament
     * @param Tournament $tournament
     * @return array
     */
    private function formatTournament(Tournament $tournament) {
        $dateText = $tournament->getStartDateTime() == $tournament->getEndDateTime() ? $tournament->getStartDateTime()->format("l j F Y") : $tournament->getStartDateTime()->format("l j F Y") .' - '. $tournament->getEndDateTime()->format("l j F Y");

        $res = array(
            'tournamentId' => $tournament->getId(),
            'url' => $tournament->getUrl(),
            'name' => $tournament->getName(),
            'startDateTime' => $tournament->getStartDateTime(),
            'endDateTime' => $tournament->getEndDateTime(),
            'dateText' => $dateText,

        );
        return $res;
    }

    /**
     * Get tournament info
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Tournaments",
     *  description="Tournaments.get",
     *  access="public"
     * )
     */
    public function getAction() {
        /* @var \TS\ApiBundle\Entity\OauthAccessToken $securityToken */
        $securityToken = $this->container->get('security.context')->getToken();
        $loginAccount = $this->container->get('security.context')->getToken()->getUser();

        $res = $this->formatTournament($this->tournament);

        $apiAllowed = $this->tournament->getAuthorization()->isApiAllowed();
        if ($apiAllowed && !empty($this->tournament->getAuthorization()->getLivePassword())) {
            $apiAllowed = 'password';
        }

        // expand $res with additional values
        $res['registrationOpenDateTime'] = $this->tournament->getAuthorization()->getCreateRegistrationStart();
        $res['registrationClosedDateTime'] = $this->tournament->getAuthorization()->getCreateRegistrationEnd();
        $res['pageHtml'] = $this->tournament->getSite()->getSitePages()->get(0)->getHtml();
        $res['isApiAllowed'] = $apiAllowed;
        $res['isLiveScoreAllowed'] = $this->tournament->getAuthorization()->isLiveScoreAllowed();
        $res['isLive2ndCallAllowed'] = $this->tournament->getAuthorization()->isLive2ndCallAllowed();
        $res['nrSets'] = $this->tournament->getNrSets();
        $res['checkScoreMin'] = $this->tournament->getCheckScoreMin();
        $res['checkScoreMax'] = $this->tournament->getCheckScoreMax();

        return $this->handleResponse($res);
    }

    /**
     * Get list of possible status options
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Tournaments",
     *  description="Tournaments.possibleStatusOptions"
     * )
     */
    public function possibleStatusOptionsAction() {
        $res = $this->tournament->getStatusOptions();
        return $this->handleResponse($res);
    }

    /**
     * Get list of used status options
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Tournaments",
     *  description="Tournaments.usedStatusOptions",
     * )
     */
    public function usedStatusOptionsAction() {
        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Player');
        $query = $repository->createQueryBuilder('p')
            ->andWhere('p.tournament = :tournament')
            ->setParameter('tournament', $this->tournament)
            ->groupBy('p.status')
            ->getQuery();
        $usedStatusPlayers = $query->getResult();

        $res = array();
        foreach ($usedStatusPlayers as $player) {
            $res[] = $player->getStatus();
        }

        return $this->handleResponse($res);
    }
}