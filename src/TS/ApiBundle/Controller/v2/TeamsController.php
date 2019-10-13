<?php
namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use TS\ApiBundle\Controller\v2\ApiV2MainController;
use TS\ApiBundle\Entity\Team;

use TS\ApiBundle\Model\TeamModel;

class TeamsController extends ApiV2MainController
{


    /**
     * Get list of teams<br />
     * Can be with poolId or "all"
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Teams",
     *  description="Teams.list",
     *  requirements = {
     *		{"name"="poolId", "dataType"="Integer", "description"="Pool ID (or 'all')"},
     *  }
     * )
     */
    public function listAction($poolId) {
        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Team');

        $query = $repository->createQueryBuilder('t')
            ->andWhere('t.tournament = :tournament')
            ->setParameter('tournament', $this->tournament)
            ->andWhere('t.givenUp = false');
        /* 		    ->groupBy('t.round'); */
        if ($poolId != 'all') {
            $query = $query
                ->andWhere('t.pool = :poolId')
                ->setParameter('poolId', $poolId);
        }

        $teams = $query->getQuery()->getResult();

        $resArray = array();
        foreach ($teams as $team) {
            $resArray[$team->getId()] = array(
                'id' => $team->getId(),
                'name' => $team->getName(),
            );
        }

        return $this->handleResponse($resArray);
    }

    /**
     * Get information about specific team
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Teams",
     *  description="Teams.get",
     *  requirements = {
     *		{"name"="teamId", "dataType"="Integer", "description"="Team ID"},
     *  }
     * )
     */
    public function getAction($teamId) {
        $team = $this->getTeam($teamId);
        $res = TeamModel::formatTeam($team);
        return $this->handleResponse($res);
    }


    /**
     * Add a player to a team. Existing players will be overwritten (i.e. removed from team)
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Teams",
     *  description="Teams.addPlayer",
     *  filters = {
     *		{"name"="teamId", "required"="false", "type"="integer", "description"="Team ID (or 0 if a new team should be created)"},
     *		{"name"="poolId", "required"="true", "type"="integer", "description"="Pool ID"},
     *		{"name"="playerId", "required"="true", "type"="integer", "description"="Player ID"},
     *		{"name"="position", "required"="true", "type"="integer", "description"="Position"}
     *  }
     * )
     */
    public function addPlayerAction() {
        $pool = $this->getPool($this->getParam('poolId'));
        $player = $this->getPlayer($this->getParam('playerId'));
        $teamId = $this->getParam('teamId', false, 0);
        $team = ($teamId != 0) ? $this->getTeam($teamId) : null;
        $position = intval($this->getParam('position'));

        if ($position >= $pool->getNrPlayersInTeam()) {
            $this->throwError("Position ". $position ." is bigger than maximum number of players in team for this pool", self::$ERROR_BAD_REQUEST);
        }
        /*if (!is_null($team) && in_array($player, $team->getPlayersForAllPositions(true))) {
            $this->throwError("Player ". $player->getName() ." is already in this team", self::$ERROR_BAD_REQUEST);
        }*/

        $teamModel = new TeamModel($this->getDoctrine(), $this->tournament);
        $team = $teamModel->addPlayerToTeam($pool, $team, $position, $player);

        $res = array(
			'message' => 'added player '. $player->getName() .' to team '. $team->getId(),
			'teamId' => $team->getId(),
		);
		$this->newMessage('success', 'Added player', $res);
        return $this->handleResponse($res);
    }

    /**
     * Remove a player from a team TODO: change playerId to position
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Teams",
     *  description="Teams.removePlayer",
     *  requirements = {
     *		{"name"="teamId", "dataType"="Integer", "description"="Team ID"},
     *		{"name"="playerId", "dataType"="Integer", "description"="Player ID (only original playerId, no replacement playerId)"}
     *  }
     * )
     */
    public function removePlayerAction($teamId, $playerId) {
        $player = $this->getPlayer($playerId);
        $team = $this->getTeam($teamId);
        $res = $this->removePlayerFromTeam($player, $team);


        $this->newMessage('success', 'Removed player', $res);
        return $this->handleResponse($res);
    }

    /**
     * Implement removing player from team
     * @param $player
     * @param $team
     * @return string with result
     */
    private function removePlayerFromTeam($player, $team) {
        $res = "";
        $teamName = $team->getName();

        // remove player from team
        $removed = false;
        foreach ($team->getPlayersForAllPositions(true) as $position=>$playerTmp) {
            if ($playerTmp == $player) {
                // remove player
                $team->setPlayerForPosition($position, null);
                $removed = true;
            }
        }

        if (!$removed) {
            //$this->throwError("Player ". $player->getName() ." not found in team ". $teamName, self::$ERROR_BAD_REQUEST);
            // TODO: throw error, and change teams template
            $res = "Player ". $player->getName() ." not found in team ". $teamName;
        } else {
            $res = 'removed player '. $player->getName() .' from team '. $teamName;
        }

        return $res;
    }

