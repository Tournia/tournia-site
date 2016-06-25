<?php
namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use TS\ApiBundle\Controller\v2\ApiV2MainController;
use TS\ApiBundle\Entity\Match;
use TS\ApiBundle\Entity\Pool;
use TS\ApiBundle\Entity\Team;
use TS\ApiBundle\Model\TeamModel;

class PoolsController extends ApiV2MainController
{



    /**
     * Get list of pools
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Pools",
     *  description="Pools.list",
     * )
     */
    public function listAction() {
        $res = $this->getPoolsData();
        return $this->handleResponse($res);
    }

    /**
     * Get a pool
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Pools",
     *  description="Pools.get",
     *  requirements = {
     *      {"name"="poolId", "dataType"="Integer", "description"="Pool ID"},
     *  }
     * )
     */
    public function getAction($poolId) {
        $pool = $this->getPool($poolId);
        $res = $this->formatPool($pool);
        return $this->handleResponse($res);
    }

    /**
     * Create a new pool
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Pools",
     *  description="Pools.create",
     *  filters = {
     *		{"name"="name", "required"="true", "type"="string", "description"="Name of pool"},
     *		{"name"="nrPlayersInTeam", "required"="false", "type"="string", "description"="Number of players in team"},
     *		{"name"="algorithm", "required"="false", "type"="string", "description"="Which algorithm to use, can be swissladder or roundrobin"},
     *      {"name"="inputDisciplines", "required"="false", "type"="int or array", "description"="disciplineIds as input for this pool"}
     *  }
     * )
     */
    public function createAction() {
        $name = $this->getParam('name');
        $pool = new Pool();
        $pool->setName($name);
        $pool->setTournament($this->tournament);

        $nrPlayersInTeam = $this->getParam('nrPlayersInTeam', false, null);
        if (!is_null($nrPlayersInTeam)) {
            $this->setNrPlayersInTeam($pool, $nrPlayersInTeam);
        }
        $algorithm = $this->getParam('algorithm', false, null);
        if (!is_null($algorithm)) {
            $this->setAlgorithm($pool, $algorithm);
        }

        $inputDisciplines = $this->getParam('inputDisciplines', false, null);
        if (!is_null($inputDisciplines)) {
            if (!is_array($inputDisciplines)) {
                // input disciplines can be int or array
                $inputDisciplines = array($inputDisciplines);
            }

            foreach ($inputDisciplines as $inputDisciplineId) {
                $inputDiscipline = $this->getDiscipline($inputDisciplineId);
                $pool->addInputDiscipline($inputDiscipline);
            }
        }

        $this->em()->persist($pool);
        $res = 'created a new pool '. $name;
        $this->newMessage('success', 'Added pool', $res);
        return $this->handleResponse($res);
    }


    /**
     * Edit pool
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Pools",
     *  description="Pools.edit",
     *  requirements = {
     *      {"name"="poolId", "dataType"="Integer", "description"="Pool ID"},
     *  },
     *  filters = {
     *		{"name"="name", "required"="false", "type"="string", "description"="Name of pool"},
     *		{"name"="nrPlayersInTeam", "required"="false", "type"="int", "description"="Number of players in team"},
     *      {"name"="algorithm", "required"="false", "type"="string", "description"="Which algorithm to use, can be swissladder or roundrobin"},
     *		{"name"="position", "required"="false", "type"="integer", "description"="Position of pool"},
     *		{"name"="inputDisciplines", "required"="false", "type"="int or array", "description"="disciplineIds as input for this pool"}
     *  }
     * )
     */
    public function editAction($poolId) {
        $pool = $this->getPool($poolId);
        $name = $this->getParam('name', false, null);
        $nrPlayersInTeam = $this->getParam('nrPlayersInTeam', false, null);
        $position = $this->getParam('position', false, null);
        $inputDisciplines = $this->getParam('inputDisciplines', false, null);
        $res = '';

        if (!is_null($name)) {
            $pool->setName($name);
            $res = 'changed pool name to '. $pool->getName();
            $this->newMessage('success', 'Pool changed', $res);
        }
        if (!is_null($nrPlayersInTeam)) {
            $this->setNrPlayersInTeam($pool, $nrPlayersInTeam);
            $res = 'changed pool number of players in team to '. $pool->getNrPlayersInTeam();
            $this->newMessage('success', 'Pool changed', $res);
        }
        $algorithm = $this->getParam('algorithm', false, null);
        if (!is_null($algorithm)) {
            $this->setAlgorithm($pool, $algorithm);
        }
        if (!is_null($position)) {
            $pool->setPosition($position);
            if ($res != '') {
                $res .= ". ";
            }
            $res .= 'changed position of pool '. $pool->getName();
            $this->newMessage('success', 'Pool changed', $res);
        }
        if (!is_null($inputDisciplines)) {
            if (!is_array($inputDisciplines)) {
                // input disciplines can be int or array
                $inputDisciplines = array($inputDisciplines);
            }

            // remove all existing input disciplines for pool and set new
            foreach ($pool->getInputDisciplines() as $oldDiscipline) {
                $pool->removeInputDiscipline($oldDiscipline);
            }

            foreach($inputDisciplines as $inputDisciplineId) {
                $inputDiscipline = $this->getDiscipline($inputDisciplineId);
                $pool->addInputDiscipline($inputDiscipline);
            }

            if ($res != '') {
                $res .= ". ";
            }
            $res .= 'changed input disciplines of pool '. $pool->getName();
            $this->newMessage('success', 'Pool changed', $res);
        }
        $this->em()->persist($pool);

        return $this->handleResponse($res);
    }

