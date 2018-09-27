<?php

/**
 * Christophe Avonture
 * Written date: 2018-09-13
 * Last modified:
 *
 * cavo789\Class\Session aimed to provide features for
 * working with the $_SESSION object
 *
 * How to:
 * 		use \cavo789\Classes\Session as Session;
 * 		$session = new Session('MyApp_');
 * 		$session->set('Password', md5('MyPassword'));
 *
 * 		echo '<pre>'.print_r($session->getAll(), true).'</pre>';
 *
 * 		if ($session->get('Password', '') === md5('MyPassword')) {
 * 			echo 'Great, correct password';
 * 		}
 *
 * 		unset($session);
 */

declare(strict_types=1);

namespace cavo789\Classes;

use cavo789\Helpers\Strings as Strings;

class Session
{
	private $key_prefix = '';

	/**
	 * Constructor
	 *
	 * @param string $key_prefix Keys added by this class
	 *                           can have a prefix to make sure that there will
	 *                           be no conflict with other running script.
	 *                           Specify f.i. "MySuperSoft_" to have all your keys
	 *                           prefixed with that pattern.
	 */
	public function __construct(string $key_prefix = '')
	{
		if (!isset($_SESSION)) {
			session_start();
		}
		$this->key_prefix = trim($key_prefix) ?? '';
	}

	/**
	 * Destroys the session.
	 */
	public function destroy()
	{
		session_destroy();
		$_SESSION = [];
	}

	/**
	 * Register the session.
	 *
	 * @param integer $time Expressed in minutes, the length of time
	 *                      during which the session will be considered valid
	 */
	public function register(int $time = 60)
	{
		$_SESSION[self::getKey('session_id')] = session_id();
		$_SESSION[self::getKey('session_time')] = intval($time);
		$_SESSION[self::getKey('session_start')] = self::newTime();
	}

	/**
	 * Checks if the session has been registered
	 *
	 * @return boolean True if it is, False if not.
	 */
	public function isRegistered() : bool
	{
		if (!empty($_SESSION[self::getKey('session_id')])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set key/value in session.
	 *
	 * @param string $key
	 * @param  $value
	 */
	public function set(string $key, $value)
	{
		$_SESSION[self::getKey($key)] = $value;
	}

	/**
	 * Retrieve value stored in session by key.
	 *
	 * @param  string $key     Entry to retrieve
	 * @param         $default Default value
	 * @return void
	 */
	public function get(string $key, $default = null)
	{
		return $_SESSION[self::getKey($key)] ?? $default;
	}

	/**
	 * Retrieve the global session variable.
	 *
	 * @return array
	 */
	public function getAll() : array
	{
		$arr = [];

		// Take care to remove the prefix before returning the
		// list of keys in the $_SESSION
		foreach ($_SESSION as $key => $value) {
			if (Strings::startsWith($key, $this->key_prefix)) {
				$key = substr($key, strlen($this->key_prefix));
			}
			$arr[$key] = $value;
		}

		return $arr;
	}

	/**
	 * Gets the id for the current session.
	 *
	 * @return string - session id (not an integer!)
	 */
	public function getSessionId() : string
	{
		return $_SESSION[self::getKey('session_id')];
	}

	/**
	 * Checks to see if the session is still valid (expired?).
	 *
	 * @return boolean
	 */
	public function isExpired()
	{
		if ($_SESSION[self::getKey('session_start')] < $this->timeNow()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Extends the current session
	 */
	public function renew()
	{
		$_SESSION[self::getKey('session_start')] = self::newTime();
	}

	/**
	 * Simply add the prefix to the requested key
	 *
	 * @param  string $key
	 * @return string
	 */
	private function getKey(string $key) : string
	{
		return $this->key_prefix . $key;
	}

	/**
	 * Returns the current time.
	 *
	 * @return int timestamp
	 */
	private function timeNow() : int
	{
		return mktime(
			intval(date('H')),	// Hour
			intval(date('i')),	// Minutes
			intval(date('s')),	// Seconds
			intval(date('m')),	// Month
			intval(date('d')),	// Day
			intval(date('y'))	// Year
		);
	}

	/**
	 * Generates new time.
	 *
	 * @return int timestamp
	 */
	private function newTime() : int
	{
		return mktime(
			intval(date('H')),	// Hour
			(intval(date('i')) + $_SESSION[self::getKey('session_time')]), // Minutes
			intval(date('s')),	// Seconds
			intval(date('m')),	// Month
			intval(date('d')),	// Day
			intval(date('y'))	// Year
		);
	}
}
