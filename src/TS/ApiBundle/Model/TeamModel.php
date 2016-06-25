<?php

namespace TS\ApiBundle\Model;

use TS\ApiBundle\Entity\Team;


class TeamModel
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
      * Adding a player to a team. When team == null, a new team will be created
      * @return Team The team object
      */
    public function addPlayerToTeam($pool, $team, $position, $player) {
		if (is_null($team)) {
    		// create new team
    		$team = new Team();
    		$team->setTournament($this->tournament);
    		$team->setPool($pool);
    		$pool->addTeam($team);

			$this->doctrine->getManager()->persist($team);
    	}
		
		// add player to team
		$team->setPlayerForPosition($position, $player);

		return $team;
    }

	/**
	 * @param \TS\ApiBundle\Entity\Team $team
	 * @return array
	 */
	public static function formatTeam($team) {
		$playersArray = array();
		for ($i = 0; $i < $team->getPool()->getNrPlayersInTeam(); $i++) {
			// fill playersArray with null for empty spots
			$playersArray[$i] = null;
		}
		foreach ($team->getPlayersForAllPositions(true) as $position=>$player) { /* @var \TS\ApiBundle\Entity\Player $player */
			$registrationGroup = (!is_null($player->getRegistrationGroup())) ? $player->getRegistrationGroup()->getName() : '';
			$hasReplacementPlayer = $team->hasReplacementPlayerForPosition($position);
			$playersArray[$position] = array(
				'id' => $player->getId(),
				'name' => $player->getName(false),
				'registrationGroup' => $registrationGroup,
				'gender' => $player->getGender(),
				'hasReplacementPlayer' => $hasReplacementPlayer,
			);

			// Find partner name of registration
			foreach($player->getDisciplinePlayers() as $disciplinePlayer) {
				foreach ($team->getPool()->getInputDisciplines() as $discipline) {
					if ($disciplinePlayer->getDiscipline() == $discipline) {
						if ($disciplinePlayer->getDiscipline()->getDisciplineType()->getPartnerRegistration()) {
							$partnerName = (empty($disciplinePlayer->getPartner())) ? "wanted" : $disciplinePlayer->getPartner();
							$playersArray[$position]['partnerName'] = $partnerName;
						}
					}
				}
			}

			if ($hasReplacementPlayer) {
				$replacementPlayer = $team->getPlayerForPosition($position, false);
				$playersArray[$position]['replacementPlayerId'] = $replacementPlayer->getId();
				$playersArray[$position]['replacementPlayerName'] = $replacementPlayer->getName();
			}
		}
		$res = array(
			'id' => $team->getId(),
			'name' => $team->getName(),
			'givenUp' => $team->getGivenUp(),
			'nonreadyReason' => $team->getNonreadyReason(),
			'players' => $playersArray,
		);
		return $res;
	}
}