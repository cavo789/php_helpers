<?php

declare(strict_types=1);

namespace cavo789\tests;

use \cavo789\Classes\Session as Session;
use \PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    public function testGetInstance()
    {
        @session_start();

        // The session can't be started when fired from PHPUnit on CLI
        $this->expectException('Exception');

        $session = Session::getInstance('MyApp_');
        // The session will be valid 60 minutes
        $session->register(60);

        // We're not yet registered
        $this->assertFalse($session->isRegistered());

        // Not yet expired
        $this->assertFalse($session->isExpired());
    }

    public function testKeys()
    {
        @session_start();

        // The session can't be started when fired from PHPUnit on CLI
        $this->expectException('Exception');

        $session = Session::getInstance('MyApp_');

        // Store a key-value in the session
        $session->setString('Password', 'MyPassword');

        $value = $session->getString('Password', 'Oups...');
        $this->assertTrue($value == 'MyPassword');

        // Store a key-value in the session
        $session->setInt('Age', 42);

        $value = $session->getInt('Age');
        $this->assertSame($value, 42);
    }

    public function testGetAll()
    {
        @session_start();

        // The session can't be started when fired from PHPUnit on CLI
        $this->expectException('Exception');

        $session = Session::getInstance('MyApp_');

        // Store a key-value in the session
        $session->setString('Password', 'MyPassword');
        $session->setArray('User', ['lastname' => 'Doe', 'firstname' => 'John']);

        $value = $session->getAll();
        $expected = [];
        $expected['Password'] = 'MyPassword';
        $expected['User']['lastname'] = 'Doe';
        $expected['User']['firstname'] = 'John';

        $this->assertTrue($value == $expected);
    }

    public function testRemove()
    {
        @session_start();

        // The session can't be started when fired from PHPUnit on CLI
        $this->expectException('Exception');

        $session = Session::getInstance('MyApp_');

        // Store a key-value in the session
        $session->setString('Password', 'MyPassword');
        $value = $session->getString('Password', '#NOTFOUND');
        $expected = 'MyPassword';

        $this->assertTrue($value == $expected);

        // Remove the password so on next call, we can't retrieve
        // the password
        $session->remove('Password');
        $value = $session->getString('Password', '#NOTFOUND');
        $this->assertFalse($value == $expected);
    }

    public function testFlashMessage()
    {
        @session_start();

        // The session can't be started when fired from PHPUnit on CLI
        $this->expectException('Exception');

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
