<?php

namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use TS\ApiBundle\Controller\v2\ApiV2MainController;
use TS\ApiBundle\Entity\Pool;
use TS\ApiBundle\Model\RankingModel;
use TS\ApiBundle\Entity\Match;

class RoundsController extends ApiV2MainController
{


    /**
     * Request of rounds list for a pool<br />
     * Can be with poolId or "all"
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Rounds",
     *  description="Rounds.list",
     *  requirements = {
     *		{"name"="poolId", "dataType"="Integer or String", "description"="Pool ID (or 'all')"}
     *  }
     * )
     */
    public function listAction($poolId)
    {
        $matchRepository = $this->getDoctrine()->getRepository('TSApiBundle:Match');
        $pool = ($poolId == 'all') ? null : $this->getPool($poolId);
        $rounds   = $matchRepository->getAllRounds($this->tournament, $pool);

        return $this->handleResponse($rounds);
    }

    /**
     * Create a round
     * Can create rounds for all pools with poolId == "all"
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Rounds",
     *  description="Rounds.create",
     *  filters = {
     *		{"name"="poolId", "required"="true", "type"="integer", "description"="Pool ID (or 'all')"}
     *  }
     * )
     */
    public function createAction()
    {
        $res        = "";
        $poolId = $this->getParam('poolId');

        if ($poolId == 'all') {
            // creating new rounds for all pools
            $errors = 0;
            foreach ($this->tournament->getPools() as $pool) {
                if (sizeof($pool->getTeams()) == 0) {
                    // no teams -> no need to create a new round
                    continue;
                }

                $algorithm = $this->getAlgorithm($pool);
                $response = $algorithm->newRound($pool, $this->tournament);

                if ($response === false) {
                    $errors++;
                }
            }

            if (count($errors) > 0) {
                $res = 'Error in creating new rounds for all pools, please check all pools to find possible errors';
                $this->newMessage('error', 'New round', $res);
            } else {
                $res = 'created new rounds for all pools';
                $this->newMessage('success', 'New round', $res);
            }

        } else {
            $pool = $this->getPool($poolId);
            if (sizeof($pool->getTeams()) == 0) {
                $this->throwError('Pool '. $pool->getName() .' has no teams and therefore it is not possible to create a new round', self::$ERROR_BAD_REQUEST);
            } else if (sizeof($pool->getTeams()) == 1) {
                $this->throwError('Pool '. $pool->getName() .' has only one team and therefore it is not possible to create a new round', self::$ERROR_BAD_REQUEST);
            } else {
                $algorithm = $this->getAlgorithm($pool);
                $response = $algorithm->newRound($pool, $this->tournament);

                if ($response === false) {
                    $res = 'Error in creating new round for pool ' . $pool->getName();
                    $this->newMessage('error', 'New round', $res);
                } else {
                    $res = 'created new round ' . $response . ' for pool ' . $pool->getName();
                    $this->newMessage('success', 'New round', $res);
                }
            }
        }

        return $this->handleResponse($res);
    }

    /**
     * Remove a round<br />
     * Can remove all rounds when round == "all"
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Rounds",
     *  description="Rounds.remove",
     *  requirements = {
     *     {"name"="poolId", "dataType"="Integer or String", "description"="Pool ID (or 'all')"},
     *  },
     *  filters = {
     *		{"name"="round", "required"="true", "type"="string", "description"="Round name (or 'all')"}
     *  }
     * )
     */
    public function removeAction()
    {
        $res = "";

        $repository = $this->getDoctrine()->getRepository('TSApiBundle:Match');
        $query      = $repository->createQueryBuilder('m')
            ->andWhere('m.tournament = :tournament')
            ->setParameter("tournament", $this->tournament);

        $poolParam = $this->getParam('poolId');
        if ($poolParam != "all") {
            $pool = $this->getPool($poolParam);
            $query = $query
                ->andWhere('m.pool = :pool')
                ->setParameter('pool', $pool);
        }

        $round = $this->getParam('round');

        if ($round != 'all') {
            $query = $query
                ->andWhere('m.round = :round')
                ->setParameter('round', $round);
            $res = 'deleted round ' . $round;
            if ($poolParam == "all") {
                $res .= ' for all pools';
            } else {
                $res .= ' for pool ' . $pool->getName();
            }
        } else {
            $res = 'deleted all rounds';
            if ($poolParam == "all") {
                $res .= ' for all pools';
            } else {
                $res .= ' for pool ' . $pool->getName();
            }
        }

        $matches = $query->getQuery()->getResult();

        foreach ($matches as $match) {
            $this->em()
                ->getRepository('TSApiBundle:Match')
                ->remove($match);
        }

        $this->newMessage('success', 'Deleted round', $res);
        return $this->handleResponse($res);
    }

    /**
     * Decide which algorithm to use
     * @param \TS\ApiBundle\Entity\Pool $pool
     * @return instanceof TS\ApiBundle\Algorithm\AlgorithmAbstract
     */
    private function getAlgorithm($pool) {
        $algorithmPicker = $this->get('algorithmPicker');

        if ($pool->getAlgorithm() == Pool::$ALGORITHM_ROUNDROBIN) {
            $algorithm = $algorithmPicker->pick("RoundRobin\RoundRobin", "TS\ApiBundle\Algorithm");
        } else {
            $algorithm = $algorithmPicker->pick("SwissLadder\SharpShuttle", "TS\ApiBundle\Algorithm");
        }

        return $algorithm;
    }
}