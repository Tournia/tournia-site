<?php

namespace TS\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TS\ApiBundle\Tests\MainTest;

class TeamControllerTest extends MainTest
{
	
	public function testAddPlayer() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		
		// create new teams for single disciplines
		// position=0
		$this->request('POST', 'team/addplayer', array(
			'disciplineId' => $this->disciplines[1]->getId(),
			'playerId' => $this->players[1]->getId(),
			'position' => 0,
		));
		$this->assertEquals("added player ". $this->players[1]->getName() ." to team ". $this->players[1]->getName(), $this->response);
		
		// position=0
		$this->request('POST', 'team/addplayer', array(
			'disciplineId' => $this->disciplines[1]->getId(),
			'playerId' => $this->players[2]->getId(),
			'position' => 0,
		));
		$this->assertEquals("added player ". $this->players[2]->getName() ." to team ". $this->players[2]->getName(), $this->response);
		
		// position=1 for single discipline
		$this->request('POST', 'team/addplayer', array(
			'disciplineId' => $this->disciplines[1]->getId(),
			'playerId' => $this->players[3]->getId(),
			'position' => 1,
		));
		$this->assertErrorResponse("Position 1 is bigger than maximum number of players in team for this discipline");
		
		// check created teams
		$this->request('GET', 'team/list/'. $this->disciplines[1]->getId());
		$this->standardChecks();
		$this->assertEquals(2, sizeof($this->response));
		reset($this->response);
		$firstTeam = current($this->response);
		$secondTeam = next($this->response);
		$this->assertEquals($this->players[1]->getName(), $firstTeam['name']);
		$this->assertEquals($this->players[2]->getName(), $secondTeam['name']);
		
		
		// now create teams for double disciplines
		// position=0
		$this->request('POST', 'team/addplayer', array(
			'disciplineId' => $this->disciplines[4]->getId(),
			'playerId' => $this->players[4]->getId(),
			'position' => 0,
		));
		$this->assertEquals("added player ". $this->players[4]->getName() ." to team ". $this->players[4]->getName(), $this->response);
		
		// position=1
		$this->request('POST', 'team/addplayer', array(
			'disciplineId' => $this->disciplines[4]->getId(),
			'playerId' => $this->players[5]->getId(),
			'position' => 1,
		));
		$this->assertEquals("added player ". $this->players[5]->getName() ." to team ". $this->players[5]->getName(), $this->response);
		
		// check created teams
		$this->request('GET', 'team/list/'. $this->disciplines[4]->getId());
		$this->standardChecks();
		$this->assertEquals(2, sizeof($this->response));
		reset($this->response);
		$firstTeam = current($this->response);
		$secondTeam = next($this->response);
		$this->assertEquals($this->players[4]->getName(), $firstTeam['name']);
		$this->assertEquals($this->players[5]->getName(), $secondTeam['name']);
		
		
		// now adding another player to a team
		$this->request('POST', 'team/addplayer', array(
			'teamId' => $firstTeam['id'],
			'disciplineId' => $this->disciplines[4]->getId(),
			'playerId' => $this->players[8]->getId(),
			'position' => 1,
		));
		$this->assertEquals("added player ". $this->players[8]->getName() ." to team ". $this->players[4]->getName() ." & ". $this->players[8]->getName(), $this->response);
		
		// adding another player on the position that is already taken
		$this->request('POST', 'team/addplayer', array(
			'teamId' => $firstTeam['id'],
			'disciplineId' => $this->disciplines[4]->getId(),
			'playerId' => $this->players[9]->getId(),
			'position' => 0,
		));
		$this->assertEquals("added player ". $this->players[9]->getName() ." to team ". $this->players[9]->getName() ." & ". $this->players[8]->getName(), $this->response);
		
		// adding the same player again to the team
		$this->request('POST', 'team/addplayer', array(
			'teamId' => $firstTeam['id'],
			'disciplineId' => $this->disciplines[4]->getId(),
			'playerId' => $this->players[8]->getId(),
			'position' => 0,
		));
		$this->assertEquals("added player ". $this->players[8]->getName() ." to team ". $this->players[8]->getName() ." & ". $this->players[8]->getName(), $this->response);
		
