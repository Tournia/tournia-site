<?php

namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use TS\ApiBundle\Controller\v2\ApiV2MainController;
use TS\ApiBundle\Entity\Announcement;
use TS\ApiBundle\Entity\Match;

use Symfony\Component\HttpFoundation\Request;

use TS\ApiBundle\Model\MatchListModel;
use TS\NotificationBundle\Event\MatchEvent;
use TS\NotificationBundle\NotificationEvents;


class MatchesController extends ApiV2MainController
{

    /**
     * Get information about specific match
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.get"
     * )
     */
    public function getAction($matchId) {
        $match = $this->getMatch($matchId);
        $res = $this->prepareMatchData($match);
        return $this->handleResponse($res);
    }

    /**
     * Returns an array with match data
     */
    private function prepareMatchData($match) {
        $resArray = array(
            "id" => $match->getId(),
            "localId" => $match->getLocalId(),
            "poolId" => $match->getPool()->getId(),
            "team1Id" => '',
            "team1Name" => '',
            "team2Id" => '',
            "team2Name" => '',
            'poolName' => $match->getPool()->getName(),
            "round" => $match->getRound(),
            "status" => $match->getStatus(),
            "nonreadyReason" => $match->getNonreadyReason(),
            "priority" => $match->getPriority(),
            "scoreText" => $match->getScoreTextual(),
            "score" => $match->getScore(),
        );
        if (!is_null($match->getTeam1())) {
            $resArray['team1Id'] = $match->getTeam1()->getId();
            $resArray['team1Name'] = $match->getTeam1()->getName();
        }
        if (!is_null($match->getTeam2())) {
            $resArray['team2Id'] = $match->getTeam2()->getId();
            $resArray['team2Name'] = $match->getTeam2()->getName();
        }
        return $resArray;
    }


    /**
     * Get list of matches
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.list"
     * )
     */
    public function listAllAction(Request $request) {
        $matchListModel = new MatchListModel($this->getDoctrine(), $this->tournament);
        $resArray = $matchListModel->getAllMatchesData($request);
        return $this->handleResponse($resArray);
    }

    /**
     * Get list of matches for a specific pool (and round)
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.listPool"
     * )
     */
    public function listPoolAction($poolId, $round) {
        $round = ($round == 'all') ? null : $round;
        $matchListModel = new MatchListModel($this->getDoctrine(), $this->tournament);
        $pool = $this->getPool($poolId);
        $resArray = $matchListModel->getPoolMatchesData($pool, $round);
        return $this->handleResponse($resArray);
    }

    /**
     * Get list of matches for a specific status
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.listStatus",
     *  filters = {
     *		{"name"="status", "required"="true", "type"="string or array", "description"="Status to look for", "pattern"="array(postponed|ready|playing|finished|played)"},
     *		{"name"="limit", "required"="false", "type"="integer", "description"="Number of results to return", "default"="25"},
     *		{"name"="startPos", "required"="false", "type"="integer", "description"="Starting position of results to return", "default"="0"},
     *		{"name"="sortOrder", "required"="false", "type"="string", "description"="Sort order of matches to return", "default"="ASC"}
     *  }
     * )
     */
    public function listStatusAction() {
        $status = $this->getParam('status');
        if (!is_array($status)) {
            $status = array($status);
        }
        $statusArray = array();
        foreach ($status as $statusText) {
            if ($statusText == "postponed") {
                $statusArray[] = Match::STATUS_POSTPONED;
            } else if ($statusText == "ready") {
                $statusArray[] = Match::STATUS_READY;
            } else if ($statusText == "playing") {
                $statusArray[] = Match::STATUS_PLAYING;
            } else if ($statusText == "finished") {
                $statusArray[] = Match::STATUS_FINISHED;
            } else if ($statusText == "played") {
                $statusArray[] = Match::STATUS_PLAYED;
            } else {
                $this->throwError("Status ". $statusText ." is not a recognized status", self::$ERROR_BAD_REQUEST);
            }
        }

        $matchListModel = new MatchListModel($this->getDoctrine(), $this->tournament);
        $resMatches = $matchListModel->getStatusMatchesData($statusArray, $this->getParam('limit', false, null), $this->getParam('startPos', false, null), $this->getParam('sortOrder', false, "ASC"));
        $resArray = $matchListModel->matchDataInArray($resMatches);
        return $this->handleResponse($resArray);
    }

