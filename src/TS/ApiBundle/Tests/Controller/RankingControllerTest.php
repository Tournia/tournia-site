<?php

namespace TS\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TS\ApiBundle\Tests\MainTest;

class RankingControllerTest extends MainTest
{
	private function initializeTest() {
		$this->startTest();
		
		// combining registration groups to three groups
		for ($i = 0; $i < sizeof($this->players); $i++) {
			$nr = $i % 3;
			$registrationGroup = $this->players[($i % 3)]->getRegistrationGroup();
			$this->players[$i]->setRegistrationGroup($registrationGroup);
			$this->em->persist($this->players[$i]);
		}
		$this->em->flush();
		
		$this->performScenario1();
		
		// making a doubles discipline a bit bigger, to make testing easier
		// prevent random bye's by having 8 players, so 4 teams.
		$commandArray = array();
		for ($i = 0; $i < 8; $i++) {
			$commandArray[] = array(
				'command' => 'Disciplines.addPlayer',
				'disciplineId' => $this->disciplines[2]->getId(),
				'playerId' => $this->players[$i]->getId(),
			);
		}
		$this->request('POST', 'command', array('commands' => $commandArray));
		
		$this->performScenario3();
		$this->performScenario4();
	}
	
	private function scoreMatches() {
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
	}
	
