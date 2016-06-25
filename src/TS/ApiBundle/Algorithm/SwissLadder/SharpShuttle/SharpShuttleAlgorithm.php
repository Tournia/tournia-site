<?php

namespace TS\ApiBundle\Algorithm\SwissLadder\SharpShuttle;

use TS\ApiBundle\Algorithm\AlgorithmAbstract;
use TS\ApiBundle\Model\RankingModel;

class SharpShuttleAlgorithm extends AlgorithmAbstract
{
    
	protected function generateNewRound()
	{
	    $pool = $this->pool;
	    
		$teams = $this->getNonGivenUpTeams($pool);
				
        if(sizeof($teams) > 1)
		{
            $alreadyPlayedMatches = $this->getAlreadyPlayedMatchesWithoutGivenUpTeams($pool, $this->tournament);

    		// Determine round
    		$matchRepository = $this->entityManager->getRepository('TSApiBundle:Match');
    		$rounds = $matchRepository->getAllRounds($this->tournament, $pool);
    		
    		if (sizeof($rounds) == 0) {
    			$newRoundNr = 1;
    		}
    		else {
    			end($rounds);
    			$newRoundNr = key($rounds) + 1;
    		}

            //
            // The next part is used to determine if there are more rounds than
            // should be possible based on the number of teams. This is to make
            // it possible to continue playing after a poule is finished (which means having multiple cycles)
            //
            
			// Determine the number of teams, round up for uneven number of teams
			if(count($teams) % 2) {
				//uneven, so add fictional bye team
				$numTeams = count($teams) + 1;
			}
			else {
				$numTeams = count($teams);
			}

			if($numTeams <= $newRoundNr) {
				// Multiple cycles

				// We need to determine the number of cycles ($nrCycles) and calculate
				// the number of rounds in earlier cycles by that
				$nrCycles = floor(($newRoundNr - 1) / ($numTeams - 1));
				$numberOfRoundsToDelete = $nrCycles * ($numTeams - 1);
                
                // Remove matches played in earlier cycles
                foreach($alreadyPlayedMatches as $i => $match)
                {
                    if(intval(substr($match['round'], 6)) <= $numberOfRoundsToDelete)
                    {
                        unset($alreadyPlayedMatches[$i]);
                    }
                }
                
                $fictionalRound = $newRoundNr - $numberOfRoundsToDelete;
			}
			else {
				$fictionalRound = $newRoundNr;
			}

    		// Make round list based on ranking
    		$rankingModel = new RankingModel($this->entityManager, $this->tournament);
    		$rankedTeamArray = $rankingModel->getPoolRankingData($pool);

            // remove given up teams from ranking teams
            $rankedTeamArray2 = array();
            $rankIndex = 0;
            foreach ($rankedTeamArray as $index=>$team) {
                if (!$team['givenUp']) {
                    $rankedTeamArray2[$rankIndex] = $team;
                    $rankIndex++;
                }
            }
        
/*          Monolog::getInstance()->addDebug('New round : ' . ($poule['round'] + 1)); */
            
            // remove round key from $alreadyPlayedMatches
            foreach($alreadyPlayedMatches as $i => $match) {
                unset($alreadyPlayedMatches[$i]['round']);
            }

            $generator = new LadderGenerator($rankedTeamArray2, $fictionalRound, $alreadyPlayedMatches);
            $matches = $generator->generate();

            $newRound = 'Round '. $newRoundNr;
    		if(count($matches) > 0) {
    			$this->saveMatches($matches, $newRound, $pool);
                return $newRound;
    		}
    		else {
    /* 			Monolog::getInstance()->addWarning('New round started, but no new matches were generated because all different combinations of matches are already played...'); */
    			return false;
    		}
    	} else {
            // no teams
            return true;
        }
	}

    /**
      * Returns all the matches that have been played in a pool, but exludes the matches with teams that are given up
      * @returns array with team1 and team2 as keys
      */
    private function getAlreadyPlayedMatchesWithoutGivenUpTeams($pool, $tournament)
    {
        $repository = $this->entityManager->getRepository('TSApiBundle:Match');
        $query = $repository->createQueryBuilder('m')
                            ->select('IDENTITY(m.team1) as team1, IDENTITY(m.team2) as team2, m.round AS round')
                            ->andWhere('m.tournament = :tournament')
                            ->andWhere('m.pool = :pool')
                            ->setParameter('tournament', $tournament)
                            ->setParameter('pool', $pool)
                            ->leftJoin('m.team1', 't1')
                            ->leftJoin('m.team2', 't2')
                            ->andWhere('t1.givenUp = false AND t2.givenUp = false');

        return $query->getQuery()->getResult();
    }

}