    /**
     * Get list of matches for a specific player
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.listPlayer",
     *  requirements = {
     *		{"name"="playerId", "dataType"="Integer", "description"="Player to get matches from"}
     *  }
     * )
     */
    public function listPlayerAction($playerId) {
        $matchListModel = new MatchListModel($this->getDoctrine(), $this->tournament);
        $resArray = $matchListModel->getPlayerMatchesData($playerId);
        return $this->handleResponse($resArray);
    }

    /**
     * Get list of playing matches
     * Matches are returned in order of: first matches that have no specific location, then ordered by location position
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.listPlaying"
     * )
     */
    public function listPlayingAction() {
        $matchListModel = new MatchListModel($this->getDoctrine(), $this->tournament);
        $resArray = $matchListModel->getPlayingMatchesData();
        return $this->handleResponse($resArray);
    }

    /**
     * Get list of playing matches for specific player
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.listSearch",
     *  filters = {
     *		{"name"="searchQuery", "required"="true", "type"="string", "description"="Player name to get matches from"}
     *  }
     * )
     */
    public function listSearchAction() {
        $matchListModel = new MatchListModel($this->getDoctrine(), $this->tournament);
        $resArray = $matchListModel->getSearchMatchesData($this->getParam('searchQuery'));
        return $this->handleResponse($resArray);
    }


    /**
     * Create a new match
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.create",
     *  filters = {
     *		{"name"="poolId", "required"="true", "type"="integer", "description"="Pool ID"},
     *		{"name"="round", "required"="true", "type"="string", "description"="Round"},
     *		{"name"="team1", "required"="false", "type"="integer", "description"="Team 1 ID (empty for no team)", "default"="No team"},
     *		{"name"="team2", "required"="false", "type"="integer", "description"="Team 2 ID (empty for no team)", "default"="No team"},
     *		{"name"="priority", "required"="false", "type"="boolean", "description"="Priority match or not", "default"="false"},
     *  }
     * )
     */
    public function createAction() {
        $pool = $this->getPool($this->getParam('poolId'));
        $round = $this->getParam('round');
        $team1 = null;
        if ($this->getParam('team1', false, '') != '') {
            $team1 = $this->getTeam($this->getParam('team1'));
        }
        $team2 = null;
        if ($this->getParam('team2', false, '') != '') {
            $team2 = $this->getTeam($this->getParam('team2'));
        }

        $match = new Match();
        $match->setTournament($this->tournament);
        // lookup new localId
        $repository = $this->getDoctrine()->getRepository('TSApiBundle:Match');
        $newLocalId = intval($repository->getLastLocalId($this->tournament)) + 1;
        $match->setLocalId($newLocalId);

        $match->setPool($pool);
        $match->setTeam1($team1);
        $match->setTeam2($team2);
        $match->setRound($round);
        $match->setPriority($this->getParam('priority', false, 'false') == "true");

        $team1Name = 'none';
        if ($team1 != null) {
            $team1Name = $team1->getName();
        }
        $team2Name = 'none';
        if ($team2 != null) {
            $team2Name = $team2->getName();
        }

        $this->em()->persist($match);
        $this->em()->flush();

        // trigger new Match event
        $event = new MatchEvent($match);
        $this->get('event_dispatcher')->dispatch(NotificationEvents::MATCH_NEW, $event);

        $message = 'added match: '. $match->getLocalId() .' to pool: '. $pool->getName() .' round: '. $round .' and team 1: '. $team1Name .' and team 2: '. $team2Name;
        $res = array('message'=>$message, 'id'=>$match->getId());
        $this->newMessage('success', 'Match added', $message);
        return $this->handleResponse($res);
    }


