<?php

namespace TS\ApiBundle\Model;

use TS\ApiBundle\Entity\Match;


class RankingModel
{
	
	private $doctrine;
	private $tournament;
	
    
    /**
     * Constructor
     */
    public function __construct($doctrine, $tournament)
    {
        $this->doctrine = $doctrine;
        $this->tournament = $tournament;
    }
    
	
    
    /**
      * Generates the ranking data of a pool in an array
	  * @param \TS\ApiBundle\Entity\Pool $pool
      */
    public function getPoolRankingData($pool) {
    	// first get all teams in pool
    	$teams = $pool->getTeams();
    	
    	// prepare data of rankings
    	$data = array();
    	foreach ($teams as $team) {
    		$teamId = $team->getId();
    		$data[$teamId]['matchesWon'] = 0;
    		$data[$teamId]['matchesDraw'] = 0;
    		$data[$teamId]['matchesLost'] = 0;
    		$data[$teamId]['setsWon'] = 0;
    		$data[$teamId]['setsLost'] = 0;
    		$data[$teamId]['pointsWon'] = 0;
    		$data[$teamId]['pointsLost'] = 0;
    		
    		$data[$teamId]['teamId'] = $teamId;
    		$data[$teamId]['players'] = array();
    		$data[$teamId]['givenUp'] = $team->getGivenUp();
    		foreach($team->getPlayersForAllPositions() as $player) {
    			$data[$teamId]['players'][$player->getId()] = $player->getName();
    		}
    	}
    	
    	// then get all matches in this pool
    	$repository = $this->doctrine
		    ->getRepository('TSApiBundle:Match');
		$queryParameters = array(
	    		'poolId' => $pool->getId(),
	    		'tournament' => $this->tournament,
	    		'status_played' => Match::STATUS_PLAYED,
	    	);
		$query = $repository->createQueryBuilder('m')
		    ->where('m.tournament = :tournament')
		    ->andWhere('m.pool = :poolId')
		    ->andWhere('m.status = :status_played')
			->setParameters($queryParameters)
		    ->getQuery();
		$matches = $query->getResult();
		
		// go through matches and save scores in data array
		foreach ($matches as $match) {
			if (is_null($match->getTeam1()) || is_null($match->getTeam2())) {
				continue;
			}
            if (($match->getTeam1()->getPool() != $pool) || ($match->getTeam1()->getPool() != $pool)) {
                // team1 or team2 has another pool, and should therefore not in this pool ranking.
                // This can be caused by changing the match to another pool.
                continue;
            }
			$scoreArray = $match->getScore();
			$team1Sets = 0;
			$team2Sets = 0;
			$team1Points = 0;
			$team2Points = 0;
			$team1Id = $match->getTeam1()->getId();
			$team2Id = $match->getTeam2()->getId();
			
			foreach ($scoreArray as $set=>$result) {
				$team1Points += $result[1];
				$team2Points += $result[2];
				if ($result[1] > $result[2]) {
					// winner is team 1
					$team1Sets++;
				} else if ($result[1] < $result[2]) {
					$team2Sets++;
				} // result 1==2 -> do nothing
			}
			
			// now adding results of this match to data
    		$data[$team1Id]['setsWon'] += $team1Sets;
    		$data[$team1Id]['setsLost'] += $team2Sets;
    		$data[$team2Id]['setsWon'] += $team2Sets;
    		$data[$team2Id]['setsLost'] += $team1Sets;
    		$data[$team1Id]['pointsWon'] += $team1Points;
    		$data[$team1Id]['pointsLost'] += $team2Points;
    		$data[$team2Id]['pointsWon'] += $team2Points;
    		$data[$team2Id]['pointsLost'] += $team1Points;
    		
    		if ($team1Sets > $team2Sets) {
    			// team1 won match
    			$data[$team1Id]['matchesWon'] += 1;
    			$data[$team2Id]['matchesLost'] += 1;
    		} else if ($team1Sets == $team2Sets) {
    			// draw
    			$data[$team1Id]['matchesDraw'] += 1;
    			$data[$team2Id]['matchesDraw'] += 1;
    		} else {
    			// team2 won match
    			$data[$team1Id]['matchesLost'] += 1;
    			$data[$team2Id]['matchesWon'] += 1;
    		}
		}
		
		// calculate relative numbers, and store these relative numbers in arrays
		$relativeMatches = array();
		$relativeSets = array();
		$relativePoints = array();

		$teamsGivenUp = array();
		foreach ($data as $teamId=>$teamData) {
			$data[$teamId]['matchesPlayed'] = $data[$teamId]['matchesWon'] + $data[$teamId]['matchesDraw'] + $data[$teamId]['matchesLost'];
			if ($data[$teamId]['matchesPlayed'] == 0) {
				// zero matches played, which gives a problem for deviding by zero
				$data[$teamId]['matchesRelative'] = 0.0;
				$data[$teamId]['setsRelative'] = 0.5;
				$data[$teamId]['pointsRelative'] = 0.5;
			} else {
				if ($data[$teamId]['matchesWon'] + $data[$teamId]['matchesLost'] == 0) {
					$data[$teamId]['matchesRelative'] = 0.0;
				} else {
					$data[$teamId]['matchesRelative'] = (($data[$teamId]['matchesWon'] * 2 + $data[$teamId]['matchesDraw']) / ($data[$teamId]['matchesWon'] + $data[$teamId]['matchesDraw'] + $data[$teamId]['matchesLost'])) - 1;
				}
				if ($data[$teamId]['setsWon'] + $data[$teamId]['setsLost'] == 0) {
					$data[$teamId]['setsRelative'] = 0.5;
				} else {
					$data[$teamId]['setsRelative'] = $data[$teamId]['setsWon'] / ($data[$teamId]['setsWon'] + $data[$teamId]['setsLost']);
				}
				if ($data[$teamId]['pointsWon'] + $data[$teamId]['pointsLost'] == 0) {
					$data[$teamId]['pointsRelative'] = 0.5;
				} else {
					$data[$teamId]['pointsRelative'] = $data[$teamId]['pointsWon'] / ($data[$teamId]['pointsWon'] + $data[$teamId]['pointsLost']);
				}
			}
			
			$relativeMatches[$teamId] = $data[$teamId]['matchesRelative'];
			$relativeSets[$teamId] = $data[$teamId]['setsRelative'];
			$relativePoints[$teamId] = $data[$teamId]['pointsRelative'];

			if ($data[$teamId]['givenUp'])
			{
			    array_push($teamsGivenUp, $teamId);
            }

		}
		
		// calculate ranking
		arsort($relativeMatches);
		arsort($relativeSets);
		arsort($relativePoints);
		$ranks = array(); // array where teamIds are saved in order or ranking
		foreach ($relativeMatches as $matchesTeamId=>$relativeMatchesNumber) {
			if (in_array($matchesTeamId, $ranks)) {
				// rank already set
				continue;
			}
			$sameRelativeMatches = array_keys($relativeMatches, $relativeMatchesNumber);
			if (sizeof($sameRelativeMatches) > 1) {
				// there are more teams with same relative matches number
				foreach ($relativeSets as $setsTeamId=>$relativeSetsNumber) {
					if (!in_array($setsTeamId, $ranks) && in_array($setsTeamId, $sameRelativeMatches)) {
						// found next place in ranking, look for more teams with same relative sets number
						$sameRelativeSets = array_keys($relativeSets, $relativeSetsNumber);
						if (sizeof($sameRelativeSets) > 1) {
							// there are more teams with same relative matches and sets number -> look for next points
							foreach ($relativePoints as $pointsTeamId=>$relativePointsNumber) {
								if (!in_array($pointsTeamId, $ranks) && in_array($pointsTeamId, $sameRelativeSets)) {
									// found top team
									$ranks[] = $pointsTeamId;
								}
							}
						} else {
							// no other teams with same relative sets number
							$ranks[] = $setsTeamId;
						}
					}
				}
			} else {
				// no other teams with same relative matches number
				$ranks[] = $matchesTeamId;
			}
		}
		
		foreach($teamsGivenUp as $teamId)
		{
    		// put given up teams in bottom of ranking
    		$originalRank = array_search($teamId, $ranks);
    		unset($ranks[$originalRank]);
    		$ranks[] = $teamId;
		}

		// make result array in order or ranking
		$res = array();
		$rankPos = 0;
		foreach ($ranks as $teamId){
			$teamData = $data[$teamId];
            // Team which gave up doesn't have a ranking number, otherwise ranking number is $rankPos+1
            $teamData['rank'] = $data[$teamId]['givenUp'] ? '-' : $rankPos+1;
			$res[$rankPos] = $teamData;
			$rankPos++;
		}
		
		return $res;
    }
    
    
    /**
      * Generates the ranking data of all players in an array. Ranking is calculated by calculating average number of points per set played
      */
    public function getPlayersRankingData() {
    	$data = $this->startPlayerData();
		
		// calculate relative numbers, and store these relative numbers in arrays
		$playersRelative = array();
		foreach ($data as $playerId=>$playerData) {
			$relative = 0;
			if ($data[$playerId]['nrSets'] != 0) {
				$relative = $data[$playerId]['sumPoints'] / $data[$playerId]['nrSets'];
			}
			$data[$playerId]['relative'] = $relative;
			$playersRelative[$playerId] = $relative;
		}
		
		// calculate ranking
		arsort($playersRelative);
		$rank = 1;
		$res = array();
		foreach ($playersRelative as $playerId=>$playerRelative) {
			$res[$rank] = array(
				'rank' => $rank,
				'playerId' => $playerId,
				'name' => $data[$playerId]['player']->getName(),
				'sumPoints' => $data[$playerId]['sumPoints'],
				'nrSets' => $data[$playerId]['nrSets'],
				'relative' => $data[$playerId]['relative'],
				'gender' => $data[$playerId]['player']->getGender(),
			);
			if (!is_null($data[$playerId]['player']->getRegistrationGroup())) {
				$res[$rank]['groupId'] = $data[$playerId]['player']->getRegistrationGroup()->getId();
				$res[$rank]['groupName'] = $data[$playerId]['player']->getRegistrationGroup()->getName();
			}
			$rank++;
		}
		
		return $res;
    }
    