    /**
     * Set the nrPlayersInTeam and check for errors in input
     * @param \TS\ApiBundle\Entity\Pool $pool
     * @param int $nrPlayersInTeam
     */
    private function setNrPlayersInTeam(&$pool, $nrPlayersInTeam) {
        if (!is_numeric($nrPlayersInTeam)) {
            $this->throwError("Number of players in team is not a number: ". $nrPlayersInTeam, self::$ERROR_BAD_REQUEST);
        }
        $nrPlayersInTeam = intval($nrPlayersInTeam);
        if ($nrPlayersInTeam < 1) {
            $this->throwError("The minimum number of players in a team is 1", self::$ERROR_BAD_REQUEST);
        }
        $pool->setNrPlayersInTeam($nrPlayersInTeam);
    }

    /**
     * Set the algorithm and check for errors in input
     * @param \TS\ApiBundle\Entity\Pool $pool
     * @param string $algorithm
     */
    private function setAlgorithm(&$pool, $algorithm) {
        if (($algorithm != Pool::$ALGORITHM_ROUNDROBIN) && ($algorithm != Pool::$ALGORITHM_SWISSLADDER)) {
            $this->throwError("Algorithm is not correct, can be swissladder or roundrobin, but is : ". $algorithm, self::$ERROR_BAD_REQUEST);
        }
        $pool->setAlgorithm($algorithm);
    }

    /**
     * Remove pool
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Pools",
     *  description="Pools.remove",
     *  requirements = {
     *      {"name"="poolId", "dataType"="Integer", "description"="Pool ID"},
     *  }
     * )
     */
    public function removeAction($poolId) {
        $pool = $this->getPool($poolId);

        if (!is_null($pool->getTeams())) {
            $this->throwError('Pool '. $pool->getName() .' currently has teams, and can therefore not be removed.', self::$ERROR_BAD_REQUEST);
        }
        if (!is_null($pool->getMatches())) {
            $this->throwError('Pool '. $pool->getName() .' currently has matches, and can therefore not be removed.', self::$ERROR_BAD_REQUEST);
        }

        $res = 'removed pool '. $pool->getName();
        $this->newMessage('success', 'Pool removed', $res);
        $this->em()->remove($pool);

        return $this->handleResponse($res);
    }


    /**
     * Get data of all pools
     * @return array
     */
    private function getPoolsData() {
        $res = array();

        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Pool');
        $pools = $repository->getBySortableGroups(array('tournament'=>$this->tournament));

        foreach ($pools as $pool) {
            $res[] = $this->formatPool($pool);
        }

        return $res;
    }

    /**
     * Return formatted pool in array
     * @param \TS\ApiBundle\Entity\Pool $pool
     * @return array
     */
    private function formatPool($pool) {
        $inputDisciplinesArray = array();
        foreach($pool->getInputDisciplines() as $discipline) {
            $inputDisciplinesArray[$discipline->getId()] = $discipline->getName();
        }

        $res = array(
            'poolId' => $pool->getId(),
            'position' => $pool->getPosition(),
            'name' => $pool->getName(),
            'algorithm' => $pool->getAlgorithm(),
            'inputDisciplines' => $inputDisciplinesArray,
            'totTeams' => sizeof($pool->getTeams()),
            'nrPlayersInTeam' => $pool->getNrPlayersInTeam(),
        );

        $teamsArray = array();
        foreach ($pool->getTeams() as $team) {
            $teamsArray[$team->getId()] = TeamModel::formatTeam($team);
        }
        $res['teams'] = $teamsArray;

        return $res;
    }

