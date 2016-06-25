<?php

namespace TS\ApiBundle\Algorithm\SwissLadder\TSSjoerd;

use TS\ApiBundle\Algorithm\AlgorithmAbstract;
use TS\ApiBundle\Model\RankingModel;
use TS\ApiBundle\Entity\Match;
use TS\NotificationBundle\Event\MatchEvent;
use TS\NotificationBundle\NotificationEvents;

class TsSjoerdAlgorithm extends AlgorithmAbstract
{

	protected function generateNewRound()
	{
		$pool = $this->pool;

		// deciding about round's name
		$matchRepository = $this->entityManager->getRepository('TSApiBundle:Match');
		$rounds = $matchRepository->getAllRounds($this->tournament, $pool);
		
		if (sizeof($rounds) == 0) {
			$newRound = 'Round 1';
		}
		else {
			end($rounds);
			$newRoundNr = key($rounds) + 1;
			$newRound = 'Round '. $newRoundNr;
		}
		
		$teams = $pool->getTeams();
		if (sizeof($teams) > 1)	{
			// saving previous matches (to prevent multiple bye's) and keep a list of teams that have to be selected ($toBeSelectedTeams)
			$previousMatches = array();
			$toBeSelectedTeams = array(); // teams(Id)s that have to be given an opponent, at the moment all the teams in this pool
			
			foreach ($teams as $team) {
				$previousMatches[$team->getId()] = array(
					'teamObject' => $team,
					'playedInRound' => array(),
					'playedMatches' => array(),
					'opponents' => array(),
				);
				if (!$team->getGivenUp()) {
					$toBeSelectedTeams[$team->getId()] = $team;
				}
			}
			
			// getting all the previous matches
			$repository = $this->entityManager->getRepository('TSApiBundle:Match');
			$query = $repository->createQueryBuilder('m')
				                ->andWhere('m.tournament = :tournament')
                                ->andWhere('m.pool = :poolId')
                                ->setParameter('tournament', $this->tournament)
                                ->setParameter('poolId', $pool->getId());
			$matches = $query->getQuery()->getResult();
			
			// now save previous matches for all teams
			foreach ($matches as $match) {
				$teams = $match->getTeams();

				if (!is_null($teams[1]) && !is_null($teams[2]))	{
					foreach($teams as $team) {
						$previousMatches[$team->getId()]['playedInRound'][] = $match->getRound();
						$previousMatches[$team->getId()]['playedMatches'][] = $match;
					}
					
					$previousMatches[$teams[1]->getId()]['opponents'][] = $teams[2]->getId();
					$previousMatches[$teams[2]->getId()]['opponents'][] = $teams[1]->getId();
				}
			}
			
			if (sizeof($toBeSelectedTeams) % 2 == 1) {
				// uneven number of teams -> select free round team
				// free round team = team with most rounds played
				$maxPlayedRounds = -1;
				$maxPlayedTeams = array();
				
				foreach ($toBeSelectedTeams as $team) {
					$teamId = $team->getId();
					$nrPlayedRounds = sizeof($previousMatches[$teamId]['playedInRound']);
				
					if ($nrPlayedRounds > $maxPlayedRounds)	{
						// this team has more rounds than others -> save new maximum
						$maxPlayedTeams = array();
						$maxPlayedRounds = $nrPlayedRounds;
					}
					
					if ($nrPlayedRounds == $maxPlayedRounds) {
						// this team has played the most rounds -> save in array to randomly pick from all teams which have played this number of rounds as well
						$maxPlayedTeams[$teamId] = $team;
					}
				}
				// remove free round team from to be selected teams
				$freeRoundTeamId = array_rand($maxPlayedTeams);
				
				unset($toBeSelectedTeams[$freeRoundTeamId]);
			}

			// and now make round list based on ranking
			$rankingModel = new RankingModel($this->entityManager, $this->tournament);
			$rankingArray = $rankingModel->getPoolRankingData($pool);

			foreach ($rankingArray as $rank=>$rankData)	{
				$teamId = $rankData['teamId'];
				
				// start with top-playing teams, and go down for selecting a suitable opponent
				// check whether team has to be given an opponent (and that there are opponents (which should, but is just an extra check))
				if (array_key_exists($teamId, $toBeSelectedTeams) && (sizeof($toBeSelectedTeams) > 1)) { 
					$opponentTeamId = null;
					// now check for opponent that hasn't been playing against team (or otherwise team that has played the least)
					$maxTimesPlayedOpponent = 0;
					while (is_null($opponentTeamId)) {
						// go through to be selected teams, and select first team in ranking as opponent
						foreach ($rankingArray as $rank2=>$possibleOpponentObject) {
							$possibleOpponentTeamId = $possibleOpponentObject['teamId'];
							
							if (array_key_exists($possibleOpponentTeamId, $toBeSelectedTeams) && ($teamId != $possibleOpponentTeamId)) {
								// lookup number of times this possible opponent has played against teamId
								$nrTimesPlayedAgainstThisOpponent = 0;
								
								foreach ($previousMatches[$teamId]['opponents'] as $tmpOpponentId) {
									if ($tmpOpponentId == $possibleOpponentTeamId) {
										$nrTimesPlayedAgainstThisOpponent++;
									}
								}
								
								if ($maxTimesPlayedOpponent >= $nrTimesPlayedAgainstThisOpponent) {
									// We found a match! This opponent is chosen
									$opponentTeamId = $possibleOpponentTeamId;
									break 1;
								}
							}
						}
						
						// increase maxTimesPlayedOpponent
						$maxTimesPlayedOpponent++;
					}
					
					// now create the match between $teamId and $opponentTeamId
					$this->createNewMatch($teamId, $opponentTeamId, $pool, $newRound);
					
					unset($toBeSelectedTeams[$teamId]);
					unset($toBeSelectedTeams[$opponentTeamId]);
				}
			}
		}
		
		return $newRound;
	}
	
	private function createNewMatch($team1Id, $team2Id, $pool, $round)
	{
		$match = new Match();
		$match->setTeam1($this->getTeam($team1Id));
		$match->setTeam2($this->getTeam($team2Id));
		$match->setPool($pool);
		$match->setRound($round);
		$match->setTournament($this->tournament);
		
		// lookup new localId
		$this->entityManager->flush();
		$repository = $this->entityManager->getRepository('TSApiBundle:Match');
		$newLocalId = intval($repository->getLastLocalId($this->tournament)) + 1;
		$match->setLocalId($newLocalId);
		
		$this->entityManager->persist($match);
		$this->entityManager->flush();

		// trigger new Match event
		$event = new MatchEvent($match);
		$this->eventDispatcher->dispatch(NotificationEvents::MATCH_NEW, $event);
	}
}
