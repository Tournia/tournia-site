<?php

namespace TS\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TS\ApiBundle\Tests\MainTest;

class DisciplineControllerTest extends MainTest
{
	
	public function testAddPlayer() {
		$this->startTest();
		$this->performScenario1();
		
		// check if player isn't in discipline yet
		$this->request('GET', 'discipline/deeplist/'. $this->disciplines[2]->getId());

		// adding a player
		$this->request('POST', 'discipline/addplayer', array(
			'disciplineId' => $this->disciplines[2]->getId(),
			'playerId' => $this->players[2]->getId()
		));
		$this->assertEquals("added player Test 2 to discipline Test Men Doubles", $this->response);
		
		// check if player is now in discipline
		$this->request('GET', 'discipline/deeplist/'. $this->disciplines[2]->getId());

		// and trying to adding it again
		$this->request('POST', 'discipline/addplayer', array(
			'disciplineId' => $this->disciplines[2]->getId(),
			'playerId' => $this->players[2]->getId()
		));
		$this->assertErrorResponse("Player Test 2 is already in players without a team of discipline Test Men Doubles");
		
		// check if player is still in discipline
		$this->request('GET', 'discipline/deeplist/'. $this->disciplines[2]->getId());

		$this->endTest();
	}
	
	public function testAutoAssign() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		
		// autoassign to make teams
		$this->request('POST', 'discipline/autoassign');
		$this->assertEquals("automatically added total of 22 players to teams", $this->response);
		
		// check newly created teams
		$this->request('GET', 'discipline/deeplist/all');
		$this->standardChecks();
		$this->assertEquals(5, sizeof($this->response[$this->disciplines[0]->getId()]['teams']));
		$this->assertEquals(2, sizeof($this->response[$this->disciplines[3]->getId()]['teams']));
		
		// check discipline 1
		$teams = $this->response[$this->disciplines[1]->getId()]['teams'];
		reset($teams);
		$teamData = current($teams);
		$this->assertEquals("Test 2", $teamData['name']);
		$this->assertFalse($teamData['givenUp']);
		$this->assertEquals($this->players[2]->getId(), $teamData['players'][0]['id']);
		$this->assertEquals($this->players[2]->getName(), $teamData['players'][0]['name']);
		$this->assertEquals($this->players[2]->getRegistrationGroup()->getName(), $teamData['players'][0]['registrationGroup']);
		$this->assertEquals($this->players[2]->getGender(), $teamData['players'][0]['gender']);
		$this->assertFalse($teamData['players'][0]['hasReplacementPlayer']);
		
		// check third team in discipline 1
		next($teams);
		next($teams);
		$teamData = current($teams);
		$this->assertEquals("Test 4", $teamData['name']);
		$this->assertEquals($this->players[4]->getId(), $teamData['players'][0]['id']);
		
		// discipline 4
		$teams = $this->response[$this->disciplines[4]->getId()]['teams'];
		reset($teams);
		next($teams);
		$teamData = current($teams);
		$this->assertEquals("Test 7 & Test 8", $teamData['name']);
		$this->assertEquals($this->players[8]->getId(), $teamData['players'][0]['id']);
		$this->assertEquals($this->players[7]->getId(), $teamData['players'][1]['id']);
		
