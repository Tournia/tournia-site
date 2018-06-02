<?php

use Symfony\Component\HttpFoundation\Request;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.

header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");

if (isset($_SERVER['HTTP_CLIENT_IP'])
	    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
	    || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1'))
	) {
	    //header('HTTP/1.0 403 Forbidden');
	    //exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
	    
	    // not localhost -> login via htpasswd
	    /*require_once('../htpasswd.inc.php');
	    $pass_array = load_htpasswd();
	    if (!isset($_SERVER['PHP_AUTH_USER']) || !test_htpasswd( $pass_array,  $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] )) {
			header('WWW-Authenticate: Basic realm="Restricted area"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'Access denied for '.basename(__FILE__).'. Please enter correct credentials.';
			exit;
		} */
	    
} 

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);