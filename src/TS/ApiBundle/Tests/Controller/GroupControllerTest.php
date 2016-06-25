<?php

namespace TS\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TS\ApiBundle\Tests\MainTest;

class GroupControllerTest extends MainTest
{
	
	public function testList() {
		$this->startTest();
		
		$this->request('GET', 'group/list');
		$this->standardChecks();
		$this->assertEquals(10, sizeof($this->response));
		
		// create new group
		$this->request('POST', 'group/new', array(
			'name' => 'Test group A',
			'country' => 'Test country A'
		));
		$this->assertEquals("created a new group Test group A", $this->response['message']);
		$newGroupId = $this->response['groupId'];
		$this->request('GET', 'group/list');
		$this->standardChecks();
		$this->assertEquals(11, sizeof($this->response));
		$row = end($this->response);
		$this->assertEquals("Test group A", $row['name']);
		$this->assertEquals("Test country A", $row['country']);
		$this->assertTrue($row['groupId'] == $newGroupId);
		
		$this->endTest();
	}
	
	public function testNew() {
		$this->startTest();
		
		// create new group
		$this->request('POST', 'group/new', array(
			'name' => 'Test group B',
			'country' => 'Test country B'
		));
		$this->assertEquals("created a new group Test group B", $this->response['message']);
		$newGroupId = $this->response['groupId'];
		$this->assertTrue(!empty($newGroupId));
		
		// create a new group with the same name
		$this->request('POST', 'group/new', array(
			'name' => 'Test group B',
			'country' => 'Test country B'
		));
		$this->assertEquals("created a new group Test group B", $this->response['message']);
		$this->assertTrue($newGroupId != $this->response['groupId']);
		
		// create a new group with an empty name
		$this->request('POST', 'group/new', array(
			'name' => '',
			'country' => ''
		));
		$this->assertEquals("created a new group ", $this->response['message']);
		
		$this->endTest();
	}
	
	public function testEdit() {
		$this->startTest();
		
		// edit group
		$groupId = $this->players[2]->getRegistrationGroup()->getId();
		$this->request('POST', 'group/edit', array(
			'groupId' => $groupId,
			'name' => "Abc D'ef gh",
			'country' => 'ijK lmn'
		));
		$this->assertEquals("changed group name to Abc D'ef gh and country ijK lmn", $this->response);
		
		// check new name of group
		$this->request('GET', 'group/list');
		$this->standardChecks();
		$this->assertEquals("Abc D'ef gh", $this->response[$groupId]['name']);
		$this->assertEquals("ijK lmn", $this->response[$groupId]['country']);
		
		$this->endTest();
	}
	
	public function testRemove() {
		$this->startTest();
		
		// remove non-empty group
		$this->request('POST', 'group/remove', array(
			'groupId' => $this->players[2]->getRegistrationGroup()->getId(),
		));
		$this->assertEquals("There are players in group ". $this->players[2]->getRegistrationGroup()->getName() ." that need to be deleted first", $this->response);
		
		// create empty group
		$this->request('POST', 'group/new', array(
			'name' => 'Test group D',
			'country' => 'Test country D'
		));
		$newGroupId = $this->response['groupId'];

		// remove empty group
		$this->request('POST', 'group/remove', array(
			'groupId' => $newGroupId,
		));
		$this->assertEquals("removed group Test group D", $this->response);

		// remove group again
		$this->request('POST', 'group/remove', array(
			'groupId' => $newGroupId,
		));
		$this->assertErrorResponse("No group found for id ". $newGroupId);
		
		$this->endTest();
	}
}
