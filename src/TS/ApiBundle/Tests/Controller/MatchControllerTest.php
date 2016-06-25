<?php

namespace TS\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TS\ApiBundle\Tests\MainTest;

class MatchControllerTest extends MainTest
{
	
	public function testEdit() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario5();
		
		// change disciplineId
		$match = $this->matches[0];
		$currentRound = $match->getRound();
		$currentTeam1 = $match->getTeam1()->getId();
		$currentTeam2 = $match->getTeam2()->getId();
		$currentLocalId = $match->getLocalId();
		$currentPriority = $match->getPriority();
		
		$this->request('POST', 'match/edit', array(
			'matchId' => $match->getId(),
			'disciplineId' => $this->disciplines[2]->getId(),
		));
		$this->assertEquals('edited match '. $match->getLocalId(), $this->response);
		$this->request('GET', "match/get/". $match->getId());
		$this->assertEquals($this->disciplines[2]->getId(), $this->response['disciplineId']);
		$this->assertEquals($currentRound, $this->response['round']);
		$this->assertEquals($currentTeam1, $this->response['team1Id']);
		$this->assertEquals($currentTeam2, $this->response['team2Id']);
		$this->assertEquals($currentLocalId, $this->response['localId']);
		$this->assertEquals($currentPriority, $this->response['priority']);
		
		// leave everything the same
		$match = $this->matches[1];
		$currentDiscipline = $match->getDiscipline()->getId();
		$currentRound = $match->getRound();
		$currentTeam1 = $match->getTeam1()->getId();
		$currentTeam2 = $match->getTeam2()->getId();
		$currentLocalId = $match->getLocalId();
		$currentPriority = $match->getPriority();
		
		$this->request('POST', 'match/edit', array(
			'matchId' => $match->getId(),
		));
		$this->request('GET', "match/get/". $match->getId());
		$this->assertEquals($currentDiscipline, $this->response['disciplineId']);
		$this->assertEquals($currentRound, $this->response['round']);
		$this->assertEquals($currentTeam1, $this->response['team1Id']);
		$this->assertEquals($currentTeam2, $this->response['team2Id']);
		$this->assertEquals($currentLocalId, $this->response['localId']);
		$this->assertEquals($currentPriority, $this->response['priority']);
		
		// changing round, localId and priority
		$this->request('POST', 'match/edit', array(
			'matchId' => $this->matches[2]->getId(),
			'round' => 'Round something',
			'localId' => '1932',
			'priority' => true,
		));
		$this->request('GET', "match/get/". $this->matches[2]->getId());
		$this->assertEquals('Round something', $this->response['round']);
		$this->assertEquals('1932', $this->response['localId']);
		$this->assertEquals(true, $this->response['priority']);
		
		// changing team1 and team2
		$this->request('POST', 'match/edit', array(
			'matchId' => $this->matches[3]->getId(),
			'team1' => '',
			'team2' => $this->teams[8]->getId(),
		));
		$this->request('GET', "match/get/". $this->matches[3]->getId());
		$this->assertEquals('', $this->response['team1Id']);
		$this->assertEquals('', $this->response['team1Name']);
		$this->assertEquals($this->teams[8]->getId(), $this->response['team2Id']);
		$this->assertEquals($this->teams[8]->getName(), $this->response['team2Name']);
		