    private function startPlayerData() {
    	// first get all players with teams
    	$repository = $this->doctrine
		    ->getRepository('TSApiBundle:Player');
		$query = $repository->createQueryBuilder('p')
		    ->innerJoin('p.teams', 'teams')
		    ->andWhere('p.tournament = :tournament')
			->setParameter('tournament', $this->tournament)
		    ->getQuery();
		$players = $query->getResult();
		
		// now get played matches
    	$repository = $this->doctrine
		    ->getRepository('TSApiBundle:Match');
		$query = $repository->createQueryBuilder('m')
		    ->where('m.tournament = :tournament')
		    ->setParameter('tournament', $this->tournament)
		    ->andWhere('m.status = :status_played')
		    ->setParameter('status_played', Match::STATUS_PLAYED)
		    ->getQuery();
		$matches = $query->getResult();
		
		// prepare data array
    	$data = array();
    	foreach ($players as $player) {
    		$playerId = $player->getId();
    		$data[$playerId]['player'] = $player;
    		$data[$playerId]['sumPoints'] = 0;
    		$data[$playerId]['nrSets'] = 0;
    	}
		
		// go through matches and save scores in data array
		foreach ($matches as $match) {
			$team1 = $match->getTeam1();
			if (!is_null($team1)) {
				foreach ($team1->getPlayersForAllPositions() as $player) {
					$playerId = $player->getId();
					foreach ($match->getScore() as $setNr=>$pointsArray) {
						$data[$playerId]['sumPoints'] += $pointsArray[1];
						$data[$playerId]['nrSets']++;
					}
				}
			}
			
			$team2 = $match->getTeam2();
			if (!is_null($team2)) {
				foreach ($team2->getPlayersForAllPositions() as $player) {
					$playerId = $player->getId();
					foreach ($match->getScore() as $setNr=>$pointsArray) {
						$data[$playerId]['sumPoints'] += $pointsArray[2];
						$data[$playerId]['nrSets']++;
					}
				}
			}
		}
		
		return $data;
	}
    
