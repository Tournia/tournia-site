<?php

namespace TS\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TS\ApiBundle\Tests\MainTest;

class PlayerControllerTest extends MainTest
{
	
	public function testGet() {
		$this->startTest();
		
		$this->request('GET', 'player/get/'. $this->players[1]->getId());
		$this->standardChecks();
		$this->assertEquals($this->players[1]->getId(), $this->response['playerId']);
		$this->assertEquals($this->players[1]->getName(), $this->response['name']);
		
		$this->endTest();
	}
	
	public function testList() {
		$this->startTest();
		
		$this->request('GET', 'player/list');
		$this->standardChecks();
		$this->assertEquals(10, sizeof($this->response));
		$id2 = $this->players[2]->getId();
		$this->assertEquals($id2, $this->response[$id2]['id']);
		$this->assertEquals($this->players[3]->getName(), $this->response[$this->players[3]->getId()]['name']);
		
		$this->endTest();
	}
	
	public function testMatches() {
		$this->startTest();
		
		$this->request('GET', 'player/matches/'. $this->players[0]->getId());
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		$this->endTest();
	}
	
	public function testSetReady() {
		$this->startTest();
		
		//TODO: changing "true" to boolean true, as well as in tests changing '' to false
		$this->request('POST', 'player/setready', array(
			'playerId' => $this->players[0]->getId(),
			'ready' => "true",
		));
		$this->request('POST', 'player/setready', array(
			'playerId' => $this->players[1]->getId(),
			'ready' => "false",
		));
		$this->request('POST', 'player/setready', array(
			'playerId' => $this->players[2]->getId(),
			'ready' => "toggle",
		));
		
		$this->request('POST', 'player/listStatus');
		$this->standardChecks();
		$this->assertEquals(true, $this->response['players'][$this->players[0]->getId()]['ready']);
		$this->assertEquals('', $this->response['players'][$this->players[1]->getId()]['ready']);
		$this->assertEquals('', $this->response['players'][$this->players[2]->getId()]['ready']);
		$this->assertEquals(true, $this->response['players'][$this->players[3]->getId()]['ready']);
		
		// and toggle true and false again
		$this->request('POST', 'player/setready', array(
			'playerId' => $this->players[0]->getId(),
			'ready' => "toggle",
		));
		$this->request('POST', 'player/setready', array(
			'playerId' => $this->players[1]->getId(),
			'ready' => "toggle",
		));
		$this->request('POST', 'player/listStatus');
		$this->standardChecks();
		$this->assertEquals('', $this->response['players'][$this->players[0]->getId()]['ready']);
		$this->assertEquals(true, $this->response['players'][$this->players[1]->getId()]['ready']);
		$this->assertEquals('', $this->response['players'][$this->players[2]->getId()]['ready']);
		$this->assertEquals(true, $this->response['players'][$this->players[3]->getId()]['ready']);
		
		$this->endTest();
	}
}
