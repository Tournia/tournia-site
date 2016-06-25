<?php

namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use TS\ApiBundle\Entity\Discipline;

use TS\ApiBundle\Model\TeamModel;
use TS\ApiBundle\Entity\Match;

class DisciplinesController extends ApiV2MainController
{
	
    
    /**
	 * Get list of disciplines
     *
     * @ApiDoc(
	 *  views="v2",
	 *  section="Disciplines",
     *  description="Disciplines.list"
     * )
     */
    public function listAction() {
        $resArray = array();
        foreach ($this->tournament->getDisciplines() as $discipline) {
        	$resArray[] = $this->getDisciplineData($discipline);
        }
        
        return $this->handleResponse($resArray);
	}
	
	
	/**
	 * Get a discipline
	 *
	 * @ApiDoc(
	 *  views="v2",
	 *  section="Disciplines",
	 *  description="Disciplines.get"
	 * )
	 */
	public function getAction($disciplineId) {
		$discipline = $this->getDiscipline($disciplineId);
		$res = $this->getDisciplineData($discipline);
		return $this->handleResponse($res);
	}

    
    // returns players data of a discipline. If discipline is null, return all data of all disciplines
	/**
	 * @param \TS\ApiBundle\Entity\Discipline $discipline
	 * @return array
	 */
    private function getDisciplineData($discipline) {
    	$res = array();
		$res['disciplineId'] = $discipline->getId();
		$res['name'] = $discipline->getName();

		$poolsArray = array();
		foreach($discipline->getPools() as $pool) {
			$poolsArray[$pool->getId()] = $pool->getName();
		}
		$res['pools'] = $poolsArray;

        return $res;
    }
}