		// check changed teams
		$this->request('GET', 'team/list/'. $this->disciplines[4]->getId());
		$this->standardChecks();
		$this->assertEquals(2, sizeof($this->response));
		reset($this->response);
		$firstTeam = current($this->response);
		$secondTeam = next($this->response);
		$this->assertEquals($this->players[8]->getName() ." & ". $this->players[8]->getName(), $firstTeam['name']);
		$this->assertEquals($this->players[5]->getName(), $secondTeam['name']);
		
		
		$this->endTest();
	}
	
	public function testGiveUp() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		
		//TODO: changing "true" to boolean true, as well as in tests changing '' to false
		$this->request('POST', 'team/giveup', array(
			'teamId' => $this->teams[0]->getId(),
			'givenUp' => "true",
		));
		$this->request('POST', 'team/giveup', array(
			'teamId' => $this->teams[1]->getId(),
			'givenUp' => "false",
		));
		$this->request('POST', 'team/giveup', array(
			'teamId' => $this->teams[2]->getId(),
			'givenUp' => "toggle",
		));
		
		$this->request('GET', 'discipline/deeplist/'. $this->disciplines[0]->getId());
		$this->standardChecks();
		$teams = $this->response[$this->disciplines[0]->getId()]['teams'];
		$this->assertEquals(true, $teams[$this->teams[0]->getId()]['givenUp']);
		$this->assertEquals('', $teams[$this->teams[1]->getId()]['givenUp']);
		$this->assertEquals(true, $teams[$this->teams[2]->getId()]['givenUp']);
		$this->assertEquals('', $teams[$this->teams[3]->getId()]['givenUp']);
		
		// and toggle true and false again
		$this->request('POST', 'team/giveup', array(
			'teamId' => $this->teams[0]->getId(),
			'givenUp' => "toggle",
		));
		$this->request('POST', 'team/giveup', array(
			'teamId' => $this->teams[1]->getId(),
			'givenUp' => "toggle",
		));
		
		$this->request('GET', 'discipline/deeplist/'. $this->disciplines[0]->getId());
		$this->standardChecks();
		$teams = $this->response[$this->disciplines[0]->getId()]['teams'];
		$this->assertEquals('', $teams[$this->teams[0]->getId()]['givenUp']);
		$this->assertEquals(true, $teams[$this->teams[1]->getId()]['givenUp']);
		$this->assertEquals(true, $teams[$this->teams[2]->getId()]['givenUp']);
		$this->assertEquals('', $teams[$this->teams[3]->getId()]['givenUp']);
		
		$this->endTest();
	}
	
	public function testList() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		
		// empty list
		$this->request('GET', 'team/list/all');
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		$this->performScenario3();
		
		// list all
		$this->request('GET', 'team/list/all');
		$this->standardChecks();
		$this->assertEquals(16, sizeof($this->response));
		$this->assertEquals($this->teams[2]->getId(), $this->response[$this->teams[2]->getId()]['id']);
		$this->assertEquals($this->teams[3]->getName(), $this->response[$this->teams[3]->getId()]['name']);
		$this->assertEquals($this->players[5]->getName(), $this->response[$this->teams[4]->getId()]['name']);
		
		// list only one discipline
		$this->request('GET', 'team/list/'. $this->disciplines[2]->getId());
		$this->standardChecks();
		$this->assertEquals(2, sizeof($this->response));
		$this->assertEquals($this->teams[10]->getId(), $this->response[$this->teams[10]->getId()]['id']);
		$this->assertEquals($this->teams[11]->getName(), $this->response[$this->teams[11]->getId()]['name']);
		$this->assertEquals($this->players[5]->getName() ." & ". $this->players[6]->getName(), $this->response[$this->teams[11]->getId()]['name']);
		
		$this->endTest();
	}
	
	public function testRemove() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		
		// remove with onlyIfEmpty=true
		$this->request('POST', 'team/remove', array(
			'teamId' => $this->teams[2]->getId(),
		));
		$this->assertEquals("Team not empty", $this->response);
		
		// remove player and remove again with onlyIfEmpty=true
		$disciplineName = $this->teams[4]->getDiscipline()->getName();
		$this->request('POST', 'team/removeplayer', array(
			'teamId' => $this->teams[4]->getId(),
			'playerId' => $this->players[5]->getId(),
		));
		$this->request('POST', 'team/remove', array(
			'teamId' => $this->teams[4]->getId(),
		));
		$this->assertEquals("removed empty team from discipline ". $disciplineName, $this->response);
		
		// remove with onlyIfEmpty=false
		$disciplineName = $this->teams[7]->getDiscipline()->getName();
		$teamName = $this->teams[7]->getName();
		$this->request('POST', 'team/remove', array(
			'teamId' => $this->teams[7]->getId(),
			'onlyIfEmpty' => false,
		));
		$this->assertEquals("removed team ". $teamName ." from discipline ". $disciplineName, $this->response);
		
		// check list of teams
		$this->request('GET', 'team/list/all');
		$this->standardChecks();
		$this->assertEquals(14, sizeof($this->response));
		$this->assertEquals($this->teams[2]->getId(), $this->response[$this->teams[2]->getId()]['id']);
		$this->assertFalse(array_key_exists($this->teams[4]->getId(), $this->response));
		$this->assertFalse(array_key_exists($this->teams[7]->getId(), $this->response));
		
		$this->endTest();
	}
	
	public function testRemovePlayer() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		
		// remove only player in team
		$expectedResult = "removed player ". $this->players[3]->getName() ." from team ". $this->teams[2]->getName();
		$this->request('POST', 'team/removeplayer', array(
			'teamId' => $this->teams[2]->getId(),
			'playerId' => $this->players[3]->getId(),
		));
		$this->assertEquals($expectedResult, $this->response);
		
		// remove only player in team
		$expectedResult = "removed player ". $this->players[4]->getName() ." from team ". $this->teams[3]->getName() ." and added as player without a team";
		$this->request('POST', 'team/removeplayer', array(
			'teamId' => $this->teams[3]->getId(),
			'playerId' => $this->players[4]->getId(),
		));
		$this->assertEquals($expectedResult, $this->response);
		
		// set replacement player and remove original player
		$this->request('POST', 'team/setreplacementplayer', array(
			'teamId' => $this->teams[12]->getId(),
			'disciplineId' => $this->disciplines[3]->getId(),
			'playerId' => $this->players[5]->getId(),
			'position' => 1,
			'replacementPlayerId' => $this->players[0]->getId(),
		));
		$expectedResult = "removed player ". $this->players[5]->getName() ." from team ". $this->players[4]->getName() ." & ". $this->players[0]->getName();
		$this->request('POST', 'team/removeplayer', array(
			'teamId' => $this->teams[12]->getId(),
			'playerId' => $this->players[5]->getId(),
		));
		$this->assertEquals($expectedResult, $this->response);
		
		// set replacement player and remove replacement player
		$this->request('POST', 'team/setreplacementplayer', array(
			'teamId' => $this->teams[13]->getId(),
			'disciplineId' => $this->disciplines[3]->getId(),
			'playerId' => $this->players[6]->getId(),
			'position' => 0,
			'replacementPlayerId' => $this->players[1]->getId(),
		));
		$this->request('POST', 'team/removeplayer', array(
			'teamId' => $this->teams[13]->getId(),
			'playerId' => $this->players[1]->getId(),
		));
		$this->assertEquals("Player ". $this->players[1]->getName() ." not found in team ". $this->players[1]->getName() ." & ". $this->players[7]->getName(), $this->response);
		
		// remove player from double team
		$expectedResult = "removed player ". $this->players[6]->getName() ." from team ". $this->teams[14]->getName();
		$this->request('POST', 'team/removeplayer', array(
			'teamId' => $this->teams[14]->getId(),
			'playerId' => $this->players[6]->getId(),
		));
		$this->assertEquals($expectedResult, $this->response);
		
		// check list of teams
		$this->request('GET', 'team/list/all');
		$this->standardChecks();
		$this->assertEquals(16, sizeof($this->response));
		$this->assertEquals('-', $this->response[$this->teams[2]->getId()]['name']);
		
		$this->request('GET', 'discipline/deeplist/all');
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response[$this->disciplines[0]->getId()]['teams'][$this->teams[2]->getId()]['players']));
		$this->assertEquals(null, $this->response[$this->disciplines[0]->getId()]['teams'][$this->teams[2]->getId()]['players'][0]);

		$this->assertEquals(1, sizeof($this->response[$this->disciplines[0]->getId()]['teams'][$this->teams[3]->getId()]['players']));
		$this->assertEquals(null, $this->response[$this->disciplines[0]->getId()]['teams'][$this->teams[3]->getId()]['players'][0]);

		$discipline3 = $this->response[$this->disciplines[3]->getId()];
		$this->assertEquals(2, sizeof($discipline3['teams'][$this->teams[12]->getId()]['players']));
		$this->assertEquals($this->players[4]->getId(), $discipline3['teams'][$this->teams[12]->getId()]['players'][0]['id']);
		$this->assertEquals(null, $discipline3['teams'][$this->teams[12]->getId()]['players'][1]);
		$this->assertEquals(2, sizeof($discipline3['teams'][$this->teams[13]->getId()]['players']));
		$this->assertEquals($this->players[6]->getId(), $discipline3['teams'][$this->teams[13]->getId()]['players'][0]['id']);
		$this->assertEquals($this->players[7]->getId(), $discipline3['teams'][$this->teams[13]->getId()]['players'][1]['id']);
		
		$this->endTest();
	}
	
	public function testSetReplacementPlayer() {
		$this->startTest();
		$this->performScenario1();
		$this->performScenario2();
		$this->performScenario3();
		
		$this->request('GET', 'discipline/deeplist/all');
		
		// setting replacement player for single team
		$this->request('POST', 'team/setreplacementplayer', array(
			'teamId' => $this->teams[1]->getId(),
			'disciplineId' => $this->disciplines[0]->getId(),
			'playerId' => $this->players[2]->getId(),
			'position' => 0,
			'replacementPlayerId' => $this->players[5]->getId(),
		));
		$this->assertEquals("set replacement player ". $this->players[5]->getName() ." for ". $this->players[2]->getName(), $this->response);
		
		// setting replacement player for doubles team
		$this->request('POST', 'team/setreplacementplayer', array(
			'teamId' => $this->teams[12]->getId(),
			'disciplineId' => $this->disciplines[3]->getId(),
			'playerId' => $this->players[4]->getId(),
			'position' => 0,
			'replacementPlayerId' => $this->players[8]->getId(),
		));
		$this->assertEquals("set replacement player ". $this->players[8]->getName() ." for ". $this->players[4]->getName(), $this->response);
		
		// set and remove replacement player
		$this->request('POST', 'team/setreplacementplayer', array(
			'teamId' => $this->teams[13]->getId(),
			'disciplineId' => $this->disciplines[3]->getId(),
			'playerId' => $this->players[7]->getId(),
			'position' => 1,
			'replacementPlayerId' => $this->players[9]->getId(),
		));
		$this->assertEquals("set replacement player ". $this->players[9]->getName() ." for ". $this->players[7]->getName(), $this->response);
		$this->request('POST', 'team/setreplacementplayer', array(
			'teamId' => $this->teams[13]->getId(),
			'disciplineId' => $this->disciplines[3]->getId(),
			'playerId' => $this->players[7]->getId(),
			'position' => 1,
			'replacementPlayerId' => 0,
		));
		$this->assertEquals("removed replacement player for ". $this->players[7]->getName(), $this->response);
		
		$this->request('GET', 'discipline/deeplist/all');
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response[$this->disciplines[0]->getId()]['teams'][$this->teams[1]->getId()]['players']));
		$player = $this->response[$this->disciplines[0]->getId()]['teams'][$this->teams[1]->getId()]['players'][0];
		$this->assertEquals($this->players[2]->getId(), $player['id']); // should have original id
		$this->assertEquals($this->players[2]->getName(), $player['name']); // should have original name
		$this->assertEquals(true, $player['hasReplacementPlayer']);
		$this->assertEquals($this->players[5]->getId(), $player['replacementPlayerId']);
		$this->assertEquals($this->players[5]->getName(), $player['replacementPlayerName']);
		
		$player = $this->response[$this->disciplines[3]->getId()]['teams'][$this->teams[12]->getId()]['players'][0];
		$this->assertEquals($this->players[4]->getId(), $player['id']);
		$this->assertEquals($this->players[8]->getId(), $player['replacementPlayerId']);
		
		$player = $this->response[$this->disciplines[3]->getId()]['teams'][$this->teams[13]->getId()]['players'][1];
		$this->assertEquals($this->players[7]->getId(), $player['id']);
		$this->assertEquals(false, $player['hasReplacementPlayer']);
		$this->assertFalse(array_key_exists('replacementPlayerId', $player));
		
		$this->endTest();
	}

}
