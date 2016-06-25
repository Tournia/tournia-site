<?php

namespace TS\ApiBundle\Model;

use TS\ApiBundle\Entity\Match;
use Symfony\Component\HttpFoundation\Request;


class MatchListModel
{
	
	private $doctrine;
	private $tournament;
	private $currentlyPlayingPlayerIds;
	
    
    /**
     * Constructor
     */
    public function __construct($doctrine, $tournament)
    {
        $this->doctrine = $doctrine;
        $this->tournament = $tournament;
        $this->currentlyPlayingPlayerIds = null;
    }
    
    /**
      * Generates the data for all matches
      */
	public function getAllMatchesData(Request $request) {
		$res = array();
		$res['aaData'] = array();
		$res['sEcho'] = intval($request->query->get('sEcho', 0));
		
		// getting sorting information
		$sortColumn = "m.localId";
		$getSortColumn = $request->query->get('iSortCol_0', '');
		if ($getSortColumn != '') {
			if ($getSortColumn == 0) {
				$sortColumn = "m.localId";
			} else if ($getSortColumn == 1) {
				$sortColumn = array("playersTeam1.firstName", "playersTeam1.lastName");
			} else if ($getSortColumn == 2) {
				$sortColumn = array("playersTeam2.firstName", "playersTeam2.lastName");
			} else if ($getSortColumn == 3) {
				$sortColumn = "pool.name";
			} else if ($getSortColumn == 4) {
				$sortColumn = "m.round";
			} else if ($getSortColumn == 6) {
				$sortColumn = "m.status"; // todo: hasScore()
			}
		}
		$getSortDirection = $request->query->get('sSortDir_0', '');
		if (($getSortDirection == "") || ($getSortDirection == "asc")) {
			$sortDirection = "ASC";
		} else {
			$sortDirection = "DESC";
		}
		
		
		
		$repository = $this->doctrine
		    ->getRepository('TSApiBundle:Match');
		$query = $repository->createQueryBuilder('m')
		    ->andWhere('m.tournament = :tournament')
		    ->setParameter('tournament', $this->tournament)
		    ->leftJoin('m.pool', 'pool')
		    ->leftJoin('m.team1', 'team1')
		    ->leftJoin('team1.players', 'playersTeam1')
		    ->leftJoin('m.team2', 'team2')
		    ->leftJoin('team2.players', 'playersTeam2')
		    ->groupBy('m.id');
		if (is_array($sortColumn)) {
			$query = $query->orderBy($sortColumn[0], $sortDirection);
			for ($i = 1; $i < count($sortColumn); $i++) {
		   		$query = $query->addOrderBy($sortColumn[$i], $sortDirection);
		    }
		} else {
			$query = $query->orderBy($sortColumn, $sortDirection);
		}
		
		// finding total number of records
		$res['iTotalRecords'] = count($query->getQuery()->getResult());
		
		// search
		$searchQuery = $request->query->get('sSearch', '');
		if ($searchQuery != '') {
			$query = $query
				->andWhere("m.localId LIKE :searchQuery OR playersTeam1.firstName LIKE :searchQuery OR playersTeam1.lastName LIKE :searchQuery OR playersTeam2.firstName LIKE :searchQuery OR playersTeam2.lastName LIKE :searchQuery OR pool.name LIKE :searchQuery OR m.round LIKE :searchQuery")
				->setParameter('searchQuery', '%'. $searchQuery .'%');
			// finding current number of records (after being filtered by search
			$res['iTotalDisplayRecords'] = count($query->getQuery()->getResult());
		} else {
			// current number of records is identical to total number of records
			$res['iTotalDisplayRecords'] = $res['iTotalRecords'];
		}

		$matches = $query
			->setMaxResults($request->query->get('iDisplayLength'))
			->setFirstResult($request->query->get('iDisplayStart'))
			->getQuery()
			->getResult();

        foreach ($matches as $match) {
            $localMatchTxt = '<a href="javascript:editMatch(\''. $match->getId() .'\')">'. $match->getLocalId() .'</a>';
        	$team1Name = $match->getTeam1() == null ? 'Undefined' : $match->getTeam1()->getName();
        	$team2Name = $match->getTeam2() == null ? 'Undefined' : $match->getTeam2()->getName();
        	$matchArray = array(
        		'DT_RowId' => "matchId-". $match->getId(),
/*         		'DT_RowClass' => 'error', */
        		'localId' => $localMatchTxt,
        		'team1' => $team1Name,
        		'team2' => $team2Name,
        		'pool' => $match->getPool()->getName(),
        		'round' => $match->getRound(),
        		'score' => $match->getScoreTextual(),
        		'status' => ucfirst($match->getStatus()),
        		'nonreadyReason' => $match->getNonreadyReason(),
        	);
        	$res['aaData'][] = $matchArray;
        }
        
        return $res;
    }

