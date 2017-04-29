<?php

namespace TS\ApiBundle\Controller\v2;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class CommandsController extends ApiV2MainController
{


    /**
     * Send multiple requests in the form of commands.
     * To try out in the sandbox: go to content and set: <br />
     *   commands[0][command]=discipline.list&commands[0][disciplineId]=85 <br />
     * Don't forget to set the Content-Type = application/x-www-form-urlencoded
     * It is possible to have setCommandKey, which will be used in the result as key instead of the command string. This also makes it possible to request multiple times the same command, but have the result separated with different keys.
     *
     * @ApiDoc(
     *  views="v2",
     *  section="Commands",
     *  description="Send multiple commands"
     * )
     */
    public function commandsAction($tournamentUrl, Request $request) {
        /*$this->setTournament($tournamentUrl, false);

		$liveCommands = array();
		if (!$this->tournament->getIsLiveClosed()) {
			$liveCommands[] = "Matches.playing";
		}*/

        $commands = $this->getParam('commands', true, array());
        ApiV2MainController::$isCommandRequest = true;

        foreach ($commands as $commandIndex=>$command) {
            /*// check for access
            $accessLevel = "EDIT";
            if (in_array($command['command'], $liveCommands)) {
                $accessLevel = "VIEW";
            }
            if (false === $this->get('security.authorization_checker')->isGranted($accessLevel, $this->tournament)) {
                throw new AccessDeniedException();
            }*/

            // check command for errors in controller and function
            if (!isset($command['command'])) {
                $this->newErrorMessage('No command given');
                continue;
            }

            $commandArray = explode(".", $command['command']);
            if (sizeof($commandArray) != 2) {
                $this->newErrorMessage('Wrong command '. $command['command']);
                continue;
            }
            $controller = $commandArray[0];

            $controllerName = $controller .'Controller';
            $forwardController = 'TSApiBundle:v2/'. $controller;
            if (!file_exists(__DIR__ .'/'. $controllerName .'.php')) {
                if (file_exists(__DIR__ .'/../'. $controllerName .'.php')) {
                    // fallback to v1
                    $forwardController = 'TSApiBundle:'. $controller;
                } else {
                    $this->newErrorMessage('Controller '. $controller .' does not exist');
                    continue;
                }
            }

            $function = $commandArray[1];

            /*
            // TODO: check function name
            $test = new DisciplineController();

            include_once(__DIR__ .'/'. $controllerName .'.php');
            include_once('DisciplineController.php');
            if (!class_exists('DisciplineController')) {
                //return $this->throwError('Class DisciplineController vast does not exist', self::$ERROR_BAD_REQUEST);
            }
            //$controllerObject = new $controllerName();

            $controllerObject = new DisciplineController();
            $tmpName = "DisciplineController";
            //$controllerObject = new $tmpName();

            if (!method_exists($controllerObject, $function .'Action')) {
                //return $this->throwError('Function '. $function .' does not exist for controller '. $controller, self::$ERROR_BAD_REQUEST);
            }*/

            // TODO: check function get params if they are in controllerArray

            $tournamentUrl = $this->tournament->getUrl();
            $controllerArray = array(
                'tournamentUrl'  => $tournamentUrl,
                'commandIndex' => $commandIndex,
            );
            $controllerArray = array_merge($controllerArray, $command);
            if (array_key_exists('setCommandKey', $command)) {
                $controllerArray['command'] = $command['setCommandKey'];
            }
            //$controllerArray['_route'] = 'aa';
            //unset($controllerArray['command']);

            $response = $this->forward($forwardController .':'. $function, $controllerArray, $controllerArray);

            if ($response->isClientError()) {
                if (array_key_exists($command['command'], ApiV2MainController::$commandErrorArray)) {
                    // error message set -> return it
                    ApiV2MainController::$commandResArray[$command['command']] = array('error' => "Error: ". ApiV2MainController::$commandErrorArray[$command['command']]);
                } else {
                    // threw an error with unknown message -> return response
                    ApiV2MainController::$commandResArray[$command['command']] = "Error: ". $response;
                }
            } else if (!$response->isOk()) {
                // there is something wrong with the response -> show it
                return $response;
            }
        }

        $resArray = array(
            'data' => array_merge(ApiV2MainController::$commandResArray)
        );
        $this->setMessagesJson($resArray);
        $response = new JsonResponse();
        $response->setData($resArray);
        self::setResponseHeaders($response);
        return $response;
    }

    /**
     * Webclient is requesting whether there are updates (done periodically)
     * Handles post request from json ajax, to change data
     * @ApiDoc(
     *  views="v2",
     *  section="Commands",
     *  description="Commands.updates"
     * )
     */
    public function updatesAction(Request $request)
    {
        $resArray = array(); // empty array, will be filled with messages by setMessagesJson()
        $this->setMessagesJson($resArray);
        return $this->handleResponse($resArray);
    }

    /**
     * Options request, to be able to serve CORS
     */
    public function optionsAction(Request $request)
    {
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        self::setResponseHeaders($response);
        return $response;
    }
}