    /**
     * Edit a match
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.edit",
     *  requirements = {
     *     {"name"="matchId", "dataType"="Integer", "description"="Match ID"}
     *  },
     *  filters = {
     *		{"name"="poolId", "required"="false", "type"="integer", "description"="new pool ID", "default"="Current poolId"},
     *		{"name"="round", "required"="false", "type"="string", "description"="new round", "default"="Current round"},
     *		{"name"="team1", "required"="false", "type"="integer", "description"="new team 1 ID (empty for no team)", "default"="Current team1Id"},
     *		{"name"="team2", "required"="false", "type"="integer", "description"="new team 2 ID (empty for no team)", "default"="Current team2Id"},
     *		{"name"="localId", "required"="false", "type"="integer", "description"="new local ID", "default"="Current localId"},
     *		{"name"="priority", "required"="false", "type"="boolean", "description"="Priority match or not", "default"="Current priority"},
     *  }
     * )
     */
    public function editAction($matchId) {
        $match = $this->getMatch($matchId);

        $pool = $this->getPool($this->getParam('poolId', false, $match->getPool()->getId()));
        $round = $this->getParam('round', false, $match->getRound());

        $team1 = $this->getParam('team1', false, $match->getTeam1()->getId());
        if (!is_object($team1)) {
            // team1 is filled in string -> locate team object
            if ($team1 == '') {
                $team1 = null;
            } else {
                $team1 = $this->getTeam($team1);
            }
        }
        $team2 = $this->getParam('team2', false, $match->getTeam2()->getId());
        if (!is_object($team2)) {
            // team2 is filled in string -> locate team object
            if ($team2 == '') {
                $team2 = null;
            } else {
                $team2 = $this->getTeam($team2);
            }
        }

        $newLocalId = $this->getParam('localId', false, $match->getLocalId());
        if (!is_numeric($newLocalId)) {
            $this->throwError('Match number: '. $newLocalId .' is not a number', self::$ERROR_BAD_REQUEST);
        }
        $match->setLocalId($newLocalId);

        $match->setPool($pool);
        $match->setTeam1($team1);
        $match->setTeam2($team2);
        $match->setRound($round);
        $match->setPriority($this->getParam('priority', false, $match->getPriority()) == "true");

        $team1Name = 'none';
        if ($team1 != null) {
            $team1Name = $team1->getName();
        }
        $team2Name = 'none';
        if ($team2 != null) {
            $team2Name = $team2->getName();
        }

        $res = 'edited match '. $match->getLocalId();
        $this->em()->persist($match);

        $this->newMessage('success', 'Match changes saved', $res);
        return $this->handleResponse($res);
    }


    /**
     * Remove a match
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.remove",
     *  requirements = {
     *		{"name"="matchId", "dataType"="Integer", "description"="Match ID"}
     *  }
     * )
     */
    public function removeAction($matchId) {
        $match = $this->getMatch($matchId);
        $res = 'removed match '. $match->getLocalId();

        $this->em()
            ->getRepository('TSApiBundle:Match')
            ->remove($match);

        $this->newMessage('success', 'Deleted match', $res);
        return $this->handleResponse($res);
    }


    /**
     * Start a match. Will bring match to next status of playing.
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.start",
     *  requirements = {
     *		{"name"="matchId", "dataType"="Integer", "description"="Match ID"}
     *  },
     *  filters = {
     *		{"name"="locationId", "required"="false", "type"="integer", "description"="Location ID (or empty for undefined location)", "default"="Undefined location"}
     *  }
     * )
     */
    public function startAction($matchId) {
        $match = $this->getMatch($matchId);

        if (is_null($match->getTeam1())) {
            $this->throwError('No team 1 defined in match '. $match->getLocalId(), self::$ERROR_BAD_REQUEST);
        }
        if (is_null($match->getTeam2())) {
            $this->throwError('No team 2 defined in match '. $match->getLocalId(), self::$ERROR_BAD_REQUEST);
        }

        if ($this->getParam('locationId', false, '') == '') {
            // Undefined location
            $res = 'started match '. $match->getLocalId() .' on undefined location';
        } else {
            // defined location
            $location = $this->getLocation($this->getParam('locationId'));
            if (!is_null($location->getMatch())) {
                // already a match playing on this location
                $this->throwError($location->getName() ." is unavailable because of match ". $location->getMatch()->getLocalId(), self::$ERROR_BAD_REQUEST);
            }
            $location->setMatch($match);
            $this->em()->persist($location);
            $match->setLocation($location);
            $res = 'started match '. $match->getLocalId() .' on '. $location->getName();
        }
        $this->newMessage('success', 'Match started', $res);
        $match->setStartTime();
        $match->setStatus($match::STATUS_PLAYING);

        // create announcement
        $announcement = new Announcement();
        $announcement->setTournament($this->tournament);
        $announcement->setType('newMatch');
        $announcement->setMatch($match);
        $match->addAnnouncement($announcement);

        $this->em()->flush();

        // trigger status Match event
        $event = new MatchEvent($match);
        $this->get('event_dispatcher')->dispatch(NotificationEvents::MATCH_STATUS, $event);

        return $this->handleResponse($res);
    }

    /**
     * Stop a match. Will bring match back to status ready as if match hasn't been played.
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.stop",
     *  requirements = {
     *		{"name"="matchId", "dataType"="Integer", "description"="Match ID"}
     *  }
     * )
     */
    public function stopAction($matchId) {
        $match = $this->getMatch($matchId);

        $match->setLocation(null);
        $match->setStatus($match::STATUS_READY);
        $this->em()->persist($match);
        $this->em()->flush();
        $res = 'stopped match '. $match->getLocalId();
        $this->newMessage('success', 'Match stopped', $res);

        // trigger status Match event
        $event = new MatchEvent($match);
        $this->get('event_dispatcher')->dispatch(NotificationEvents::MATCH_STATUS, $event);

        return $this->handleResponse($res);
    }

