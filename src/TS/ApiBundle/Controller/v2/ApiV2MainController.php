<?php

namespace TS\ApiBundle\Controller\v2;

use Gos\Component\WebSocketClient\Exception\BadResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TS\ApiBundle\Entity\Tournament;
use TS\ApiBundle\Entity\UpdateMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TS\ControlBundle\Entity;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class ApiV2MainController extends Controller
{
	protected static $messages = array();
	private $startLastUpdateId;
	protected static $isCommandRequest = false;
	protected static $commandResArray = array();
	protected static $commandErrorArray = array();
	private static $paramValues;

	public static $ERROR_BAD_REQUEST = 400;
	public static $ERROR_UNAUTHORIZED = 401;
	public static $ERROR_FORBIDDEN = 403;
	public static $ERROR_NOT_FOUND = 404;

	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		
	}

	/** @var \Symfony\Component\HttpFoundation\Request $request */
	protected $request;
	public function setRequest(Request $request) {
		$this->request = $request;
	}

    /* @var \TS\ApiBundle\Entity\Tournament $tournament */
	protected $tournament;
	
	public function setTournament($tournamentUrl) {
		try {
			$tournament = $this->getDoctrine()
				->getRepository('TSApiBundle:Tournament')
				->createQueryBuilder('t')
					->where('t.url = :tournamentUrl')
			    	->setParameter('tournamentUrl', $tournamentUrl)
			    ->getQuery()
			    ->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
			throw $this->throwError('No tournament found for url '.$tournamentUrl);
		}
		$this->tournament = $tournament;
		
		// add userId and POST values to log, to make debugging in live errors easier
        // passwords and such shouldn't be logged, but SecurityController is already taken out by ApiPreActionListener
        // $user can normally be null, but not here, since setTournament() should have given an exception
        $user = $this->getUser();
        $logger = $this->container->get('logger');
        $requestPost = $this->request->request->all();
        $requestTxt = str_replace("\n", " ", print_r($requestPost, true));
        $requestTxt = str_replace("  ", "", $requestTxt);
        $userId = "anonymous";
        if (!is_null($user)) {
        	$userId = $user->getId();
        }
        $logger->info("Request by userId ". $userId ." for tournamentId ". $this->tournament->getId() ." with POST ". $requestTxt);
            
		$this->setStartUpdateId();
	}

	/**
	 * Check authorization
	 * @throws Exception
	 */
	public function checkAuthorization() {
		$route = $this->request->get('_route');
		$writeRequest = ($this->request->getRealMethod() != 'GET') && ($this->request->get('_route') != 'api_v2_commands');
		$session = $this->request->getSession();
		if ($this->request->headers->has('X-API-KEY')) {
			$apiKeyTxt = $this->request->headers->get('X-API-KEY');
			$apiKey = null;
			try {
				$apiKey = $this->getDoctrine()
					->getRepository('TSApiBundle:ApiKey')
					->createQueryBuilder('apiKey')
					->leftJoin('apiKey.tournament', 'tournament')
					->where('tournament.id = :tournamentId')
					->setParameter('tournamentId', $this->tournament)
					->andWhere('apiKey.secret = :apiKey')
					->setParameter('apiKey', $apiKeyTxt)
					->getQuery()
					->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
				throw $this->throwError('Incorrect API key: '. $apiKeyTxt, self::$ERROR_FORBIDDEN);
			}
			if ($apiKey == null) {
				throw $this->throwError('Incorrect API key: '. $apiKeyTxt, self::$ERROR_FORBIDDEN);
			} else if ($writeRequest && !$apiKey->getWriteAccess()) {
				throw $this->throwError('No write access with this API key: '. $apiKeyTxt, self::$ERROR_FORBIDDEN);
			}
		} else if ($this->get('security.authorization_checker')->isGranted("EDIT", $this->tournament)) {
			// Logged in organizer
		} else if ($this->request->get('_route') == 'api_v2_tournaments_get') {
			// Always allow to request general information about the tournament (also to check for isApiAllowed)
		} else if ($session->get('hasLiveAccess', false) == $this->tournament->getUrl() || ($this->tournament->getAuthorization()->getLivePassword() == '')) {
			// Live access with password, or no password necessary
			if (!$this->tournament->getAuthorization()->isApiAllowed()) {
				throw $this->throwError('No Live API authorized for tournament '. $this->tournament->getUrl(), self::$ERROR_FORBIDDEN);
			}
			if ($writeRequest) {
				// only allow write request when liveScoreAllowed or live2ndCallAllowed for those routes
				if ($this->request->get('_route') ==  'api_v2_matches_score' && $this->tournament->getAuthorization()->isLiveScoreAllowed()) {
					// OK: score matches, and is allowed
				} else if ($this->request->get('_route') ==  'api_v2_matches_second_call' && $this->tournament->getAuthorization()->isLive2ndCallAllowed()) {
					// OK: 2nd call, and is allowed
				} else {
					// unauthorized API write
					throw $this->throwError('No write access for Live of tournament '. $this->tournament->getUrl(), self::$ERROR_FORBIDDEN);
				}
			}
		} else {
			throw $this->throwError("You do not have access, have you set X-API-KEY in the header?", self::$ERROR_UNAUTHORIZED);
		}
	}
	
	protected function getDiscipline($disciplineId) {
		$discipline = $this->getDoctrine()
			->getRepository('TSApiBundle:Discipline')
			->findOneBy(array('tournament' => $this->tournament, 'id' => $disciplineId));
		if (!$discipline) {
			throw $this->throwError('No discipline found for id '. $disciplineId);
		}
		return $discipline;
	}

    /**
     * Retrieves Player based on $playerId
     * @param $playerId
     * @return \TS\ApiBundle\Entity\Player $player
     * @throws Exception When no player is found
     */
	protected function getPlayer($playerId) {
		$player = $this->getDoctrine()
			->getRepository('TSApiBundle:Player')
			->findOneBy(array('tournament' => $this->tournament, 'id' => $playerId));
		if (!$player) {
			throw $this->throwError('No player found for id '. $playerId);
		}
		return $player;
	}

	protected function getRegistrationGroup($registrationGroupId) {
		$registrationGroup = $this->getDoctrine()
			->getRepository('TSApiBundle:RegistrationGroup')
			->findOneBy(array('tournament' => $this->tournament, 'id' => $registrationGroupId));
		if (!$registrationGroup) {
			throw $this->throwError('No group found for id '. $registrationGroupId);
		}
		return $registrationGroup;
	}

    /**
     * Retrieves Team based on $teamId
     * @param $teamId
     * @return \TS\ApiBundle\Entity\Team $team
     * @throws Exception When no team is found
     */
	protected function getTeam($teamId) {
		$team = $this->getDoctrine()
			->getRepository('TSApiBundle:Team')
			->findOneBy(array('tournament' => $this->tournament, 'id' => $teamId));
		if (!$team) {
			throw $this->throwError('No team found for id '. $teamId);
		}
		return $team;
	}

    /**
     * Retrieves Match based on $matchId
     * @param $matchId
     * @return \TS\ApiBundle\Entity\Match $matchId
     * @throws Exception When no match is found
     */
	protected function getMatch($matchId) {
		$match = $this->getDoctrine()
			->getRepository('TSApiBundle:Match')
			->findOneBy(array('tournament' => $this->tournament, 'id' => $matchId));
		if (!$match) {
			throw $this->throwError('No match found for id '. $matchId);
		}
		return $match;
	}

    /**
     * Retrieves Location based on $locationId
     * @param $locationId
     * @return \TS\ApiBundle\Entity\Location $location
     * @throws Exception When no location is found
     */
    protected function getLocation($locationId) {
		$location = $this->getDoctrine()
			->getRepository('TSApiBundle:Location')
			->findOneBy(array('tournament' => $this->tournament, 'id' => $locationId));
		if (!$location) {
			throw $this->throwError('No location found for id '. $locationId);
		}
		return $location;
	}

	/**
	 * Retrieves Pool based on $poolId
	 * @param $poolId
	 * @return \TS\ApiBundle\Entity\Pool $pool
	 * @throws Exception When no pool is found
	 */
	protected function getPool($poolId) {
		$pool = $this->getDoctrine()
			->getRepository('TSApiBundle:Pool')
			->findOneBy(array('tournament' => $this->tournament, 'id' => $poolId));
		if (!$pool) {
			throw $this->throwError('No pool found for id '. $poolId);
		}
		return $pool;
	}
	
	protected function getAnnouncement($announcementId) {
		$announcement = $this->getDoctrine()
			->getRepository('TSApiBundle:Announcement')
			->findOneBy(array('tournament' => $this->tournament, 'id' => $announcementId));
		if (!$announcement) {
			throw $this->throwError('No announcement found for id '. $announcementId);
		}
		return $announcement;
	}
	
	/**
	  * Create a new UpdateMessage object
	  * @param String $updateSection Can be: all, discipline, team, player, match, location
	  */
	protected function newMessage($type, $title, $text, $updateSection="all") {
		$message = new UpdateMessage();
		$message->setType($type);
		$message->setTitle($title);
		$message->setText($text);
		$message->setUpdateSection($updateSection);
		$loginAccount = $this->getUser();
		if (!is_null($loginAccount)) {
			$message->setLoginAccount($loginAccount);
		}
		$message->setTournament($this->tournament);
		$this->tournament->addUpdateMessage($message);

		$this->em()->flush();

		// Push message to Websocket
		$pusher = $this->container->get('gos_web_socket.wamp.pusher');
		$pushMessage = array(
			'type' => $type,
			'title' => $title,
			'text' => $text,
			'updateSection' => $updateSection,
			'messageId' => $message->getId()
		);
		if (!is_null($loginAccount)) {
			$pushMessage['loginAccountId'] = $loginAccount->getId();
			$pushMessage['loginAccountName'] = $loginAccount->getPerson()->getName();

		}
		try {
			$pusher->push($pushMessage, 'api_topic_updates', ['tournamentUrl' => $this->tournament->getUrl()]);
		} catch (BadResponseException $e) {
			// Websocket not available, do nothing
		}
	}
	
	protected function newErrorMessage($message, $log=false) {
		if ($log) {
			$this->newMessage('error', 'Error', $message);
		} else {
			self::$messages[] = array('type' => 'error', 'title' => 'Error', 'text' => $message, 'updateSection' => "all");
		}
	}
	
	/**
	  * Returns the last update(message)Id currently in use
	  * Returns int The ID of the last UpdateMessage
	  */
	protected function getLastUpdateId() {
		return $this->em()->getRepository('TSApiBundle:UpdateMessage')->getLastId();
	}
	
	/**
	  * Returns the UpdateMessage objects between two IDs
	  * Returns array UpdateMessage objects
	  */
	private function setMessages($fromId, $toId) {
		$result = $this->em()->getRepository('TSApiBundle:UpdateMessage')->getBetweenId($fromId, $toId, $this->tournament);
		$currentLoginAccount = $this->getUser();
		foreach ($result as $message) {
			$origin = '';
            $text = $message->getText();
			if ($message->getType() != "success") {
				$text = $text;
			} else if ((!is_null($message->getLoginAccount())) && (!is_null($currentLoginAccount)) && $message->getLoginAccount()->isEqualTo($currentLoginAccount)) {
				$text = "You ". $text;
                $origin = 'me';
			} else {
				if (is_null($message->getLoginAccount())) {
					$text = "Anonymous Live ". $text;
				} else {
					$text = $message->getLoginAccount()->getPerson()->getName() ." ". $text;
				}
                $origin = 'otherPerson';
			}
			self::$messages[] = array(
				'messageId' => $message->getId(),
                'origin' => $origin,
				'type' => $message->getType(), 
				'title' => $message->getTitle(), 
				'text' => $text,
				'datetime' => $message->getDatetime(),
				'updateSection' => $message->getUpdateSection()
			);
		}
	}
	
	// setting lastUpdateId before executing changes, so that these changes with UpdateMessages will also be returned
	public function setStartUpdateId() {
		$startUpdateId = $this->getParam('lastUpdateId', false, $this->getLastUpdateId());
		if ($startUpdateId == 0) {
			$startUpdateId = $this->getLastUpdateId();
		}
		$this->startUpdateId = $startUpdateId;
	}
	
	/**
	  * Setting the messages in the JSON array
	  */
	protected function setMessagesJson(&$jsonArray) {
		if (!isset($this->startUpdateId)) {
			throw $this->throwError('No start update ID set', self::$ERROR_BAD_REQUEST);
		}
		$newLastUpdateId = $this->getLastUpdateId();
		$this->setMessages($this->startUpdateId, $newLastUpdateId);
		
		$jsonArray['messages'] = self::$messages;
		$jsonArray['lastUpdateId'] = $newLastUpdateId;
	}
	
	/**
	 * Throw an error
     * @param String $message
     * @param int $errorCode
     * @return HttpException
	 */
	protected function throwError($message, $errorCode=404) {
		if (self::$isCommandRequest) {
			// command request -> show error in message
			$command = $this->request->query->get('command', '');
			self::$commandErrorArray[$command] = $message;
			$this->newErrorMessage($message);
		}

		$responseHeaders = array(
			'Access-Control-Allow-Origin' => '*',
			'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
			'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
		);
		throw new HttpException($errorCode, $message, null, $responseHeaders, $errorCode);
	}
	
	/**
	  * Handle the result of the res array for a JSON Response
	  * If isCommandRequest -> result will be saved for when responding with all command results. Else: return a JSON Response Object.
	  * @param mixed $result The result (text message or array or entity object) for the JSON response
	  * @return Response object If !isCommandRequest then return JsonResponse object, else return empty Response object.
	  */
	protected function handleResponse($result) {
		$this->em()->flush();
		
		if (self::$isCommandRequest) {
			// save request for response of all commands
			$command = $this->request->query->get('command', '');
			self::$commandResArray[$command] = $result;
			return new Response();
		} else {
			$encoders = array(new XmlEncoder(), new JsonEncoder());
			$normalizers = array(new GetSetMethodNormalizer());
			$serializer = new Serializer($normalizers, $encoders);

			// decide on JSON or XML output
			$contentType = $this->request->headers->get('Content-Type');
			$format = ($contentType === null) ? $this->request->getRequestFormat('json') : $this->request->getFormat($contentType);
			if ($format != "json" && $format != "xml") {
				$format = "json";
			}
			$content = $serializer->serialize($result, $format);

            $response = new Response();
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_OK);
			self::setResponseHeaders($response);
            $response->headers->set('Content-Type', 'application/'. $format);
			return $response;
		}
	}
	
	/**
	  * Get POST or GET value
	  * @param String $name Name of POST or GET value, i.e. $_POST[$name] or $_GET[$name]
	  * @param boolean $required Whether throwError() should be called if name is not set
	  * @param mixed $defaultValue The value returned if the name is not set
	  */
	protected function getParam($name, $required = true, $defaultValue = null) {
		if (!isset(self::$paramValues)) {
			// Setting POST and GET values
			// JSON or x-www-form-urlencoded body data will be handled by FOSRestBundle, and be put in POST
			self::$paramValues = array_merge($this->request->request->all(), $this->request->query->all());
		}
		
		$reference = self::$paramValues;
		if (self::$isCommandRequest) {
			// post values are saved in self::$paramValues, but since it is a command request, they have to be retrieved for a specific command index
			$commandIndex = intval($this->request->query->get('commandIndex'));
			$reference = self::$paramValues['commands'][$commandIndex];
		}
		
		
		if (!isset($reference[$name])) {
			if ($required) {
				// not set but required -> throw error
				$this->throwError('Parameter '. $name .' is required but not set', self::$ERROR_BAD_REQUEST);
			} else {
				// not set and not required -> return default value
				return $defaultValue;
			}
		} else {
			// set -> return value
			return $reference[$name];
		}
	}
	
	protected function em() {
		return $this->getDoctrine()->getManager();
	}

	public static function setResponseHeaders(Response &$response) {
		$response->headers->set('Access-Control-Allow-Origin', '*');
		$response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
		$response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
	}
}
