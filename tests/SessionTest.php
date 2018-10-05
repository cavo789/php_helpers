<?php

declare(strict_types=1);

namespace cavo789;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Classes\Session as Session;
use \PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
	public function testGetInstance() : void
	{
		@session_start();

		$session = Session::getInstance('MyApp_');
		// The session will be valid 60 minutes
		$session->register(60);

		// We're not yet registered
		$this->assertFalse($session->isRegistered());

		// Not yet expired
		$this->assertFalse($session->isExpired());
	}

	public function testKeys() : void
	{
		@session_start();

		$session = Session::getInstance('MyApp_');

		// Store a key-value in the session
		$session->set('Password', 'MyPassword');

		$value = $session->get('Password', 'Oups...');
		$this->assertTrue($value == 'MyPassword');
	}

	public function testGetAll() : void
	{
		@session_start();

		$session = Session::getInstance('MyApp_');

		// Store a key-value in the session
		$session->set('Password', 'MyPassword');
		$session->set('User', ['lastname' => 'Doe', 'firstname' => 'John']);

		$value = $session->getAll();
		$expected['Password'] = 'MyPassword';
		$expected['User']['lastname'] = 'Doe';
		$expected['User']['firstname'] = 'John';

		$this->assertTrue($value == $expected);
	}

	public function testRemove() : void
	{
		@session_start();

		$session = Session::getInstance('MyApp_');

		// Store a key-value in the session
		$session->set('Password', 'MyPassword');
		$value = $session->get('Password', '#NOTFOUND');
		$expected = 'MyPassword';

		$this->assertTrue($value == $expected);

		// Remove the password so on next call, we can't retrieve
		// the password
		$session->remove('Password');
		$value = $session->get('Password', '#NOTFOUND');
		$this->assertFalse($value == $expected);
	}

	public function testFlashMessage() : void
	{
		@session_start();

		$session = Session::getInstance('MyApp_');

		// Store the "Invalid password" as a flash message
		// (destroyed on the first get)
		$session->flash('message', 'Invalid password');

		// Retrieve the message (should display Invalid password); first call
		$value = $session->getFlash('message', '#NOTFOUND');
		$expected = 'Invalid password';
		$this->assertTrue($value == $expected);

		// Second call, the message doesn't exists anymore
		$value = $session->getFlash('message', '#NOTFOUND');
		$this->assertFalse($value == $expected);
		$this->assertTrue($value == '#NOTFOUND');
	}
}