	public function testDiscipline() {
		$this->initializeTest();
		
		// test empty catgory
		$this->request('GET', 'ranking/discipline/'. $this->disciplines[0]->getId());
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		// test discipline without matches
		$this->request('GET', 'ranking/discipline/'. $this->disciplines[2]->getId());
		$this->standardChecks();
		$this->assertEquals(4, sizeof($this->response));
		$i = 0;
		foreach($this->response as $key=>$row) {
			$this->assertEquals($i, $key);
			$this->assertEquals(($i+1), $row['rank']);
			$i++;
			$this->assertEquals(0, $row['matchesWon']);
			$this->assertEquals(0, $row['matchesDraw']);
			$this->assertEquals(0, $row['matchesLost']);
			$this->assertEquals(0, $row['setsWon']);
			$this->assertEquals(0, $row['setsLost']);
			$this->assertEquals(0, $row['pointsWon']);
			$this->assertEquals(0, $row['pointsLost']);
			$this->assertEquals(0, $row['matchesPlayed']);
			$this->assertEquals(0.0, $row['matchesRelative']);
			$this->assertEquals(0.5, $row['setsRelative']);
			$this->assertEquals(0.5, $row['pointsRelative']);
		}
		
		// creating first round matches
		$this->performScenario5();
		$this->request('GET', 'ranking/discipline/'. $this->disciplines[2]->getId());
		$this->standardChecks();
		$this->assertEquals(4, sizeof($this->response));
		$i = 0;
		foreach($this->response as $key=>$row) {
			$this->assertEquals($i, $key);
			$this->assertEquals(($i+1), $row['rank']);
			$i++;
			$this->assertEquals(0, $row['matchesWon']);
			$this->assertEquals(0, $row['matchesDraw']);
			$this->assertEquals(0, $row['matchesLost']);
			$this->assertEquals(0, $row['setsWon']);
			$this->assertEquals(0, $row['setsLost']);
			$this->assertEquals(0, $row['pointsWon']);
			$this->assertEquals(0, $row['pointsLost']);
			$this->assertEquals(0, $row['matchesPlayed']);
			$this->assertEquals(0.0, $row['matchesRelative']);
			$this->assertEquals(0.5, $row['setsRelative']);
			$this->assertEquals(0.5, $row['pointsRelative']);
		}
		
		// score matches
		$this->scoreMatches();
		$this->request('GET', 'ranking/discipline/'. $this->disciplines[2]->getId());
		$this->standardChecks();
		
		$this->assertEquals(1, $this->response[0]['rank']);
		$this->assertEquals(2, $this->response[1]['rank']);
		$this->assertEquals(3, $this->response[2]['rank']);
		$this->assertEquals(4, $this->response[3]['rank']);
		
		$this->assertEquals($this->teams[0]->getId(), $this->response[0]['teamId']);
		$this->assertEquals($this->teams[2]->getId(), $this->response[1]['teamId']);
		$this->assertEquals($this->teams[3]->getId(), $this->response[2]['teamId']);
		$this->assertEquals($this->teams[1]->getId(), $this->response[3]['teamId']);
		
		$players = $this->teams[0]->getPlayersForAllPositions();
		$players0Array = array($players[0]->getId() => $players[0]->getName(), $players[1]->getId() => $players[1]->getName());
		$players = $this->teams[1]->getPlayersForAllPositions();
		$players1Array = array($players[0]->getId() => $players[0]->getName(), $players[1]->getId() => $players[1]->getName());
		$players = $this->teams[2]->getPlayersForAllPositions();
		$players2Array = array($players[0]->getId() => $players[0]->getName(), $players[1]->getId() => $players[1]->getName());
		$players = $this->teams[3]->getPlayersForAllPositions();
		$players3Array = array($players[0]->getId() => $players[0]->getName(), $players[1]->getId() => $players[1]->getName());
		$this->assertEquals($players0Array, $this->response[0]['players']);
		$this->assertEquals($players2Array, $this->response[1]['players']);
		$this->assertEquals($players3Array, $this->response[2]['players']);
		$this->assertEquals($players1Array, $this->response[3]['players']);
		
		$this->assertEquals(1, $this->response[0]['matchesPlayed']);
		$this->assertEquals(1, $this->response[1]['matchesPlayed']);
		$this->assertEquals(1, $this->response[2]['matchesPlayed']);
		$this->assertEquals(1, $this->response[3]['matchesPlayed']);
		
		$this->assertEquals(1, $this->response[0]['matchesWon']);
		$this->assertEquals(0, $this->response[1]['matchesWon']);
		$this->assertEquals(0, $this->response[2]['matchesWon']);
		$this->assertEquals(0, $this->response[3]['matchesWon']);
		
		$this->assertEquals(0, $this->response[0]['matchesDraw']);
		$this->assertEquals(1, $this->response[1]['matchesDraw']);
		$this->assertEquals(1, $this->response[2]['matchesDraw']);
		$this->assertEquals(0, $this->response[3]['matchesDraw']);
		
		$this->assertEquals(0, $this->response[0]['matchesLost']);
		$this->assertEquals(0, $this->response[1]['matchesLost']);
		$this->assertEquals(0, $this->response[2]['matchesLost']);
		$this->assertEquals(1, $this->response[3]['matchesLost']);
		
		$this->assertEquals('1.000', $this->response[0]['matchesRelative']);
		$this->assertEquals('0.000', $this->response[1]['matchesRelative']);
		$this->assertEquals('0.000', $this->response[2]['matchesRelative']);
		$this->assertEquals('-1.000', $this->response[3]['matchesRelative']);
		
		$this->assertEquals(2, $this->response[0]['setsWon']);
		$this->assertEquals(1, $this->response[1]['setsWon']);
		$this->assertEquals(1, $this->response[2]['setsWon']);
		$this->assertEquals(0, $this->response[3]['setsWon']);
		
		$this->assertEquals(0, $this->response[0]['setsLost']);
		$this->assertEquals(1, $this->response[1]['setsLost']);
		$this->assertEquals(1, $this->response[2]['setsLost']);
		$this->assertEquals(2, $this->response[3]['setsLost']);
		
		$this->assertEquals('1.000', $this->response[0]['setsRelative']);
		$this->assertEquals('0.500', $this->response[1]['setsRelative']);
		$this->assertEquals('0.500', $this->response[2]['setsRelative']);
		$this->assertEquals('0.000', $this->response[3]['setsRelative']);
		
		$this->assertEquals(40, $this->response[0]['pointsWon']);
		$this->assertEquals(35, $this->response[1]['pointsWon']);
		$this->assertEquals(30, $this->response[2]['pointsWon']);
		$this->assertEquals(3, $this->response[3]['pointsWon']);
		
		$this->assertEquals(3, $this->response[0]['pointsLost']);
		$this->assertEquals(30, $this->response[1]['pointsLost']);
		$this->assertEquals(35, $this->response[2]['pointsLost']);
		$this->assertEquals(40, $this->response[3]['pointsLost']);
		
		$this->assertEquals('0.930', $this->response[0]['pointsRelative']);
		$this->assertEquals('0.538', $this->response[1]['pointsRelative']);
		$this->assertEquals('0.462', $this->response[2]['pointsRelative']);
		$this->assertEquals('0.070', $this->response[3]['pointsRelative']);

		$this->endTest();
	}
	