		$this->endTest();
	}
	
	public function testFinish() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario5();
		
		// finish a match
		$this->request('POST', 'match/finish', array(
			'matchId' => $this->matches[2]->getId(),
		));
		$this->assertEquals('finished match '. $this->matches[2]->getLocalId(), $this->response);
		$this->request('GET', "match/get/". $this->matches[2]->getId());
		$this->assertEquals('finished', $this->response['status']);
		
		$this->endTest();
	}
	
	public function testGet() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario5();
		
		$this->request('GET', 'match/get/'. $this->matches[1]->getId());
		$this->standardChecks();
		$this->assertEquals($this->matches[1]->getId(), $this->response['id']);
		$this->assertEquals($this->matches[1]->getLocalId(), $this->response['localId']);
		$this->assertEquals($this->matches[1]->getTeam1()->getId(), $this->response['team1Id']);
		$this->assertEquals($this->matches[1]->getTeam1()->getName(), $this->response['team1Name']);
		$this->assertEquals($this->matches[1]->getTeam2()->getId(), $this->response['team2Id']);
		$this->assertEquals($this->matches[1]->getTeam2()->getName(), $this->response['team2Name']);
		$this->assertEquals($this->matches[1]->getDiscipline()->getName(), $this->response['disciplineName']);
		$this->assertEquals($this->matches[1]->getRound(), $this->response['round']);
		$this->assertEquals($this->matches[1]->getStatus(), $this->response['status']);
		$this->assertEquals(false, $this->response['priority']);
		$this->assertEquals('', $this->response['scoreText']);
		$this->assertEquals(0, sizeof($this->response['score']));
		
		$this->endTest();
	}
	
	public function testListDiscipline() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario5();
		
		$this->request('GET', 'match/listdiscipline/'. $this->disciplines[0]->getId() .'/Round 1');
		$this->standardChecks();
		$this->assertEquals(3, sizeof($this->response));
		
		$match0 = $this->response[$this->matches[0]->getId()];
		$this->assertEquals($this->matches[0]->getId(), $match0['matchId']);
		$this->assertEquals($this->matches[0]->getLocalId(), $match0['localId']);
		
		$this->assertEquals($this->matches[1]->getId(), $this->response[$this->matches[1]->getId()]['matchId']);
		
		$this->assertEquals(1, sizeof($this->response['nonPlayingTeams']));
		
		// all rounds
		$this->request('GET', 'match/listdiscipline/'. $this->disciplines[0]->getId() .'/all');
		$this->standardChecks();
		$this->assertEquals(3, sizeof($this->response));
		$match0 = $this->response[$this->matches[0]->getId()];
		$this->assertEquals($this->matches[0]->getId(), $match0['matchId']);
		$this->assertEquals($this->matches[0]->getLocalId(), $match0['localId']);
		
		$this->endTest();
	}
	
	public function testListStatus() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario5();
		
		$this->request('POST', 'match/liststatus', array(
			'status' => 'ready',
		));
		$this->standardChecks();
		$this->assertEquals(7, sizeof($this->response));
		
		$match0 = $this->response[$this->matches[0]->getId()];
		$this->assertEquals($this->matches[0]->getId(), $match0['matchId']);
		$this->assertEquals($this->matches[0]->getLocalId(), $match0['localId']);
		$this->assertEquals($this->matches[0]->getTeam1()->getId(), $match0['team1']['teamId']);
		$this->assertEquals($this->matches[0]->getTeam2()->getId(), $match0['team2']['teamId']);
		$this->assertEquals($this->matches[0]->getDiscipline()->getName(), $match0['discipline']);
		$this->assertEquals($this->matches[0]->getRound(), $match0['round']);
		$this->assertEquals(ucfirst($this->matches[0]->getStatus()), $match0['status']);
		$this->assertEquals($this->matches[0]->getPriority(), $match0['priority']);
		$this->assertEquals('', $match0['score']);
		
		$this->assertEquals($this->matches[1]->getId(), $this->response[$this->matches[1]->getId()]['matchId']);
		
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[3]->getId(),
			'status' => 'played',
		));
		$this->request('POST', 'match/liststatus', array(
			'status' => 'played',
		));
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response));
		$this->assertEquals($this->matches[3]->getId(), $this->response[$this->matches[3]->getId()]['matchId']);
		
		$this->endTest();
	}
	
	public function testNew() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario5();
		
		// create a match normally
		$this->request('POST', 'match/new', array(
			'disciplineId' => $this->disciplines[0]->getId(),
			'round' => 'Round 1',
			'team1' => $this->teams[0]->getId(),
			'team2' => $this->teams[4]->getId(),
		));
		$newId = $this->response['id'];
		
		$this->request('GET', "match/get/". $newId);
		$this->assertEquals($this->disciplines[0]->getId(), $this->response['disciplineId']);
		$this->assertEquals($this->teams[0]->getId(), $this->response['team1Id']);
		$this->assertEquals($this->teams[4]->getId(), $this->response['team2Id']);
		
		// create match with only one team
		$this->request('POST', 'match/new', array(
			'disciplineId' => $this->disciplines[0]->getId(),
			'round' => 'Round 1',
			'team1' => $this->teams[2]->getId(),
			'team2' => '',
		));
		$newId = $this->response['id'];
		
		$this->request('GET', "match/get/". $newId);
		$this->assertEquals($this->disciplines[0]->getId(), $this->response['disciplineId']);
		$this->assertEquals($this->teams[2]->getId(), $this->response['team1Id']);
		$this->assertEquals('', $this->response['team2Id']);
		$this->assertEquals(false, $this->response['priority']);
		
		// create match with zero teams and priority
		$this->request('POST', 'match/new', array(
			'disciplineId' => $this->disciplines[0]->getId(),
			'round' => 'Round 1',
			'team1' => '',
			'team2' => '',
			'priority' => true,
		));
		$newId = $this->response['id'];
		
		$this->request('GET', "match/get/". $newId);
		$this->assertEquals($this->disciplines[0]->getId(), $this->response['disciplineId']);
		$this->assertEquals('', $this->response['team1Id']);
		$this->assertEquals('', $this->response['team2Id']);
		$this->assertEquals(true, $this->response['priority']);
		
		$this->endTest();
	}
	
	public function testPlaying() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario4();
		$this->performScenario5();
		
		// start match on location 2
		$this->request('POST', 'match/start', array(
			'matchId' => $this->matches[0]->getId(),
			'locationId' => $this->locations[2]->getId(),
		));
		// start match on unknown location
		$this->request('POST', 'match/start', array(
			'matchId' => $this->matches[3]->getId(),
		));
		
		// now get playing list
		$this->request('GET', 'match/listplaying');
		$this->standardChecks();
		$this->assertEquals(5, sizeof($this->response));
		reset($this->response);

		// check match on unknown location, because this is the first returned match
		$row = current($this->response);
		$this->assertEquals($this->matches[3]->getId(), $row['matchId']);
		$this->assertEquals($this->matches[3]->getTeam1()->getId(), $row['team1Id']);
		$this->assertEquals($this->matches[3]->getTeam2()->getId(), $row['team2Id']);

		// check second match
		$row = next($this->response);
		$this->assertEquals($this->locations[0]->getId(), $row['locationId']);
		$this->assertEquals($this->locations[0]->getName(), $row['location']);
		$this->assertEquals(false, $row['locationOnHold']);
		
		next($this->response);
		$row = next($this->response);
		$this->assertEquals($this->locations[2]->getId(), $row['locationId']);
		$this->assertEquals($this->locations[2]->getName(), $row['location']);
		$this->assertEquals(false, $row['locationOnHold']);
		$this->assertEquals($this->matches[0]->getId(), $row['matchId']);
		$this->assertEquals($this->matches[0]->getLocalId(), $row['localId']);
		$this->assertEquals($this->matches[0]->getTeam1()->getPlayers()[0]->getName(), $row['team1Players'][$this->matches[0]->getTeam1()->getPlayers()[0]->getId()]);
		$this->assertEquals($this->matches[0]->getTeam2()->getPlayers()[0]->getName(), $row['team2Players'][$this->matches[0]->getTeam2()->getPlayers()[0]->getId()]);
		$this->assertEquals($this->matches[0]->getTeam1()->getId(), $row['team1Id']);
		$this->assertEquals($this->matches[0]->getTeam2()->getId(), $row['team2Id']);
		$this->assertEquals($this->matches[0]->getDiscipline()->getName(), $row['discipline']);
		$this->assertEquals($this->matches[0]->getRound(), $row['round']);
		$this->assertTrue(array_key_exists('deltaStartTime', $row));
		
		$this->endTest();
	}
	
	public function testRemove() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario4();
		$this->performScenario5();
		
		// delete a match ready for playing
		$this->request('POST', 'match/remove', array(
			'matchId' => $this->matches[2]->getId(),
		));
		$this->assertEquals('removed match '. $this->matches[2]->getLocalId(), $this->response);
		
		// delete a match that is currently playing
		$this->request('POST', 'match/start', array(
			'matchId' => $this->matches[3]->getId(),
			'locationId' => $this->locations[2]->getId(),
		));
		$this->request('POST', 'match/remove', array(
			'matchId' => $this->matches[3]->getId(),
		));
		$this->assertEquals('removed match '. $this->matches[3]->getLocalId(), $this->response);
		
		// now get matches list
		$this->request('POST', 'match/liststatus', array(
			'status' => 'ready',
		));
		$this->standardChecks();
		$this->assertFalse(array_key_exists($this->matches[2]->getId(), $this->response));
		$this->assertFalse(array_key_exists($this->matches[3]->getId(), $this->response));
		$this->assertTrue(array_key_exists($this->matches[4]->getId(), $this->response));
		
		$this->endTest();
	}
	
	public function testScore() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario5();
		
		// setting normal score
		$this->request('POST', 'match/score', array(
			'matchId' => $this->matches[0]->getId(),
			'score' => array(
				array(5,21),
				array(9,21),
			),
		));
		$this->assertEquals('set score of match '. $this->matches[0]->getLocalId() .' to 5-21 9-21', $this->response);
		
		// setting invalid score
		$this->request('POST', 'match/score', array(
			'matchId' => $this->matches[1]->getId(),
			'score' => array(
				array(9,5),
				array(9),
			),
		));
		$this->assertErrorResponse('Set 2 does not have two scores');
		
		// setting score of 3 sets
		$this->request('POST', 'match/score', array(
			'matchId' => $this->matches[2]->getId(),
			'score' => array(
				array(5,21),
				array(9,21),
				array(92,15),
			),
		));
		$this->assertEquals('set score of match '. $this->matches[2]->getLocalId() .' to 5-21 9-21 92-15', $this->response);
		
		// getting match list and checking status
		$this->request('POST', 'match/liststatus', array(
			'status' => array('played')
		));
		$this->standardChecks();
		$this->assertEquals(2, sizeof($this->response));
		$this->assertEquals('5-21 9-21', $this->response[$this->matches[0]->getId()]['score']);
		$this->assertFalse(array_key_exists($this->matches[1]->getId(), $this->response));
		$this->assertEquals('5-21 9-21 92-15', $this->response[$this->matches[2]->getId()]['score']);
		
		$this->endTest();
	}
	
	public function testSetStatus() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario5();
		
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[0]->getId(),
			'status' => "postponed",
		));
		$this->assertEquals("Match ". $this->matches[0]->getLocalId() ." is now postponed", $this->response);
		
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[1]->getId(),
			'status' => "ready",
		));
		$this->assertEquals("Match ". $this->matches[1]->getLocalId() ." is now ready", $this->response);
		
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[2]->getId(),
			'status' => "finished",
		));
		$this->assertEquals("Match ". $this->matches[2]->getLocalId() ." is now finished", $this->response);
		
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[3]->getId(),
			'status' => "played",
		));
		$this->assertEquals("Match ". $this->matches[3]->getLocalId() ." is now played", $this->response);
		
		// changing from played to ready
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[4]->getId(),
			'status' => "played",
		));
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[4]->getId(),
			'status' => "postponed",
		));
		$this->assertEquals("Match ". $this->matches[4]->getLocalId() ." is now postponed", $this->response);
		
		// getting match list and checking status
		$this->request('POST', 'match/liststatus', array(
			'status' => array('postponed', 'ready', 'playing', 'finished', 'played')
		));
		$this->standardChecks();
		$this->assertEquals('Postponed', $this->response[$this->matches[0]->getId()]['status']);
		$this->assertEquals('Ready', $this->response[$this->matches[1]->getId()]['status']);
		$this->assertEquals('Finished', $this->response[$this->matches[2]->getId()]['status']);
		$this->assertEquals('Played', $this->response[$this->matches[3]->getId()]['status']);
		$this->assertEquals('Postponed', $this->response[$this->matches[4]->getId()]['status']);
		
		$this->endTest();
	}
	
	public function testStart() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario4();
		$this->performScenario5();
		
		// starting a match normally
		$this->request('POST', 'match/start', array(
			'matchId' => $this->matches[0]->getId(),
			'locationId' => $this->locations[1]->getId(),
		));
		$this->assertEquals('started match '. $this->matches[0]->getLocalId() .' on '. $this->locations[1]->getName(), $this->response);
		
		// starting an already playing match
		$this->request('POST', 'match/start', array(
			'matchId' => $this->matches[1]->getId(),
		));
		$this->assertEquals('started match '. $this->matches[1]->getLocalId() .' on undefined location', $this->response);
		
		// starting a played match
		$this->request('POST', 'match/start', array(
			'matchId' => $this->matches[2]->getId(),
			'locationId' => $this->locations[2]->getId(),
		));
		$this->assertEquals('started match '. $this->matches[2]->getLocalId() .' on '. $this->locations[2]->getName(), $this->response);
		
		// getting match list and checking status
		$this->request('POST', 'match/liststatus', array(
			'status' => array('playing')
		));
		$this->standardChecks();
		$this->assertEquals(3, sizeof($this->response));
		$this->assertEquals('Playing', $this->response[$this->matches[0]->getId()]['status']);
		$this->assertEquals('Playing', $this->response[$this->matches[1]->getId()]['status']);
		$this->assertEquals('Playing', $this->response[$this->matches[2]->getId()]['status']);
		
		$this->endTest();
	}
	
	public function testStop() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		$this->performScenario4();
		$this->performScenario5();
		
		// stop a match normally
		$this->request('POST', 'match/start', array(
			'matchId' => $this->matches[0]->getId(),
			'locationId' => $this->locations[1]->getId(),
		));
		$this->request('POST', 'match/stop', array(
			'matchId' => $this->matches[0]->getId(),
		));
		$this->assertEquals('stopped match '. $this->matches[0]->getLocalId(), $this->response);
		
		// stopping a non-playing match
		$this->request('POST', 'match/stop', array(
			'matchId' => $this->matches[1]->getId(),
		));
		$this->assertEquals('stopped match '. $this->matches[1]->getLocalId(), $this->response);
		
		// stopping an already played match
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[2]->getId(),
			'status' => 'played',
		));
		$this->request('POST', 'match/stop', array(
			'matchId' => $this->matches[2]->getId(),
		));
		$this->assertEquals('stopped match '. $this->matches[2]->getLocalId(), $this->response);
		
		// getting match list and checking status
		$this->request('POST', 'match/liststatus', array(
			'status' => array('ready')
		));
		$this->standardChecks();
		$this->assertEquals(7, sizeof($this->response));
		$this->assertEquals('Ready', $this->response[$this->matches[0]->getId()]['status']);
		$this->assertEquals('Ready', $this->response[$this->matches[1]->getId()]['status']);
		$this->assertEquals('Ready', $this->response[$this->matches[2]->getId()]['status']);
		
		$this->endTest();
	}
}
