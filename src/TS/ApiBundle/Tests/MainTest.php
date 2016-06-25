<?php

namespace TS\ApiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TS\ApiBundle\Entity\DisciplinePlayer;
use TS\ApiBundle\Entity\Tournament;
use TS\ApiBundle\Entity\Discipline;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken,
    Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\BrowserKit\Cookie;
use TS\SiteBundle\Entity\Site;

class MainTest extends WebTestCase
{
	const USER_USERNAME = 'test2';
	const USER_PASSWORD = 'test2';
	
	protected $client;
	protected $em;
	protected $response;
	protected $testUser;
	protected $tournament;
	protected $tournamentUrl;
	protected $players;
	protected $disciplines = array();
	protected $teams = array();
	protected $locations = array();
	protected $matches = array();
	
	protected static function getPhpUnitXmlDir() {
		// the default dir is for VisualPHPUnit
		$dir = getcwd() ."/../../../app/";
		if (!is_dir($dir)) {
			// phpunit is executed from command line
			$dir = getcwd() ."/app/";
		}
		return $dir;
	}
	
	// TODO: use suite start function for startTest as initializer
	// start testing, and initialize data
	protected function startTest() {
		$this->client = static::createClient(array(
			'environment' => 'test',
			'debug'       => true,
		));
		
		// getting the entity manager of doctrine
		$container = $this->client->getContainer();
		$this->em = $container->get('doctrine.orm.entity_manager');
		
		// setting the user which is used for requests
		$this->testUser = $this->em
			->getRepository('TSApiBundle:LoginAccount')
			->findOneByUsername(self::USER_USERNAME);
        
		// setting variables
		$this->setTournament();
		$this->players = array();
		for ($i = 0; $i < 10; $i++) {
			$this->setPlayer($i);
		}
	}
	
	// do request of client in a simple method, and save result (possibly in array format) in $this->reponse
	protected function request($method, $url, $postValues = array(), $format="json") {
		$this->client = null;
		$this->client = static::createClient(array(
			'environment' => 'test',
			'debug'       => true,
		));
		$this->client->enableProfiler();
		// having multiple connections
		$this->client->insulate();

		$completeUrl = '/api/'. $this->tournament->getUrl() .'/'. $url .'.'. $format;
		$crawler = $this->client->request($method, $completeUrl, $postValues);
		$this->response = $this->client->getResponse()->getContent();

		if ($this->client->getResponse()->getStatusCode() == 403) {
			echo "Request ". $completeUrl .": not authorized";
		}
		if ($format == "json") {
			$this->response = json_decode($this->response, true);
		}
		if (json_last_error() != JSON_ERROR_NONE) {
			echo "Incorrect json response for ". $completeUrl .": ". $this->response;
		}
		return $crawler;
	}
	
	// perform standard tests on response
	protected function standardChecks() {
		/*if (!$this->client->getResponse()->isSuccessful()) {
			exit("code = ". $this->client->getResponse()->getStatusCode());
		}*/
		$this->assertTrue($this->client->getResponse()->isSuccessful());
		$this->assertTrue(
			$this->client->getResponse()->headers->contains(
				'Content-Type',
				'application/json'
			)
		);
	}
	
	// perform test that response is an error
	public function assertErrorResponse($message = null) {
		$this->assertFalse($this->client->getResponse()->isSuccessful());
		if (!is_null($message)) {
			$this->assertEquals($message, $this->response['error']['exception'][0]['message']);
		}
	}
	
	private function setTournament() {
		// create new test tournament
		$tournament = new Tournament();
		$this->tournamentUrl = 'test'. round(microtime(true)*1000);
		$tournament->setUrl($this->tournamentUrl);
		$tournament->setName("Test tournament");
		$tournament->setEmailFrom("test@tournia.net");
		$tournament->setContactName("Test contact");
		$tournament->addOrganizerPerson($this->testUser->getPerson());

        $site = new Site();
        $tournament->setSite($site);

		$this->em->persist($tournament);
		$this->em->flush();
			
		$this->tournament = $tournament;
	}
	
	// update the reference to $this->tournament, in case changes have been made with API requests
	protected function updateTournamentReference() {
		$this->tournament = null;
		$this->tournament = $this->em
			->getRepository('TSApiBundle:Tournament')
			->findOneByUrl($this->tournamentUrl);
	}	
	