	public function testGroups() {
		$this->initializeTest();
		
		// test groups without matches
		$this->request('GET', 'ranking/groups');
		$this->checkDefaultGroups();
		
		// creating first round matches
		$this->performScenario5();
		$this->request('GET', 'ranking/groups');
		$this->checkDefaultGroups();
		
		// score matches
		$this->scoreMatches();
		$this->request('GET', 'ranking/groups');
		$this->standardChecks();
		
		$group0 = $this->players[0]->getRegistrationGroup();
		$group1 = $this->players[1]->getRegistrationGroup();
		$group2 = $this->players[2]->getRegistrationGroup();
		
		$this->assertEquals(1, $this->response[1]['rank']);
		$this->assertEquals(2, $this->response[2]['rank']);
		$this->assertEquals(3, $this->response[3]['rank']);
		
		/*
		Expected result:
		Array ( 
			[1] => Array ( [rank] => 1 [groupId] => 268 [groupName] => Team 1 [sumPoints] => 105 [nrSets] => 6 [relative] => 17.500 [nrPlayers] => 3 ) 
			[2] => Array ( [rank] => 2 [groupId] => 266 [groupName] => Team 0 [sumPoints] => 73 [nrSets] => 6 [relative] => 12.167 [nrPlayers] => 3 ) 
			[3] => Array ( [rank] => 3 [groupId] => 269 [groupName] => Team 2 [sumPoints] => 38 [nrSets] => 4 [relative] => 9.500 [nrPlayers] => 2 ) 
		) 
		*/
		$this->assertEquals($group1->getId(), $this->response[1]['groupId']);
		$this->assertEquals($group0->getId(), $this->response[2]['groupId']);
		$this->assertEquals($group2->getId(), $this->response[3]['groupId']);
		
		$this->assertEquals($group1->getName(), $this->response[1]['groupName']);
		$this->assertEquals($group0->getName(), $this->response[2]['groupName']);
		$this->assertEquals($group2->getName(), $this->response[3]['groupName']);
		
		$this->assertEquals(105, $this->response[1]['sumPoints']);
		$this->assertEquals(73, $this->response[2]['sumPoints']);
		$this->assertEquals(38, $this->response[3]['sumPoints']);
		
		$this->assertEquals(6, $this->response[1]['nrSets']);
		$this->assertEquals(6, $this->response[2]['nrSets']);
		$this->assertEquals(4, $this->response[3]['nrSets']);
		
		$this->assertEquals('17.500', $this->response[1]['relative']);
		$this->assertEquals('12.167', $this->response[2]['relative']);
		$this->assertEquals('9.500', $this->response[3]['relative']);
		
		$this->assertEquals(3, $this->response[1]['nrPlayers']);
		$this->assertEquals(3, $this->response[2]['nrPlayers']);
		$this->assertEquals(2, $this->response[3]['nrPlayers']);
		
		$this->endTest();
	}
	
	private function checkDefaultGroups() {
		$this->standardChecks();
		$this->assertEquals(3, sizeof($this->response));
		$i = 1;
		foreach($this->response as $key=>$row) {
			$this->assertEquals($i, $key);
			$this->assertEquals($i, $row['rank']);
			
			$group = $this->players[(3-$i)]->getRegistrationGroup();
			$i++;
			
			$this->assertEquals($group->getId(), $row['groupId']);
			$this->assertEquals($group->getName(), $row['groupName']);
			$this->assertEquals(0, $row['sumPoints']);
			$this->assertEquals(0, $row['nrSets']);
			$this->assertEquals('0.000', $row['relative']);
			
			if ($i == 2) {
				$this->assertEquals(2, $row['nrPlayers']);
			} else {
				$this->assertEquals(3, $row['nrPlayers']);
			}
		}
	}
	
