<?php

namespace TS\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TS\ApiBundle\Tests\MainTest;
use TS\ApiBundle\Model\RankingModel;

class RoundControllerTest extends MainTest
{
	
	public function testList() {
		$this->startTest();
		
		// get all rounds while there are no disciplines
		$this->request('GET', 'round/list/all');
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		
		// get all possible rounds
		$this->request('GET', 'round/list/all');
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		// get rounds of discipline 2
		$this->request('GET', 'round/list/'. $this->disciplines[2]->getId());
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		// creating and testing the new round
		$this->performScenario5();
		
		$this->request('GET', 'round/list/all');
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response));
		$this->assertEquals("Round 1", $this->response[1]);
		
		// get rounds of discipline 2
		$this->request('GET', 'round/list/'. $this->disciplines[2]->getId());
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response));
		$this->assertEquals("Round 1", $this->response[1]);
		
		
		$this->endTest();
	}
	/*
	public function testNew() {
		$this->startTest();
		
		$this->performScenario1();
		
		// making a discipline a bit bigger, to make testing easier
		$commandArray = array();
		foreach ($this->players as $player) {
			$commandArray[] = array(
				'command' => 'Disciplines.addPlayer',
				'disciplineId' => $this->disciplines[0]->getId(),
				'playerId' => $player->getId(),
			);
		}
		$this->request('POST', 'command', array('commands' => $commandArray));
		
		$this->performScenario3();
		$this->performScenario5();
		
		// create a new round in an empty discipline
		$this->request('POST', 'round/new', array(
			'disciplineId' => $this->disciplines[1]->getId()
		));
		$this->assertErrorResponse('Discipline '. $this->disciplines[1]->getName() .' has no teams and therefore it is not possible to create a new round');
		
		$this->request('GET', 'round/list/all');
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response));
		$this->assertEquals("Round 1", $this->response[1]);
		
		// get rounds of discipline 0 (should be 1 round)
		$this->request('GET', 'round/list/'. $this->disciplines[0]->getId());
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response));
		$this->assertEquals("Round 1", $this->response[1]);
		
		// check whether matches have been made correctly according to the ranking
		$this->request('GET', 'match/listdiscipline/'. $this->disciplines[0]->getId() .'/Round 1');
		$this->standardChecks();
		$this->assertEquals(6, sizeof($this->response));
		
		// create round 2 with scores of matches
		$commandArray = array();
		$commandArray[] = array(
			'command' => 'Matches.score',
			'matchId' => $this->matches[0]->getId(),
			'score' => array(
				array(10, 20),
				array(20, 15),
			),
		);
		$commandArray[] = array(
			'command' => 'Matches.score',
			'matchId' => $this->matches[1]->getId(),
			'score' => array(
				array(1, 20),
				array(2, 20),
			),
		);
		$this->request('POST', 'command', array('commands' => $commandArray));
		
		$this->request('POST', 'round/new', array(
			'disciplineId' => $this->disciplines[0]->getId()
		));
		$this->assertEquals("created new round Round 2 for discipline ". $this->disciplines[0]->getName(), $this->response);
		
		// get rounds of discipline 0 (should be 2 rounds)
		$this->request('GET', 'round/list/'. $this->disciplines[0]->getId());
		$this->standardChecks();
		$this->assertEquals(2, sizeof($this->response));
		$this->assertEquals("Round 1", $this->response[1]);
		$this->assertEquals("Round 2", $this->response[2]);
		
		// get rounds of all disciplines (should be 2 rounds)
		$this->request('GET', 'round/list/all');
		$this->standardChecks();
		$this->assertEquals(2, sizeof($this->response));
		$this->assertEquals("Round 1", $this->response[1]);
		$this->assertEquals("Round 2", $this->response[2]);
		
		// check whether matches have been made correctly according to the ranking
		$this->request('GET', 'match/listdiscipline/'. $this->disciplines[0]->getId() .'/Round 2');
		$this->standardChecks();
		$this->assertEquals(6, sizeof($this->response));
		
		$rankingModel = new RankingModel($this->em, $this->tournament);
		// reloading discipline
		$pool = $this->em
        	->getRepository('TSApiBundle:Pool')
        	->findOneById($this->pools[0]->getId());
		$rankingArray = $rankingModel->getPoolRankingData($pool);
		
		exit("ranking array = ". print_r($rankingArray, true));
		$this->assertEquals($rankingArray[1]['teamId'], $this->response[$this->teams[0]->getId()]['id']);
		
		exit("tournament = ". $this->tournament->getUrl() ." cateogyr = ". $this->disciplines[0]->getId() ." res = ". print_r($this->response, true));
		
		// giving up team and checking for free round
		
		$this->endTest();
	}*/
	
	/*
	public function testRemove() {
		$this->startTest();
		
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario5();
		
		$this->request('POST', 'round/remove', array(
			'disciplineId' => $this->disciplines[3]->getId(),
		//	'round' => 'all'
		));
		// TODO: this isn't working yet
		
		$this->request('GET', 'round/list/all');
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response));
		$this->assertEquals("Round 1", $this->response[1]);
		
		// get rounds of discipline 2
		$this->request('GET', 'round/list/'. $this->disciplines[2]->getId());
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response));
		$this->assertEquals("Round 1", $this->response[1]);
		
		// get rounds of discipline 3
		$this->request('GET', 'round/list/'. $this->disciplines[3]->getId());
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		// and now remove all rounds
		$this->request('POST', 'round/remove', array(
			'disciplineId' => 'all'
		));
		// TODO: not possible...
		
		$this->request('GET', 'round/list/all');
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		// get rounds of discipline 2
		$this->request('GET', 'round/list/'. $this->disciplines[2]->getId());
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		$this->endTest();
	}*/
	
}