    /**
     * Remove all players from a team
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Teams",
     *  description="Teams.removeAllPlayers",
     *  requirements = {
     *		{"name"="teamId", "dataType"="integer", "description"="Team ID"}
     *  }
     * )
     */
    public function removeAllPlayersAction($teamId) {
        $team = $this->getTeam($teamId);
        foreach ($team->getPlayersForAllPositions(true) as $position=>$player) {
            if ($player != null) {
                $this->removePlayerFromTeam($player, $team);
            }
        }

        $res = "removed all players from team";
        $this->newMessage('success', 'Removed player', $res);
        return $this->handleResponse($res);
    }


    /**
     * Remove a team
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Teams",
     *  description="Teams.remove",
     *  requirements = {
     *     {"name"="teamId", "dataType"="Integer", "description"="Team ID"},
     *  },
     *  filters = {
     *		{"name"="onlyIfEmpty", "required"="false", "type"="boolean", "description"="Remove the team only if it has no players or matches", "default"="true"}
     *  }
     * )
     */
    public function removeAction($teamId) {
        $team = $this->getTeam($teamId);
        $pool = $team->getPool();
        $onlyIfEmpty = $this->getParam('onlyIfEmpty', false, 'true') == 'true';

        if ($onlyIfEmpty) {
            $res = "Team not empty";

            $team = $this->getTeam($this->getParam('teamId'));
            if ((sizeof($team->getPlayers()) == 0) && (sizeof($team->getMatches()) == 0)) {
                // empty team -> remove team
                $res = 'removed empty team from pool '. $pool->getName();
                $this->newMessage('success', 'Deleted team', $res);
                $pool->removeTeam($team);
                $this->em()->remove($team);
            }
        } else {
            $res = 'removed team '. $team->getName() .' from pool '. $pool->getName();
            $this->newMessage('success', 'Deleted team', $res);
            // delete matches
            foreach ($team->getMatches() as $match) {
                $this->em()
                    ->getRepository('TSApiBundle:Match')
                    ->remove($match);
            }

            $pool->removeTeam($team);
            $this->em()->remove($team);
        }

        return $this->handleResponse($res);
    }

    /**
     * Give up a team
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Teams",
     *  description="Teams.giveUp",
     *  requirements = {
     *     {"name"="teamId", "dataType"="Integer", "description"="Team ID"},
     *  },
     *  filters = {
     *		{"name"="givenUp", "required"="true", "type"="boolean", "description"="Whether to give up team", "pattern"="(true|false|toggle)"},
     *		{"name"="nonreadyReason", "required"="false", "type"="string", "description"="Optional reason for why team is not ready. Only applied when givenUp==true"}
     *  }
     * )
     */
    public function giveUpAction($teamId) {
        $team = $this->getTeam($teamId);

        if ($this->getParam('givenUp') == "toggle") {
            $setGivenUp = $team->getGivenUp() == false;
        } else {
            $setGivenUp = $this->getParam('givenUp') == "true";
        }

        $team->setGivenUp($setGivenUp);
        if ($setGivenUp) {
            $res = 'gave up team '. $team->getName();

            // apply optional non-ready reason
            $nonreadyReason = $this->getParam('nonreadyReason', false, null);
            if ($nonreadyReason != null) {
                $team->setNonreadyReason($nonreadyReason);
                $res .= " with reason ". $nonreadyReason;
            }

            $this->newMessage('success', 'Given up team', $res);
        } else {
            // remove non-ready reason
            $team->setNonreadyReason(null);

            $res = 'revert given up of team '. $team->getName();
            $this->newMessage('success', 'Ungiven up team', $res);
        }
        $this->em()->persist($team);

        return $this->handleResponse($res);
    }

    /**
     * Set a replacement player
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Teams",
     *  description="Teams.setReplacementPlayer",
     *  requirements = {
     *     {"name"="teamId", "dataType"="Integer", "description"="Team ID"},
     *  },
     *  filters = {
     *		{"name"="playerId", "required"="true", "type"="integer", "description"="Player ID"},
     *		{"name"="position", "required"="true", "type"="integer", "description"="Position"},
     *		{"name"="replacementPlayerId", "required"="true", "type"="integer", "description"="Replacement Player ID (or 0 for removing it)"}
     *  }
     * )
     */
    public function setReplacementPlayerAction($teamId) {
        $player = $this->getPlayer($this->getParam('playerId'));
        $team = $this->getTeam($teamId);
        $position = $this->getParam('position');

        if (($this->getParam('replacementPlayerId') == 0) || ($this->getParam('replacementPlayerId') == $this->getParam('playerId'))) {
            // remove replacement player
            $team->setReplacementPlayerForPosition($position, null);
            $res = 'removed replacement player for '. $player->getName();
        } else {
            // set replacement player
            $replacementPlayer = $this->getPlayer($this->getParam('replacementPlayerId'));
            $team->setReplacementPlayerForPosition($position, $replacementPlayer);
            $res = 'set replacement player '. $replacementPlayer->getName() .' for '. $player->getName();
        }
        $this->newMessage('success', 'Replacement player', $res);
        $this->em()->persist($team);

        return $this->handleResponse($res);
    }

}