	public function testPlayers() {
		$this->initializeTest();
		
		// test players without matches
		$this->request('GET', 'ranking/players');
		$this->checkDefaultPlayers();
		
		// creating first round matches
		$this->performScenario5();
		$this->request('GET', 'ranking/players');
		$this->checkDefaultPlayers();
		
		// score matches
		$this->scoreMatches();
		$this->request('GET', 'ranking/players');
		$this->standardChecks();
		
		$rankingArray = array(0, 1, 4, 5, 7, 6, 3, 2);
		$i = 1;
		foreach ($rankingArray as $rankPlayer) {
			$this->assertEquals($this->players[$rankPlayer]->getId(), $this->response[$i]['playerId']);
			$this->assertEquals($this->players[$rankPlayer]->getName(), $this->response[$i]['name']);
			$this->assertEquals($this->players[$rankPlayer]->getRegistrationGroup()->getId(), $this->response[$i]['groupId']);
			$this->assertEquals($this->players[$rankPlayer]->getRegistrationGroup()->getName(), $this->response[$i]['groupName']);
			$this->assertEquals($this->players[$rankPlayer]->getGender(), $this->response[$i]['gender']);
			
			if (($rankPlayer == 0) || ($rankPlayer == 1)) {
				$this->assertEquals(40, $this->response[$i]['sumPoints']);
				$this->assertEquals(2, $this->response[$i]['nrSets']);
				$this->assertEquals('20.000', $this->response[$i]['relative']);
			} else if (($rankPlayer == 4) || ($rankPlayer == 5)) {
				$this->assertEquals(35, $this->response[$i]['sumPoints']);
				$this->assertEquals(2, $this->response[$i]['nrSets']);
				$this->assertEquals('17.500', $this->response[$i]['relative']);
			} else if (($rankPlayer == 7) || ($rankPlayer == 6)) {
				$this->assertEquals(30, $this->response[$i]['sumPoints']);
				$this->assertEquals(2, $this->response[$i]['nrSets']);
				$this->assertEquals('15.000', $this->response[$i]['relative']);
			} else if (($rankPlayer == 3) || ($rankPlayer == 2)) {
				$this->assertEquals(3, $this->response[$i]['sumPoints']);
				$this->assertEquals(2, $this->response[$i]['nrSets']);
				$this->assertEquals('1.500', $this->response[$i]['relative']);
			}
			$i++;
		}
				
		$this->endTest();
	}
	
	private function checkDefaultPlayers() {
		$this->standardChecks();
		$this->assertEquals(8, sizeof($this->response));
		
		$availablePlayers = array();
		foreach ($this->players as $player) {
			$availablePlayers[$player->getId()] = $player;
		}
		
		$i = 1;
		foreach($this->response as $key=>$row) {
			$this->assertEquals($i, $key);
			$this->assertEquals($i, $row['rank']);
			$i++;
			
			$this->assertTrue(array_key_exists($row['playerId'], $availablePlayers));
			$player = $availablePlayers[$row['playerId']];
			
			$this->assertEquals($player->getId(), $row['playerId']);
			$this->assertEquals($player->getName(), $row['name']);
			$this->assertEquals(0, $row['sumPoints']);
			$this->assertEquals(0, $row['nrSets']);
			$this->assertEquals(0, $row['relative']);
			$this->assertEquals($player->getRegistrationGroup()->getId(), $row['groupId']);
			$this->assertEquals($player->getRegistrationGroup()->getName(), $row['groupName']);
			$this->assertEquals($player->getGender(), $row['gender']);
			
			unset($availablePlayers[$row['playerId']]);
		}
	}
}
