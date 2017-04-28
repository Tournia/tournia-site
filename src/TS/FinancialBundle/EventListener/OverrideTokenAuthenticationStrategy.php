<?php

namespace TS\FinancialBundle\EventListener;

use JMS\Payment\CoreBundle\BrowserKit\Request;
use JMS\Payment\PaypalBundle\Client\Authentication\AuthenticationStrategyInterface;

/*
 * Copyright 2010 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class OverrideTokenAuthenticationStrategy implements AuthenticationStrategyInterface
{
    private $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * @param mixed $username
     * @return OverrideTokenAuthenticationStrategy
     */
    public function setUsername($username)
    {
        $this->session->set('paypal_username', $username);

        return $this;
    }

    /**
     * @param mixed $password
     * @return OverrideTokenAuthenticationStrategy
     */
    public function setPassword($password)
    {
        $this->session->set('paypal_password', $password);

        return $this;
    }

    /**
     * @param mixed $signature
     * @return OverrideTokenAuthenticationStrategy
     */
    public function setSignature($signature)
    {
        $this->session->set('paypal_signature', $signature);

        return $this;
    }



    public function authenticate(Request $request)
    {
        $request->request->set('PWD', $this->session->get('paypal_password'));
        $request->request->set('USER', $this->session->get('paypal_username'));
        $request->request->set('SIGNATURE', $this->session->get('paypal_signature'));
    }

    public function getApiEndpoint($isDebug)
    {
        if ($isDebug) {
            return 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            return 'https://api-3t.paypal.com/nvp';
        }
    }
}