	private function setPlayer($index) {
		// create group
		$this->request('POST', 'group/new', array(
			'name' => "Group ". $index,
			'country' => "Test country",
		));
		$groupId = $this->response['groupId'];

		// create player
		$gender = ($index % 2 == 0) ? "M" : "F";
		$this->request('POST', 'player/new', array(
			'firstName' => "Test",
			'lastName' => $index,
			'gender' => $gender,
			'groupId' => $groupId,
		));
		$playerId = $this->response['playerId'];

		$player = $this->em
			->getRepository('TSApiBundle:Player')
			->findOneById($playerId);
		$this->players[$index] = $player;
	}
	
	// test data is filled with different scenarios, based on how for the tournament planning has been progressed
	// scenario 1: adding disciplines
	protected function performScenario1() {
		// creating singles, doubles and mixed discipline
		for ($i = 1; $i <= 5; $i++) {
			$discipline = new Discipline();
			$discipline->setTournament($this->tournament);
			if (($i == 1)||($i == 2)) {
				// singles
				if ($i == 1) {
					// men singles
					$discipline->setName("Test Men Singles");
					$discipline->setGender("M");
				} else {
					// ladies singles
					$discipline->setName("Test Ladies Singles");
					$discipline->setGender("F");
				}
				$discipline->setType("S");
			} else if (($i == 3) || ($i == 4)) {
				// doubles
				if ($i == 3) {
					// men doubles
					$discipline->setName("Test Men Doubles");
					$discipline->setGender("M");
				} else {
					// ladies doubles
					$discipline->setName("Test Ladies Doubles");
					$discipline->setGender("F");
				}
				$discipline->setType("D");
			} else {
				// mixed
				$discipline->setName("Test Mixed");
				$discipline->setGender("B");
				$discipline->setType("M");
			}
			$this->tournament->addDiscipline($discipline);
			$this->em->persist($discipline);
			$this->disciplines[] = $discipline;
		}
		$this->em->persist($this->tournament);
		$this->em->flush();
		
		$this->updateTournamentReference();
	}
	
	// scenario 2: adding players to disciplines
	protected function performScenario2() {
		
		for ($i = 0; $i < 5; $i++) {
			$commandArray = array();
			$commandArray[] = array(
				'command' => 'Disciplines.addPlayer',
				'disciplineId' => $this->disciplines[$i]->getId(),
				'playerId' => $this->players[($i+1)]->getId(),
			);
			$commandArray[] = array(
				'command' => 'Disciplines.addPlayer',
				'disciplineId' => $this->disciplines[$i]->getId(),
				'playerId' => $this->players[($i+2)]->getId(),
			);
			$commandArray[] = array(
				'command' => 'Disciplines.addPlayer',
				'disciplineId' => $this->disciplines[$i]->getId(),
				'playerId' => $this->players[($i+3)]->getId(),
			);
			$commandArray[] = array(
				'command' => 'Disciplines.addPlayer',
				'disciplineId' => $this->disciplines[$i]->getId(),
				'playerId' => $this->players[($i+4)]->getId(),
			);
			$commandArray[] = array(
				'command' => 'Disciplines.addPlayer',
				'disciplineId' => $this->disciplines[$i]->getId(),
				'playerId' => $this->players[($i+5)]->getId(),
			);
			$this->request('POST', 'command', array('commands' => $commandArray));
		}
		
		// also add discipline to registration (preferences)
		// singles
		$this->addDisciplinePlayer($this->players[0], $this->disciplines[0]);
		$this->addDisciplinePlayer($this->players[1], $this->disciplines[1]);
		$this->addDisciplinePlayer($this->players[2], $this->disciplines[0]);
		$this->addDisciplinePlayer($this->players[3], $this->disciplines[1]);
		// doubles
		$this->addDisciplinePlayer($this->players[2], $this->disciplines[2]);
		$this->addDisciplinePlayer($this->players[3], $this->disciplines[3]);
		$this->addDisciplinePlayer($this->players[4], $this->disciplines[2]);
		$this->addDisciplinePlayer($this->players[5], $this->disciplines[3]);
		// mixed
		$this->addDisciplinePlayer($this->players[4], $this->disciplines[4]);
		$this->addDisciplinePlayer($this->players[5], $this->disciplines[4]);
		$this->addDisciplinePlayer($this->players[6], $this->disciplines[4]);
		$this->addDisciplinePlayer($this->players[7], $this->disciplines[4]);
		for($i = 0; $i < 10; $i++) {
			$this->em->persist($this->players[$i]);
		}
		$this->em->flush();
		
		$this->updateTournamentReference();
	}