    /**
      * Generates the ranking data of all groups in an array. Ranking is calculated by calculating average number of points per set played
      */
    public function getGroupsRankingData() {
    	$data = array();
    	foreach ($this->startPlayerData() as $playerId=>$playerArray) {
    		// go through all players and add  sumPoints and nrSets in data
    		$groupId = 0;
    		if (!is_null($playerArray['player']->getRegistrationGroup())) {
    			$groupId = $playerArray['player']->getRegistrationGroup()->getId();
    		}
    		if (!array_key_exists($groupId, $data)) {
    			// first time this group is checked -> add to $data
    			$groupName = (!is_null($playerArray['player']->getRegistrationGroup())) ? $playerArray['player']->getRegistrationGroup()->getName() : '';
    			$data[$groupId] = array(
    				'sumPoints' => 0,
    				'nrSets' => 0,
    				'groupId' => $groupId,
    				'groupName' => $groupName,
    				'nrPlayers' => 0,
    			);
    		}
    		$data[$groupId]['sumPoints'] += $playerArray['sumPoints'];
    		$data[$groupId]['nrSets'] += $playerArray['nrSets'];
    		$data[$groupId]['nrPlayers']++;
    	}
    	
    	// calculate relative numbers, and store these relative numbers in an array
		$groupsRelative = array();
		foreach ($data as $groupId=>$groupData) {
			$relative = 0;
			if ($data[$groupId]['nrSets'] != 0) {
				$relative = $data[$groupId]['sumPoints'] / $data[$groupId]['nrSets'];
			}
			$data[$groupId]['relative'] = $relative;
			$groupsRelative[$groupId] = $relative;
		}
		
		// calculate ranking
		arsort($groupsRelative);
		$rank = 1;
		$res = array();
		foreach ($groupsRelative as $groupId=>$groupRelative) {
			$res[$rank] = array(
				'rank' => $rank,
				'groupId' => $data[$groupId]['groupId'],
				'groupName' => $data[$groupId]['groupName'],
				'sumPoints' => $data[$groupId]['sumPoints'],
				'nrSets' => $data[$groupId]['nrSets'],
				'relative' => $data[$groupId]['relative'],
				'nrPlayers' => $data[$groupId]['nrPlayers'],
			);
			$rank++;
		}
		
		return $res;
	}
    				
    	
}