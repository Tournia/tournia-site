<?php

namespace TS\ApiBundle\Algorithm;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TS\ApiBundle\Entity\Match;
use TS\NotificationBundle\Event\MatchEvent;
use TS\NotificationBundle\NotificationEvents;

abstract class AlgorithmAbstract
{
    protected $entityManager;
    protected $eventDispatcher;

    protected $pool;
    protected $tournament;
    
    public function __construct(EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

	protected function getTeam($teamId) 
	{
		$team = $this->entityManager->getRepository('TSApiBundle:Team')
                ->findOneBy(array('tournament' => $this->tournament, 'id' => $teamId));

		if (!$team) {
			throw $this->throwError('No team found for id '. $teamId);
		}
		return $team;
	}

	public function newRound($pool, $tournament)
	{
		$this->pool = $pool;
		$this->tournament = $tournament;
        return $this->generateNewRound();
	}

    abstract protected function generateNewRound();


	protected function saveMatches($matches, $round, $pool)
	{
		if(sizeof($matches) > 0) {
			//Monolog::getInstance()->addDebug('Matches: ' . var_export($matches, true));

			foreach($matches as $match)	{
				$team1 = (is_null($match['team1']['teamId'])) ? null : $this->getTeam($match['team1']['teamId']);
				$team2 = (is_null($match['team2']['teamId'])) ? null : $this->getTeam($match['team2']['teamId']);

				$match = new Match();
				$match->setTeam1( $team1 );
				$match->setTeam2( $team2 );
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
			/* 			Monolog::getInstance()->addDebug('Matches added for poule: '.$poule['id'].', round: '.$poule['round'] + 1); */
		}
	}

	/**
	 * Return array with (not given up) teams, with in the value the teamId
	 */
	protected function getNonGivenUpTeams($pool)
	{
		// getting all the teams
		$repository = $this->entityManager->getRepository('TSApiBundle:Team');
		$query = $repository->createQueryBuilder('t')
			->select('t')
			->andWhere('t.tournament = :tournament')
			->andWhere('t.pool = :pool')
			->andWhere('t.givenUp = false')
			->setParameter('tournament', $this->tournament)
			->setParameter('pool', $pool);
		$teamObjects = $query->getQuery()->getResult();

		$res = array();
		foreach ($teamObjects as $teamObject) {
			$res[] = $teamObject->getId();
		}
		return $res;
	}
	
}