    /**
      * Generates the data for all matches for a certain pool and round. If round == null, it will return all matches for this pool
	  * @var \TS\ApiBundle\Entity\Pool $pool
      */
	public function getPoolMatchesData($pool, $round) {
		$res = array();
		
		// get teams in this pool, to check whether which teams aren't playing
		$nonPlayingTeams = array();
		foreach ($pool->getTeams() as $team) {
			$nonPlayingTeams[$team->getId()] = $team;
		}	
		
		$repository = $this->doctrine
			->getRepository('TSApiBundle:Match');
		$queryParameters = array(
				'poolId' => $pool->getId(),
				'tournament' => $this->tournament
			);
		$query = $repository->createQueryBuilder('m')
			->andWhere('m.tournament = :tournament')
			->andWhere('m.pool = :poolId')
			->orderBy('m.localId', 'ASC');
		if ($round != null) {
			$query = $query->andWhere('m.round = :round');
			$queryParameters['round'] = $round;
		}
		$query = $query
			->setParameters($queryParameters)
			->getQuery();
		$matches = $query->getResult();
		
		foreach ($matches as $match) {
			$matchArray = array(
				'matchId' => $match->getId(),
				'localId' => $match->getLocalId(),
				'status' => $match->getStatus(),
				'nonreadyReason' => $match->getNonreadyReason(),
				'score' => $match->getScoreTextual(),
			);
			
			// add players to matchArray
			$team1 = $match->getTeam1();
			$matchArray['team1Players'] = array();
			if (!is_null($team1)) {
				$matchArray['team1Id'] = $team1->getId();
				foreach($team1->getPlayersForAllPositions() as $player) {
					$matchArray['team1Players'][$player->getId()] = $player->getName();
				}
				unset($nonPlayingTeams[$match->getTeam1()->getId()]);
			}
			$team2 = $match->getTeam2();
			$matchArray['team2Players'] = array();
			if (!is_null($team2)) {
				$matchArray['team2Id'] = $team2->getId();
				foreach($team2->getPlayersForAllPositions() as $player) {
					$matchArray['team2Players'][$player->getId()] = $player->getName();
				}
				unset($nonPlayingTeams[$match->getTeam2()->getId()]);
			}
			$res[$match->getId()] = $matchArray;
		}
		
		$nonPlayingTeamsArray = array();
		foreach ($nonPlayingTeams as $team) {
			$playersArray = array();
			foreach($team->getPlayersForAllPositions() as $player) {
				$playersArray[$player->getId()] = $player->getName();
			}
				
			$nonPlayingTeamsArray[] = array( 
				'teamId' => $team->getId(),
				'players' => $playersArray,
			);
		}
		$res['nonPlayingTeams'] = $nonPlayingTeamsArray;
		
		return $res;
	}


	/**
	  * Generates the data for matches with a specific status
	  */
	public function getStatusMatchesData($statusArray, $limit=null, $startPos=null, $sortOrder="ASC") {
		// query for getting the matches, based on the requested status
		$repository = $this->doctrine
		    ->getRepository('TSApiBundle:Match');
		$query = $repository->createQueryBuilder('m')
		    ->andWhere('m.tournament = :tournament')
		    ->setParameter('tournament', $this->tournament);
		$txtQuery = '';
		foreach ($statusArray as $i=>$status) {
			if ($i != 0) {
				$txtQuery .= " OR ";
			}
			$txtQuery .= "m.status = :status_". $i;
			
			$query = $query->setParameter('status_'. $i, $status);
		}
		$query = $query->andWhere($txtQuery);
		if (in_array(Match::STATUS_READY, $statusArray)) {
			// getting upcoming matches -> order by priority
			// TODO: making priority a status, so that the $res can contain the index in the key
			$query = $query->orderBy('m.priority', 'DESC');
		}
		$query = $query->addOrderBy('m.localId', $sortOrder);

		if (!is_null($limit) && is_numeric($limit) && $limit >= 0) {
			// query limit should be unsigned int
			$query = $query->setMaxResults($limit);
		}
		if (!is_null($startPos) && is_numeric($startPos) && $startPos >= 0) {
			// should be unsigned int
			$query = $query->setFirstResult($startPos);
		}
		$matches = $query
			->getQuery()
			->getResult();
        
        return $matches;
    }

