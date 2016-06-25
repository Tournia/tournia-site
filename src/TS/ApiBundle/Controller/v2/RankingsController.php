<?php

namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use TS\ApiBundle\Model\RankingModel;


class RankingsController extends ApiV2MainController
{
	
    
    /**
	 * Get ranking data of a certain pool
     *
     * @ApiDoc(
	 *  views="v2",
	 *  section="Rankings",
     *  description="Rankings.pool"
     * )
     */
    public function poolAction($poolId) {
		$rankingModel = new RankingModel($this->getDoctrine(), $this->tournament);
		
		$pool = $this->getPool($poolId);
		$ranking = $rankingModel->getPoolRankingData($pool);
		// now round of relative ranking numbers
		foreach ($ranking as &$teamData) {
			$teamData['matchesRelative'] = sprintf("%1\$.3f", $teamData['matchesRelative']);
			$teamData['setsRelative'] = sprintf("%1\$.3f", $teamData['setsRelative']);
			$teamData['pointsRelative'] = sprintf("%1\$.3f", $teamData['pointsRelative']);
		}
        
        return $this->handleResponse($ranking);
	}

	/**
	 * Get winners of all pools
     *
     * @ApiDoc(
	 *  views="v2",
	 *  section="Rankings",
     *  description="Rankings.poolWinners",
     *  filters = {
     *		{"name"="ranks", "required"="false", "type"="integer", "description"="Number of returned ranks", "default"="3"},
     *  }
     * )
     */
    public function poolWinnersAction() {
		$ranks = $this->getParam('ranks', false, 3);

		$rankingModel = new RankingModel($this->getDoctrine(), $this->tournament);

		$poolArray = $this->tournament->getPools();
		
		$rankingPools = array();
		foreach ($poolArray as $pool) {
			$ranking = $rankingModel->getPoolRankingData($pool);
			$rankingData = array();

			for ($i = 0; ($i < $ranks) && ($i < count($ranking)); $i++) {
				// save ranking data
				$teamData = $ranking[$i];
				$teamData['matchesRelative'] = sprintf("%1\$.3f", $teamData['matchesRelative']);
	   			$teamData['setsRelative'] = sprintf("%1\$.3f", $teamData['setsRelative']);
	   			$teamData['pointsRelative'] = sprintf("%1\$.3f", $teamData['pointsRelative']);
				$rankingData[$i] = $teamData;
	   		}
    	
			$rankingPools[] = array(
				'poolId' => $pool->getId(),
				'poolName' => $pool->getName(),
				'ranking' => $rankingData,
			);
		}
        
        return $this->handleResponse($rankingPools);
	}
	
	/**
	 * Get ranking data of all players
     *
     * @ApiDoc(
	 *  views="v2",
	 *  section="Rankings",
     *  description="Rankings.players"
     * )
     */
    public function playersAction() {
		$rankingModel = new RankingModel($this->getDoctrine(), $this->tournament);
		
		$ranking = $rankingModel->getPlayersRankingData();
		// now round relative ranking numbers
		foreach ($ranking as &$playerData) {
			$playerData['relative'] = sprintf("%1\$.3f", $playerData['relative']);
		}
        
        return $this->handleResponse($ranking);
	}
	
	/**
	 * Get ranking data of all groups
     *
     * @ApiDoc(
	 *  views="v2",
	 *  section="Rankings",
     *  description="Rankings.groups"
     * )
     */
    public function groupsAction() {
		$rankingModel = new RankingModel($this->getDoctrine(), $this->tournament);
		
		$ranking = $rankingModel->getGroupsRankingData();
		// now round relative ranking numbers
		foreach ($ranking as &$playerData) {
			$playerData['relative'] = sprintf("%1\$.3f", $playerData['relative']);
		}
        
        return $this->handleResponse($ranking);
	}
}