<?php

namespace TS\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TS\ApiBundle\Tests\MainTest;

class LocationControllerTest extends MainTest
{
	
	public function testList() {
		$this->startTest();
		
		$this->request('GET', 'location/list');
		$this->standardChecks();
		$this->assertEquals(0, sizeof($this->response));
		
		// create new location
		$this->request('POST', 'location/new', array(
			'name' => 'Test location 1'
		));
		$this->assertEquals("created a new location Test location 1", $this->response);
		$this->request('GET', 'location/list');
		$this->standardChecks();
		$this->assertEquals(1, sizeof($this->response));
		$row = current($this->response);
		$this->assertEquals("Test location 1", $row['name']);
		$this->assertFalse($row['onHold']);
		
		$this->endTest();
	}
	
	public function testNew() {
		$this->startTest();
		
		// create new location
		$this->request('POST', 'location/new', array(
			'name' => 'Test location 1'
		));
		$this->assertEquals("created a new location Test location 1", $this->response);
		
		// create a new location with the same name
		$this->request('POST', 'location/new', array(
			'name' => 'Test location 1'
		));
		$this->assertEquals("created a new location Test location 1", $this->response);
		
		// create a new location with an empty name
		$this->request('POST', 'location/new', array(
			'name' => ''
		));
		$this->assertEquals("created a new location ", $this->response);
		
		$this->endTest();
	}
	
	public function testEdit() {
		$this->startTest();
		$this->performScenario4();
		
		// edit location
		$this->request('POST', 'location/edit', array(
			'locationId' => $this->locations[2]->getId(),
			'name' => 'Abc Def gh'
		));
		$this->assertEquals("changed location name to Abc Def gh", $this->response);
		
		// check new name of location
		$this->request('GET', 'location/list');
		$this->standardChecks();
		$this->assertEquals($this->locations[2]->getId(), $this->response[2]['id']);
		$this->assertEquals("Abc Def gh", $this->response[2]['name']);
		
		$this->endTest();
	}
	
	public function testRemove() {
		$this->startTest();
		$this->performScenario4();
		
		// remove location
		$this->request('POST', 'location/remove', array(
			'locationId' => $this->locations[2]->getId(),
		));
		$this->assertEquals("removed location ". $this->locations[2]->getName(), $this->response);
		
		// remove location again
		$this->request('POST', 'location/remove', array(
			'locationId' => $this->locations[2]->getId(),
		));
		$this->assertErrorResponse("No location found for id ". $this->locations[2]->getId());
		
		$this->endTest();
	}
	
	public function testSetOnHold() {
		$this->startTest();
		$this->performScenario4();
		
		// set location on hold
		$this->request('POST', 'location/setonhold', array(
			'locationId' => $this->locations[0]->getId(),
			'onHold' => true,
		));
		$this->request('POST', 'location/setonhold', array(
			'locationId' => $this->locations[1]->getId(),
			'onHold' => false,
		));
		$this->request('POST', 'location/setonhold', array(
			'locationId' => $this->locations[2]->getId(),
			'onHold' => "toggle",
		));
		
		$this->request('GET', 'location/list');
		$this->standardChecks();
		$this->assertEquals(true, $this->response[0]['onHold']);
		$this->assertEquals(false, $this->response[1]['onHold']);
		$this->assertEquals(true, $this->response[2]['onHold']);
		$this->assertEquals(false, $this->response[3]['onHold']);
		
		// and toggle true and false again
		$this->request('POST', 'location/setonhold', array(
			'locationId' => $this->locations[0]->getId(),
			'onHold' => "toggle",
		));
		$this->request('POST', 'location/setonhold', array(
			'locationId' => $this->locations[1]->getId(),
			'onHold' => "toggle",
		));
		$this->request('GET', 'location/list');
		$this->standardChecks();
		$this->assertEquals(false, $this->response[0]['onHold']);
		$this->assertEquals(true, $this->response[1]['onHold']);
		$this->assertEquals(true, $this->response[2]['onHold']);
		$this->assertEquals(false, $this->response[3]['onHold']);
		
		$this->endTest();
	}
}