	private function addDisciplinePlayer($player, $discipline) {
		$disciplinePlayer = new DisciplinePlayer();
		$disciplinePlayer->setPlayer($player);
		$disciplinePlayer->setDiscipline($discipline);
		$player->addDisciplinePlayer($disciplinePlayer);
	}
	
	// scenario 3: creating teams
	protected function performScenario3() {
		$commandArray[] = array(
			'command' => 'Disciplines.autoAssign',
		);
		
		$this->request('POST', 'command', array('commands' => $commandArray));
		$this->updateTournamentReference();
		
		$this->teams = $this->em
        	->getRepository('TSApiBundle:Team')
        	->findByTournament($this->tournament);
	}
	
	// scenario 4: creating locations
	protected function performScenario4() {
		$commandArray[] = array(
			'command' => 'Locations.create',
			'name' => 'Test location 0',
		);
		$commandArray[] = array(
			'command' => 'Locations.create',
			'name' => 'Test location 1',
		);
		$commandArray[] = array(
			'command' => 'Locations.create',
			'name' => 'Test location 2',
		);
		$commandArray[] = array(
			'command' => 'Locations.create',
			'name' => 'Test location 3',
		);
		
		$this->request('POST', 'command', array('commands' => $commandArray));
		$this->updateTournamentReference();
		
		$this->locations = $this->em
        	->getRepository('TSApiBundle:Location')
        	->findByTournament($this->tournament);
	}
	
	// scenario 5: creating a round
	protected function performScenario5() {
		$commandArray[] = array(
			'command' => 'Rounds.create',
			'disciplineId' => 'all',
		);
		
		$this->request('POST', 'command', array('commands' => $commandArray));
		$this->updateTournamentReference();
		
		$this->matches = $this->em
        	->getRepository('TSApiBundle:Match')
        	->findByTournament($this->tournament);
	}
	
	
	
	// after performing tests, delete tournament data
	protected function endTest() {
		$this->updateTournamentReference();
		
		// delete update messages
		$updateMessages = $this->em
        	->getRepository('TSApiBundle:UpdateMessage')
        	->findByTournament($this->tournament);
		foreach ($updateMessages as $updateMessage) {
			$this->em->remove($updateMessage);
			//$this->tournament->removeUpdateMessage($updateMessage);
		}
		
		// delete locations
		$locations = $this->em
        	->getRepository('TSApiBundle:Location')
        	->findByTournament($this->tournament);
		foreach ($locations as $location) {
			$this->em->remove($location);
		}
		
		// delete matches
		$matches = $this->em
        	->getRepository('TSApiBundle:Match')
        	->findByTournament($this->tournament);
		foreach ($matches as $match) {
			$this->em
					->getRepository('TSApiBundle:Match')
					->remove($match);
		}
		
		// delete announcements
		$announcements = $this->em
        	->getRepository('TSApiBundle:Announcement')
        	->findByTournament($this->tournament);
		foreach ($announcements as $announcement) {
			$this->em->remove($announcement);
		}
		
		// delete disciplines and teams
		$disciplines = $this->em
        	->getRepository('TSApiBundle:Discipline')
        	->findByTournament($this->tournament);
		foreach ($disciplines as $discipline) {
			// remove teams
			$teams = $this->em
        		->getRepository('TSApiBundle:Team')
        		->findBy(array('tournament' => $this->tournament, 'discipline'=>$discipline));
			foreach ($teams as $team) {
				foreach ($team->getPlayers() as $player) {
					$this->em->remove($player);
				}
				$this->em->remove($team);
				
			}
			$this->em->remove($discipline);
		}
		
		// remove rest of players, that aren't in a discipline or team
		$players = $this->em
        	->getRepository('TSApiBundle:Player')
        	->findByTournament($this->tournament);
        foreach ($players as $player) {
        	$this->em->remove($player->getRegistrationGroup());
        	$this->em->remove($player);
        }
        
        $groups = $this->em
        	->getRepository('TSApiBundle:RegistrationGroup')
        	->findByTournament($this->tournament);
        foreach ($groups as $group) {
        	$this->em->remove($group);
        }
		
		//$this->tournament->removeOrganizerUser($this->testUser);
		
		$this->em->remove($this->tournament);
		$this->em->flush();
	}
	
	// this class needs to have one test for when individual files are tested
	public function testTrue() {
		$this->assertTrue(true);
	}
}
