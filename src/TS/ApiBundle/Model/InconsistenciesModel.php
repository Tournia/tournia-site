<?php

namespace TS\ApiBundle\Model;


class InconsistenciesModel
{

    private $doctrine;

    /**
     * @var \TS\ApiBundle\Entity\Tournament $tournament
     */
    private $tournament;


    /**
     * Constructor
     */
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Generates the data for all inconsistencies
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return array()
     */
    public function getAllInconsistencies($tournament) {
        $this->tournament = $tournament;

        $res = array();
        $res = array_merge($res, $this->getPlayersRegisteredNotInPool($tournament, null));
        return $res;
    }

    /**
     * Returns players which are registered for a discipline, but are not in the pool as inputDiscipline
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @param \TS\ApiBundle\Entity\Pool $pool Filter on a pool (can be null)
     * @return array()
     */
    public function getPlayersRegisteredNotInPool($tournament, $pool) {
        $repository = $this->doctrine
            ->getRepository('TSApiBundle:Pool');
        $query = $repository->createQueryBuilder('p')
            ->andWhere('p.tournament = :tournament')
            ->setParameter('tournament', $tournament);
        if (!is_null($pool)) {
            $query = $query
                ->andWhere('p = :pool')
                ->setParameter('pool', $pool);
        }
        $poolsResult = $query->getQuery()->getResult();

        $res = array();

        foreach ($poolsResult as $pool) {
            /* @var \TS\ApiBundle\Entity\Pool $pool */
            foreach ($pool->getInputDisciplines() as $discipline) {
                /* @var \TS\ApiBundle\Entity\Discipline $discipline */
                // go through players in the discipline
                foreach ($discipline->getPlayers() as $disciplinePlayer) {
                    /* @var \TS\ApiBundle\Entity\DisciplinePlayer $disciplinePlayer */
                    $player = $disciplinePlayer->getPlayer();
                    // We now have a list of players that can have an inconsistency
                    // now check if player is in a team in this pool. If so, it's ok. If not, it's an inconsistency
                    $playerIsInPool = false;
                    foreach ($pool->getTeams() as $team) {
                        /* @var \TS\ApiBundle\Entity\Team $team */
                        $playerIsInPool = $team->getPlayers()->contains($player) || $playerIsInPool;
                    }

                    if (!$playerIsInPool) {
                        $resArray = array(
                            'playerId' => $player->getId(),
                            'name' => $player->getName(),
                            'status' => $player->getStatus(),
                            'inconsistency' => "playerRegisteredNotInPool",
                        );
                        $res[] = $resArray;
                    }
                }
            }
        }

        return $res;
    }
}