    /**
      * Generates the data of a match in an array that can be returned
      * @param \TS\ApiBundle\Entity\Match $match
      */
    public function matchData($match) {
    	// getting currently playing players
		$currentlyPlayingPlayerIds = $this->getCurrentlyPlayingPlayerIds();

    	$teamData = array(
    		1 => null,
    		2 => null
    	);
    	foreach ($match->getTeams() as $key=>$team) {
        	if (!is_null($team)) {
        		$teamData[$key] = array(
        			"teamId" => $team->getId(),
        			"name" => $team->getName(),
        			"players" => array(),
        		);
        		foreach($team->getPlayersForAllPositions() as $player) {
        			$teamData[$key]['players'][] = array(
        				"playerId" => $player->getId(),
        				"name" => $player->getName(),
        				"currentlyPlaying" => in_array($player->getId(), $currentlyPlayingPlayerIds),
        				"ready" => $player->getReady(),
        			);
        		}
        	}
        }
    	$matchArray = array(
    		'matchId' => $match->getId(),
    		'localId' => $match->getLocalId(),
    		'team1' => $teamData[1],
    		'team2' => $teamData[2],
    		'pool' => $match->getPool()->getName(),
    		'round' => $match->getRound(),
    		'status' => ucfirst($match->getStatus()),
    		'nonreadyReason' => $match->getNonreadyReason(),
    		'priority' => $match->getPriority(),
    		'score' => $match->getScoreTextual(),
    	);
    	if (!is_null($match->getStartTime())) {
    		$matchArray['deltaStartTime'] = time() - $match->getStartTime()->getTimestamp();
    	}
    	return $matchArray;
    }

    /**
      * Returns the data of matches in a nicely formatted array.
      * Does the same as matchData for multiple matches
      */
    public function matchDataInArray($matches) {
    	$res = array();

    	foreach ($matches as $match) {
        	$res[] = $this->matchData($match);
        }
        return $res;
    }

    // get currently playing playerIds
	private function getCurrentlyPlayingPlayerIds() {
		if ($this->currentlyPlayingPlayerIds == null) {
			$repository = $this->doctrine
			    ->getRepository('TSApiBundle:Match');
			$query = $repository->createQueryBuilder('m')
			    ->andWhere('m.tournament = :tournament')
			    ->setParameter('tournament', $this->tournament)
			    ->andWhere('m.status = :status_playing')
			    ->setParameter('status_playing', Match::STATUS_PLAYING)
			    ->getQuery();
			$matches = $query->getResult();
			$currentlyPlayingPlayerIds = array();
			foreach ($matches as $match) {
				foreach ($match->getTeams() as $team) {
					if (!is_null($team)) {
						foreach ($team->getPlayersForAllPositions() as $player) {
							if (!in_array($player->getId(), $currentlyPlayingPlayerIds)) {
								$currentlyPlayingPlayerIds[] = $player->getId();
							}
						}
					}
				}
			}
			$this->currentlyPlayingPlayerIds = $currentlyPlayingPlayerIds;
		}

		return $this->currentlyPlayingPlayerIds;
	}
    	
    /**
      * Generate the data of all matches for a certain playerId
      */
	public function getPlayerMatchesData($playerId) {
		$res = array();	
		
		$repository = $this->doctrine
			->getRepository('TSApiBundle:Match');
		$query = $repository->createQueryBuilder('m')
			->andWhere('m.tournament = :tournament')
			->setParameter("tournament", $this->tournament)
			->leftJoin('m.team1', 'team1')
			->leftJoin('team1.players', 'players1')
			->leftJoin('m.team2', 'team2')
			->leftJoin('team2.players', 'players2')
			->andWhere('players1.id = :playerId OR players2.id = :playerId')
			->setParameter('playerId', $playerId)
			->orderBy('m.localId', 'ASC')
			->getQuery();
		$matches = $query->getResult();

		return $this->matchDataInArray($matches);
		
		
	}