    /**
     * Check whether the pool has unfinished matches
     * Can be with poolId or "all"
     * Returns "ok" or "warning"
     * @TODO improve return values
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Pools",
     *  description="Pools.checkFinishedPlaying"
     * )
     */
    public function checkFinishedPlayingAction($poolId) {
        $repository = $this->getDoctrine()
            ->getRepository('TSApiBundle:Match');
        $query = $repository->createQueryBuilder('m')
            ->select('COUNT(m)')
            ->andWhere('m.tournament = :tournament')
            ->setParameter("tournament", $this->tournament)
            ->andWhere('m.status != :status_played')
            ->setParameter("status_played", Match::STATUS_PLAYED);

        if ($poolId != 'all') {
            $pool = $this->getPool($poolId);
            $query = $query
                ->andWhere('m.pool = :poolId')
                ->setParameter('poolId', $pool->getId());
        }
        $query = $query->getQuery();
        $nrMatches = $query->getSingleScalarResult();
        $res = ($nrMatches == 0) ? "ok" : "warning";
        return $this->handleResponse($res);
    }

    /**
     * Import registration players in pools, based on registration status.
     * Players that are already in a pool will not be added again.
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Pools",
     *  description="Pools.importRegistrations",
     *	filters = {
     *		{"name"="status", "required"="true", "type"="array or string", "description"="Registration status of imported players"}
     *	}
     * )
     */
    public function importRegistrationsAction() {
        $status = $this->getParam('status');
        if (!is_array($status)) {
            $status = array($status);
        }
        $nrPlayersAdded = 0;
        $teamModel = new TeamModel($this->getDoctrine(), $this->tournament);

        foreach ($this->tournament->getPlayers() as $player) {
            if (in_array($player->getStatus(), $status)) {
                // check if player has to be added to a pool
                foreach ($player->getDisciplinePlayers() as $disciplinePlayer) { /* @var \TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayer */
                    $registrationDiscipline = $disciplinePlayer->getDiscipline();

                    // Find all teams for all inputDisciplines of pools
                    $playerInPool = false;
                    foreach ($registrationDiscipline->getPools() as $pool) {
                        /* @var \TS\ApiBundle\Entity\Pool $pool */
                        foreach ($pool->getTeams() as $team) {
                            if ($team->getPlayers()->contains($player)) {
                                // player is in pool
                                $playerInPool = true;
                                continue 2;
                            }
                        }
                    }

                    if (!$playerInPool && (count($registrationDiscipline->getPools()) > 0)) {
                        // player is not in pool -> adding player to pool
                        // TODO: make smarter decision which pool to put player in, not the first pool
                        $pool = $registrationDiscipline->getPools()->get(0);
                        $teamModel->addPlayerToTeam($pool, null, 0, $player);
                        $nrPlayersAdded++;
                    }
                }
            }
        }
        $res = 'added total of '. $nrPlayersAdded .' players to pools';
        $this->newMessage('success', 'Players added', $res);
        return $this->handleResponse($res);
    }

    /**
     * Automatically assigns players that are not in a team to each other.
     * This is only for pools with two or more players in a team and for teams without matches.
     * It detects players that have no partner yet, and automatically assigns players to each other.
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Pools",
     *  description="Pools.autoAssign"
     * )
     */
    public function autoAssignAction() {
        $nrPlayersAdded = 0;

        $query = $this->getDoctrine()
            ->getRepository('TSApiBundle:Team')
            ->createQueryBuilder('team')
            ->andWhere('SIZE(team.matches1) = 0')
            ->andWhere('SIZE(team.matches2) = 0')
            ->andWhere('SIZE(team.players) <= 1')
            ->leftJoin('team.pool', 'pool')
            ->andWhere('pool.nrPlayersInTeam > 1')
            ->andWhere('pool.tournament = :tournament')
            ->setParameter('tournament', $this->tournament)
            ->orderBy('team.pool', 'ASC')
            ->getQuery();
        $currentPool = null;
        $players = array();
        foreach ($query->getResult() as $team) {
            /* @var \TS\ApiBundle\Entity\Team $team */
            // find all players for the same pool and let $this->autoAssignForPool() handle the assigning for these players
            if ($currentPool != $team->getPool()) {
                // starting new pool
                if ($currentPool != null) {
                    $this->autoAssignForPool($currentPool, $players);
                    $nrPlayersAdded += sizeof($players);
                    $players = array();
                }
                $currentPool = $team->getPool();
            }

            foreach ($team->getPlayers() as $player) {
                array_push($players, $player);
            }

            // remove team because it will be re-created in autoAssignForPool()
            $this->em()->remove($team);
        }
        if (!is_null($currentPool)) {
            // assignForPool for last found players
            $this->autoAssignForPool($currentPool, $players);
            $nrPlayersAdded += sizeof($players);
        }

        $res = 'automatically added total of '. $nrPlayersAdded .' players to teams';
        $this->newMessage('success', 'Auto assign players', $res);
        return $this->handleResponse($res);
    }

