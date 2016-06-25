<?php

namespace TS\ApiBundle\Algorithm\RoundRobin\RoundRobin;

use TS\ApiBundle\Algorithm\AlgorithmAbstract;
use TS\ApiBundle\Model\RankingModel;
use TS\ApiBundle\Entity\Match;

class RoundRobinAlgorithm extends AlgorithmAbstract
{

    protected function generateNewRound()
    {
        $pool = $this->pool;

        $teams = $this->getNonGivenUpTeams($pool);

        if(sizeof($teams) > 1)
        {
            if (count($teams)%2 != 0){
                array_push($teams, "bye");
            }
            $away = array_splice($teams,(count($teams)/2));
            $home = $teams;
            $resRounds = array();

            for ($i=0; $i < count($home)+count($away)-1; $i++){
                $matches = array();

                // Determine matches
                for ($j=0; $j<count($home); $j++){
                    $homeId = ($home[$j] == "bye") ? null : $home[$j];
                    $awayId = ($away[$j] == "bye") ? null : $away[$j];
                    $matches[] = array(
                        "team1" => array("teamId" => $homeId),
                        "team2" => array("teamId" => $awayId)
                    );
                }
                if(count($home)+count($away)-1 > 2){
                    $splice = array_splice($home,1,1);
                    $shift = array_shift($splice);
                    array_unshift($away, $shift);
                    array_push($home, array_pop($away));
                }

                // Save matches for this round
                $newRound = $this->getNewRoundName($pool);
                if(count($matches) > 0) {
                    $this->saveMatches($matches, $newRound, $pool);
                    $resRounds[] = $newRound;
                }
            }
            return implode(", ", $resRounds);
        } else {
            // no teams
            return true;
        }
    }

    /**
     * Determine new round name
     * @param $pool
     * @return string
     */
    private function getNewRoundName($pool) {
        // Determine round
        $matchRepository = $this->entityManager->getRepository('TSApiBundle:Match');
        $rounds = $matchRepository->getAllRounds($this->tournament, $pool);

        if (sizeof($rounds) == 0) {
            $newRoundNr = 1;
        } else {
            end($rounds);
            $newRoundNr = key($rounds) + 1;
        }

        $newRound = 'Round '. $newRoundNr;
        return $newRound;
    }
}
