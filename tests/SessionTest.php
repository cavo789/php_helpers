<?php

namespace cavo789;

/**
 * Run this script from the command prompt :
 *		php SessionTest.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Classes\Session as Session;
use \tests\Helpers\Utilities as Utilities;

/**
 * Run the tests
 */
$session = new Session('MyApp_');

echo Utilities::out('Check cavo789\Classes\Session', true);

// The session will be valid 60 minutes
$session->register(60);

echo Utilities::out('Session registered?: ' . ($session->isRegistered() ? 'YES' : 'NO'));

echo Utilities::out('Current session id: ' . $session->getSessionId());

// Store a key-value in the session
$session->set('Password', md5('MyPassword'));

// Store a key-value in the session
$session->set('User', ['lastname' => 'Doe', 'firstname' => 'John']);

// Get all stored information's
echo Utilities::out(PHP_EOL . print_r($session->getAll(), true));

if ($session->get('Password', '') === md5('MyPassword')) {
	echo Utilities::out('Great, correct password');
} else {
	echo Utilities::out('Oups, incorrect password');
}

echo Utilities::out(PHP_EOL . 'Retrieve an array from the session');
echo Utilities::out(print_r($session->get('User'), true));

echo Utilities::out('Session isExpired?: ' . ($session->isExpired() ? 'YES' : 'NO'));

// Kill the session
$session->destroy();

unset($session);