	/**
	  * Generate data of all currently playing matches
	  * Matches are returned in order of: first matches that have no specific location, then ordered by location position
	  */
	public function getPlayingMatchesData() {
		$res = array();
		
		// first add matches that have no specific location
        $repository = $this->doctrine
		    ->getRepository('TSApiBundle:Match');
		$query = $repository->createQueryBuilder('m')
		    ->andWhere('m.tournament = :tournament')
		    ->setParameter('tournament', $this->tournament)
		    ->andWhere('m.location is NULL')
		    ->andWhere('m.status = :status_playing')
		    ->setParameter('status_playing', Match::STATUS_PLAYING)
		    ->orderBy('m.startTime', 'ASC')
		    ->getQuery();
		$matches = $query->getResult();
		
        foreach ($matches as $match) {
        	$matchArray = array();
        	$this->fillPlayingData($match, $matchArray);
	        $res[] = $matchArray;
        }

		// then return array with at least all locations, even if location doesn't have a match currently playing
		$repository = $this->doctrine
		    ->getRepository('TSApiBundle:Location');
		$query = $repository->createQueryBuilder('l')
		    ->andWhere('l.tournament = :tournament')
		    ->setParameter('tournament', $this->tournament)
		    ->orderBy('l.position', 'ASC')
		    ->getQuery();
		$locations = $query->getResult();
		
        foreach ($locations as $location) { /* @var \TS\ApiBundle\Entity\Location $location */
        	$match = $location->getMatch();
        	$matchArray = array();
        	$matchArray['locationId'] = $location->getId();
        	$matchArray['location'] = $location->getName();
        	$matchArray['locationOnHold'] = $location->getOnHold();
        	$matchArray['locationNonreadyReason'] = $location->getNonreadyReason();

        	if (!is_null($match)) {
	        	$this->fillPlayingData($match, $matchArray);
	        }
	        $res[] = $matchArray;
        }
        
        return $res;
    }
    
    /**
      * Generates the data for search matches
      */
	public function getSearchMatchesData($searchQuery) {
		$res = array();

		// getting sorting information
		$sortColumn = "m.localId";
        //Last matches first
        $sortDirection = "DESC";
		
		
		
		$repository = $this->doctrine
		    ->getRepository('TSApiBundle:Match');
		$query = $repository->createQueryBuilder('m')
		    ->andWhere('m.tournament = :tournament')
		    ->setParameter('tournament', $this->tournament)
		    ->leftJoin('m.pool', 'pool')
		    ->leftJoin('m.team1', 'team1')
		    ->leftJoin('team1.players', 'playersTeam1')
		    ->leftJoin('m.team2', 'team2')
		    ->leftJoin('team2.players', 'playersTeam2')
		    ->groupBy('m.id')
			->orderBy($sortColumn, $sortDirection);
		
		// search
		if ($searchQuery != '') {
			$query = $query
				->andWhere("m.localId LIKE :searchQuery OR playersTeam1.firstName LIKE :searchQuery OR playersTeam1.lastName LIKE :searchQuery OR playersTeam2.firstName LIKE :searchQuery OR playersTeam2.lastName LIKE :searchQuery OR pool.name LIKE :searchQuery OR m.round LIKE :searchQuery")
				->setParameter('searchQuery', '%'. $searchQuery .'%');
		}

		$matches = $query
			->getQuery()
			->getResult();

        foreach ($matches as $match) {
        	$team1Name = $match->getTeam1() == null ? 'Undefined' : $match->getTeam1()->getName();
        	$team2Name = $match->getTeam2() == null ? 'Undefined' : $match->getTeam2()->getName();
        	$matchArray = array(
        		'DT_RowId' => "matchId-". $match->getId(),
/*         		'DT_RowClass' => 'error', */
        		'localId' => $match->getLocalId(),
        		'team1' => $team1Name,
        		'team2' => $team2Name,
        		'pool' => $match->getPool()->getName(),
        		'round' => $match->getRound(),
        		'score' => $match->getScoreTextual(),
        		'status' => ucfirst($match->getStatus()),
        		'nonreadyReason' => $match->getNonreadyReason(),
        	);
        	$res[] = $matchArray;
        }
        
        return $res;
    }

    private function fillPlayingData($match, &$matchArray) {
    	$matchArray['matchId'] = $match->getId();
    	$matchArray['localId'] = $match->getLocalId();
    	
    	// lookup players of team1 and team2
    	$team1 = $match->getTeam1();
    	$matchArray['team1Players'] = array();
    	if (!is_null($team1)) {
    		$matchArray['team1Id'] = $team1->getId();
    		foreach($team1->getPlayersForAllPositions() as $player) {
	    		$matchArray['team1Players'][$player->getId()] = $player->getName();
	    	}
	    }
	    $team2 = $match->getTeam2();
	    $matchArray['team2Players'] = array();
    	if (!is_null($team2)) {
    		$matchArray['team2Id'] = $team2->getId();
    		foreach($team2->getPlayersForAllPositions() as $player) {
	    		$matchArray['team2Players'][$player->getId()] = $player->getName();
	    	}
	    }
    	
    	$matchArray['pool'] = $match->getPool()->getName();
    	$matchArray['round'] = $match->getRound();
        if (!is_null($match->getStartTime())) {
            $matchArray['deltaStartTime'] = time() - $match->getStartTime()->getTimestamp();
        }
		return $matchArray;
	}
    	
}