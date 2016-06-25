<?php

namespace TS\ApiBundle\Controller\v2;

use FOS\OAuthServerBundle\Controller\TokenController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OauthController extends Controller
{


    /**
     * Retrieve Oauth token
     */
    public function tokenAction(Request $request) {
        if ($request->getMethod() == 'OPTIONS') {
            // deal with CORS
            $response = new Response();
            $response->setStatusCode(Response::HTTP_OK);
            ApiV2MainController::setResponseHeaders($response);
            return $response;
        }


        $response = $this->forward('fos_oauth_server.controller.token:tokenAction', array('request' => $request));
        ApiV2MainController::setResponseHeaders($response);
        return $response;
    }
}