		$this->endTest();
	}
	
	public function testCheckFinishedPlaying() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		
		// start with no matches
		$this->request('GET', 'discipline/checkfinishedplaying/'. $this->disciplines[2]->getId());
		$this->standardChecks();
		$this->assertEquals("ok", $this->response);
		
		$this->request('GET', 'discipline/checkfinishedplaying/all');
		$this->standardChecks();
		$this->assertEquals("ok", $this->response);
		
		// create matches
		$this->performScenario5();
		$this->request('GET', 'discipline/checkfinishedplaying/all');
		$this->standardChecks();
		$this->assertEquals("warning", $this->response);
		
		$this->request('GET', 'discipline/checkfinishedplaying/'. $this->disciplines[1]->getId());
		$this->standardChecks();
		$this->assertEquals("warning", $this->response);
		
		// set status of matches in discipline 0 to finish & played
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[0]->getId(),
			'status' => 'finished'
		));
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[1]->getId(),
			'status' => 'played'
		));
		// set status of matches in discipline 1 to played
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[2]->getId(),
			'status' => 'played'
		));
		$this->request('POST', 'match/setstatus', array(
			'matchId' => $this->matches[3]->getId(),
			'status' => 'played'
		));
		
		// checking function
		$this->request('GET', 'discipline/checkfinishedplaying/all');
		$this->standardChecks();
		$this->assertEquals("warning", $this->response);
		
		$this->request('GET', 'discipline/checkfinishedplaying/'. $this->disciplines[0]->getId());
		$this->standardChecks();
		$this->assertEquals("warning", $this->response);
		
		$this->request('GET', 'discipline/checkfinishedplaying/'. $this->disciplines[1]->getId());
		$this->standardChecks();
		$this->assertEquals("ok", $this->response);
		
		$this->endTest();
	}
	
	public function testDeepList() {
		$this->startTest();
		
		// start with empty list
		$this->request('GET', 'discipline/deeplist/all');
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		// test with singles
		$this->performScenario1();
		$this->request('GET', 'discipline/deeplist/'. $this->disciplines[0]->getId());
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response));
		$row = $this->response[$this->disciplines[0]->getId()];
		$this->assertEquals("Test Men Singles", $row['name']);
		$this->assertEquals(0, sizeof($row['teams']));

		// test with doubles and teams
		$this->performScenario2();
		$this->performScenario3();
		
		// test null in players list when removing player from team
		$this->request('POST', 'team/removeplayer', array(
			'teamId' => $this->teams[13]->getId(),
			'playerId' => $this->players[7]->getId(),
		));
		
		// getting list and checking it
		// testing discipline 3 (= ladies doubles), because 2 and 4 will switch players in teams, since female gets index 1 with autoassign
		$this->request('GET', 'discipline/deeplist/'. $this->disciplines[3]->getId());
		$this->standardChecks();
		$row = $this->response[$this->disciplines[3]->getId()];
		$this->assertEquals($this->disciplines[3]->getId(), $row['id']);
		$this->assertEquals($this->disciplines[3]->getName(), $row['name']);
		$this->assertEquals(2, sizeof($row['teams']));
		$team1 = $row['teams'][$this->teams[12]->getId()];
		$this->assertEquals($this->teams[12]->getId(), $team1['id']);
		$this->assertEquals($this->teams[12]->getName(), $team1['name']);
		$this->assertEquals(false, $team1['givenUp']);
		$this->assertEquals(2, sizeof($team1['players']));
		$this->assertEquals($this->players[4]->getId(), $team1['players'][0]['id']);
		$this->assertEquals($this->players[5]->getId(), $team1['players'][1]['id']);
		
		$team2 = $row['teams'][$this->teams[13]->getId()];
		$this->assertEquals($this->teams[13]->getId(), $team2['id']);
		$this->assertEquals(2, sizeof($team2['players']));
		$this->assertEquals($this->players[6]->getId(), $team2['players'][0]['id']);
		$this->assertEquals(null, $team2['players'][1]);
		
		$this->endTest();
	}
	
	public function testImportRegistrations() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		
		// start with some players in the disciplines
		$this->request('GET', 'discipline/deeplist/all');
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response[$this->disciplines[0]->getId()]['teams']));
		
		// import registrations
		$this->request('POST', 'discipline/importregistrations', array(
			'status' => $this->tournament->getNewPlayerStatus(),
		));
		$this->assertEquals("added total of 5 players to disciplines", $this->response);
		
		// now check with imported registrations
		$this->request('GET', 'discipline/deeplist/all');
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response[$this->disciplines[0]->getId()]['teams']));
		
		$this->endTest();
	}

	public function testList() {
		$this->startTest();
		
		// start with empty list
		$this->request('GET', 'discipline/list');
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		// creating disciplines
		$this->performScenario1();
		$this->request('GET', 'discipline/list');
		$this->standardChecks();
		$this->assertEquals(5, sizeof($this->response));
		$this->assertEquals($this->disciplines[2]->getId(), $this->response[2]['id']);
		$this->assertEquals($this->disciplines[3]->getName(), $this->response[3]['name']);
		
		$this->endTest();
	}
	
	public function testRemovePlayer() {
		$this->startTest();
		$this->performScenario1();
		
		// adding a player
		$this->request('POST', 'discipline/addplayer', array(
			'disciplineId' => $this->disciplines[2]->getId(),
			'playerId' => $this->players[2]->getId()
		));
		$this->assertEquals("added player Test 2 to discipline Test Men Doubles", $this->response);
		
		// and trying to adding it again
		$this->request('POST', 'discipline/addplayer', array(
			'disciplineId' => $this->disciplines[2]->getId(),
			'playerId' => $this->players[2]->getId()
		));
		$this->assertErrorResponse("Player Test 2 is already in players without a team of discipline Test Men Doubles");
		
		$this->endTest();
	}
}