    /**
     * Finish a match. Will bring match to next status of finished.
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.finish",
     *  requirements = {
     *		{"name"="matchId", "dataType"="Integer", "description"="Match ID"}
     *  }
     * )
     */
    public function finishAction($matchId) {
        $match = $this->getMatch($matchId);

        $match->setLocation(null);
        $match->setStatus($match::STATUS_FINISHED);
        $res = 'finished match '. $match->getLocalId();
        $this->newMessage('success', 'Match finished', $res);

        // remove announcements
        foreach ($match->getAnnouncements() as $announcement) {
            $match->removeAnnouncement($announcement);
            $this->em()->remove($announcement);
        }

        $this->em()->flush();

        // trigger status Match event
        $event = new MatchEvent($match);
        $this->get('event_dispatcher')->dispatch(NotificationEvents::MATCH_STATUS, $event);

        return $this->handleResponse($res);
    }

    /**
     * Score a match. Will bring match to next status of played.
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.score",
     *  requirements = {
     *		{"name"="matchId", "dataType"="Integer", "description"="Match ID"}
     *  },
     *  filters = {
     *		{"name"="score", "required"="true", "type"="array", "description"="Every value contains an array, which has two keys (one for each team)"}
     *  }
     * )
     */
    public function scoreAction($matchId) {
        $match = $this->getMatch($matchId);

        $postScore = $this->getParam('score');
        if (sizeof($postScore) == 0) {
            $this->throwError("Empty score array", self::$ERROR_BAD_REQUEST);
        }

        $score = array(); // score will be saved in this array
        $setNr = 1;
        foreach ($postScore as $rowPostScore) {
            if (sizeof($rowPostScore) != 2) {
                $this->throwError("Set ". $setNr ." does not have two scores", self::$ERROR_BAD_REQUEST);
            }

            $setScore = array();
            $i = 1;
            foreach ($rowPostScore as $teamPostScore) {
                if (!is_numeric($teamPostScore)) {
                    $this->throwError('Score "'. $teamPostScore .'" for set '. $setNr .' is not a number', self::$ERROR_BAD_REQUEST);
                }
                $setScore[$i] = $teamPostScore;
                $i++;
            }
            $score[] = $setScore;
            $setNr++;
        }

        $match->setScore($score);
        $match->setStatus($match::STATUS_PLAYED);
        $this->em()->persist($match);
        $res = 'set score of match '. $match->getLocalId() .' to '. $match->getScoreTextual(true);
        $this->newMessage('success', 'Score set', $res);

        $this->em()->flush();

        // trigger score Match event
        $event = new MatchEvent($match);
        $this->get('event_dispatcher')->dispatch(NotificationEvents::MATCH_SCORE, $event);
        // trigger status Match event
        $this->get('event_dispatcher')->dispatch(NotificationEvents::MATCH_STATUS, $event);

        return $this->handleResponse($res);
    }

    /**
     * Set the status of a match.
     * Setting status to playing is not possible since team1 and team2 have to be defined; for this use Matches.start
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.setStatus",
     *  requirements = {
     *		{"name"="matchId", "dataType"="Integer", "description"="Match ID"}
     *  },
     *  filters = {
     *		{"name"="status", "required"="true", "type"="string", "description"="New status for match", "pattern"="array(postponed|ready|finished|played)"},
     *		{"name"="nonreadyReason", "required"="false", "type"="string", "description"="Optional reason for why match is not ready. Only applied when status==postponed"}
     *  }
     * )
     */
    public function setStatusAction($matchId) {
        $match = $this->getMatch($matchId);
        $statusText = $this->getParam('status');
        if ($statusText == "postponed") {
            $status = Match::STATUS_POSTPONED;
        } else if ($statusText == "ready") {
            $status = Match::STATUS_READY;
        } else if ($statusText == "finished") {
            $status = Match::STATUS_FINISHED;
        } else if ($statusText == "played") {
            $status = Match::STATUS_PLAYED;
        } else {
            $this->throwError("Status ". $statusText ." is not a recognized status", self::$ERROR_BAD_REQUEST);
        }

        // remove (possibly set) location and announcements
        $match->setLocation(null);
        foreach ($match->getAnnouncements() as $announcement) {
            $match->removeAnnouncement($announcement);
            $this->em()->remove($announcement);
        }

        // set new status
        $match->setStatus($status);
        $message = 'set match '. $match->getLocalId() .' to status '. $statusText;
        $res = "Match ". $match->getLocalId() ." is now ". $statusText;

        // apply optional non-ready reason
        if ($statusText == "postponed") {
            $nonreadyReason = $this->getParam('nonreadyReason', false, null);
            if ($nonreadyReason != null) {
                $match->setNonreadyReason($nonreadyReason);
                $message .= " with reason ". $nonreadyReason;
                $res .= " with reason ". $nonreadyReason;
            }
        } else {
            // remove non-ready reason
            $match->setNonreadyReason(null);
        }

        $this->em()->flush();

        // trigger status Match event
        $event = new MatchEvent($match);
        $this->get('event_dispatcher')->dispatch(NotificationEvents::MATCH_STATUS, $event);

        $this->newMessage('success', 'Match '. $statusText, $message);
        return $this->handleResponse($res);
    }