    /**
     * Automatically assigns players to a new team in a smart way
     * @param \TS\ApiBundle\Entity\Pool $pool
     * @param array $players
     */
    private function autoAssignForPool($pool, $players) {
        $i = 0;
        $team = null;
        $teamModel = new TeamModel($this->getDoctrine(), $this->tournament);
        $assignedPlayers = array();
        foreach ($players as $player) {
            /* @var \TS\ApiBundle\Entity\Player $player */
            /*if ($pool->getNrPlayersInTeam() % $i == 0) {
                // create new team
                $team = null;
            }*/

            if (in_array($player, $assignedPlayers)) {
                continue;
            }
            // try to find a match for $player
            // 1. Different gender and different RegistrationGroup
            foreach ($players as $player2) {
                if (in_array($player2, $assignedPlayers) || ($player == $player2)) {
                    continue;
                }
                if (
                    ($player->getRegistrationGroup() != $player2->getRegistrationGroup()) &&
                    ($player->getGender() != $player2->getGender())
                ) {
                    // match found
                    $posPlayer1 = 0;
                    $posPlayer2 = 1;
                    if ($player->getGender() == "F") {
                        $posPlayer1 = 1;
                        $posPlayer2 = 0;
                    }
                    $team = $teamModel->addPlayerToTeam($pool, null, $posPlayer1, $player);
                    $assignedPlayers[] = $player;
                    $teamModel->addPlayerToTeam($pool, $team, $posPlayer2, $player2);
                    $assignedPlayers[] = $player2;
                    continue 2;
                }
            }
            // 2. Different gender
            foreach ($players as $player2) {
                if (in_array($player2, $assignedPlayers) || ($player == $player2)) {
                    continue;
                }
                if (
                    ($player->getGender() != $player2->getGender())
                ) {
                    // match found
                    $posPlayer1 = 0;
                    $posPlayer2 = 1;
                    if ($player->getGender() == "F") {
                        $posPlayer1 = 1;
                        $posPlayer2 = 0;
                    }
                    $team = $teamModel->addPlayerToTeam($pool, null, $posPlayer1, $player);
                    $assignedPlayers[] = $player;
                    $teamModel->addPlayerToTeam($pool, $team, $posPlayer2, $player2);
                    $assignedPlayers[] = $player2;
                    continue 2;
                }
            }
            // 3. Different RegistrationGroup
            foreach ($players as $player2) {
                if (in_array($player2, $assignedPlayers) || ($player == $player2)) {
                    continue;
                }
                if (
                    ($player->getRegistrationGroup() != $player2->getRegistrationGroup())
                ) {
                    // match found
                    $posPlayer1 = 0;
                    $posPlayer2 = 1;
                    if ($player->getGender() == "F") {
                        $posPlayer1 = 1;
                        $posPlayer2 = 0;
                    }
                    $team = $teamModel->addPlayerToTeam($pool, null, $posPlayer1, $player);
                    $assignedPlayers[] = $player;
                    $teamModel->addPlayerToTeam($pool, $team, $posPlayer2, $player2);
                    $assignedPlayers[] = $player2;
                    continue 2;
                }
            }
            // 4. No different gender nor RegistrationGroup -> assign to own team
            foreach ($players as $player2) {
                if (in_array($player2, $assignedPlayers) || ($player == $player2)) {
                    continue;
                }

                $posPlayer1 = 0;
                $posPlayer2 = 1;
                if ($player->getGender() == "F") {
                    $posPlayer1 = 1;
                    $posPlayer2 = 0;
                }
                $team = $teamModel->addPlayerToTeam($pool, null, $posPlayer1, $player);
                $assignedPlayers[] = $player;
                $teamModel->addPlayerToTeam($pool, $team, $posPlayer2, $player2);
                $assignedPlayers[] = $player2;
                continue 1;
            }


            $i++;
        }
    }
}