    /**
     * Check whether a match is already played with the same opponents
     * Does not look at the replacement players
     * Can receive an array with multiple matches(Ids), which are then all checked
     * @return mixed Contains an array with matches that were played with the same opponents, or false if there are no same matches. When received an array with matchIds, returns an array with the same matchIds as key, and as value the array/false.
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.alreadyPlayed",
     *  requirements = {
     *		{"name"="matchId", "dataType"="Integer or array", "description"="Matches that are checked"}
     *  }
     * )
     */
    public function alreadyPlayedAction($matchId) {
        $matchRequest = $matchId;
        $receivedInt = !is_array($matchRequest);
        if ($receivedInt) {
            $matchRequest = array($matchRequest);
        }
        $resArray = array();
        foreach ($matchRequest as $matchId) {
            $match = $this->getMatch($matchId);
            $resArray[$matchId] = $this->checkMatchAlreadyPlayed($match);
        }

        if ($receivedInt) {
            $res = $resArray[$matchRequest[0]];
        } else {
            $res = $resArray;
        }
        return $this->handleResponse($res);
    }

    /**
     * Check whether match is already played with the same opponents
     * @return array (with previous matches) or false
     */
    private function checkMatchAlreadyPlayed($match) {
        $res = false;

        if (!is_null($match->getTeam1()) && !is_null($match->getTeam2())) {
            $repository = $this->getDoctrine()
                ->getRepository('TSApiBundle:Match');
            $query = $repository->createQueryBuilder('m')
                ->andWhere('m.tournament = :tournament')
                ->setParameter("tournament", $this->tournament)
                ->andWhere('(m.team1 = :team1 AND m.team2 = :team2) OR (m.team2 = :team1 AND m.team1 = :team2)')
                ->setParameter("team1", $match->getTeam1())
                ->setParameter("team2", $match->getTeam2())
                ->andWhere('m != :match')
                ->setParameter("match", $match);

            $playedMatches = $query->getQuery()->getResult();
            if (count($playedMatches) > 0) {
                $res = array();
            }
            foreach ($playedMatches as $playedMatch) {
                $res[] = $this->prepareMatchData($playedMatch);
            }
        }
        return $res;
    }

    /**
     * Create a second call for a match
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Matches",
     *  description="Matches.secondCall",
     *  requirements = {
     *		{"name"="matchId", "dataType"="Integer", "description"="Match ID"}
     *  },
     *  filters = {
     *		{"name"="playerIds", "required"="true", "type"="array", "description"="Array of Player IDs"},
     *  }
     * )
     */
    public function secondCallAction($matchId) {
        $match = $this->getMatch($matchId);
        $playerIds = $this->getParam('playerIds');

        if (sizeof($playerIds) > 0) {
            $playerNames = array(); // used for result message
            foreach ($playerIds as $playerId) {
                $player = $this->getPlayer($playerId);
                $playerNames[] = $player->getName();
            }

            // create announcement
            $announcement = new Announcement();
            $announcement->setTournament($this->tournament);
            $announcement->setType('secondCall');
            $announcement->setMatch($match);
            $announcement->setPlayerIds($playerIds);
            $match->addAnnouncement($announcement);
            $this->em()->flush();

            $message = 'created a second call for match '. $match->getLocalId() .' for ';
            $message .= (sizeof($playerNames) > 1) ? "players " : "player ";
            $message .= implode(", ", $playerNames);
            $this->newMessage('success', 'Created second call', $message);
        } else {
            $message = 'No players selected';
            $this->newMessage('error', 'Second call', $message);
        }
        return $this->handleResponse($message);
    }

}