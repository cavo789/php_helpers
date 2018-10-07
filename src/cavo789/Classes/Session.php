<?php

declare(strict_types=1);

/**
 * Christophe Avonture
 * Written date: 2018-09-13
 *
 * Description
 * cavo789\Class\Session aimed to provide features for
 * working with the $_SESSION object
 *
 * Because this class can be instantiated in more than one script
 * of the same application, the class is a Singleton: only one instance
 * will be instantiated and loaded into memory.
 *
 * How to:
 *      use \cavo789\Classes\Session as Session;
 *      $session = Session::getInstance('MyApp_');
 *      $session->set('Password', md5('MyPassword'));
 *
 *      echo '<pre>'.print_r($session->getAll(), true).'</pre>';
 *
 *      if ($session->get('Password', '') === md5('MyPassword')) {
 *          echo 'Great, correct password';
 *      }
 *
 *      unset($session);
 */

namespace cavo789\Classes;

use cavo789\Helpers\Strings as Strings;

class Session
{
    private $key_prefix = '';

    // Default duration for a session (15 minutes)
    private $duration = 15;

    /**
     * @var Session
     * @access private
     * @static
     */
    private static $_instance = null;

    /**
     * Constructor
     *
     * @param string $key_prefix Keys added by this class
     *                           can have a prefix to make sure that there will
     *                           be no conflict with other running script.
     *                           Specify f.i. "MySuperSoft_" to have all your keys
     *                           prefixed with that pattern.
     */
    private function __construct(string $key_prefix = '')
    {
        $this->key_prefix = trim($key_prefix) ?? '';
    }

    /**
     * Load an instance of the class
     *
     * @param string $key_prefix Keys added by this class
     *                           can have a prefix to make sure that there will
     *                           be no conflict with other running script.
     *                           Specify f.i. "MySuperSoft_" to have all your keys
     *                           prefixed with that pattern.
     *
     * @return Session
     */
    public static function getInstance(string $key_prefix = '')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Session($key_prefix);
        }

        if (!isset($_SESSION)) {
            if (!@session_start()) {
                throw new \Exception('The session can\'t be started');
            } else {
                $_SESSION[$key_prefix . 'session_id'] = session_id();
            }
        }

        return self::$_instance;
    }

    /**
     * Destroys the session.
     */
    public function destroy()
    {
        if (isset($_SESSION)) {
            if (session_id()) {
                session_destroy();
            }
            $_SESSION = [];
        }
    }

    /**
     * Register the session.
     *
     * @param integer $duration Expressed in minutes, the length of time
     *                          during which the session will be considered valid
     */
    public function register(int $duration = 15)
    {
        $this->duration = $duration;
        self::set('session_validUntil', self::validUntil());
    }

    /**
     * Checks if the session has been registered. When the session
     * has expired, this function will return False too.
     *
     * @return boolean True if it is, False if not.
     */
    public function isRegistered() : bool
    {
        if (self::isExpired()) {
            return false;
        } else {
            if (trim(strval(self::get('session_id', ''))) !== '') {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Checks to see if the session is still valid (expired?).
     *
     * @return boolean
     */
    public function isExpired()
    {
        if (self::get('session_validUntil') < time()) {
            // When the session is expired, destory the session
            // to remove any sensitive information's
            //self::destroy();

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
     * @return mixed
     */
    public function get(string $key, $default = null) : mixed
    {
        return $_SESSION[self::getKey($key)] ?? $default;
    }

    /**
     * Store a flash message into the Session. On the first call,
     * the message will be removed.
     *
     * @param  string $key
     * @param  string $value
     * @return void
     */
    public function flash(string $key, string $value)
    {
        self::set('flash.' . $key, $value);
    }

    /**
     * Get a flash message. Once obtained, the message will be removed
     * from the Session variables
     *
     * @param  string $key
     * @param  string $default
     * @return string
     */
    public function getFlash(string $key, string $default = '') : string
    {
        $value = $default;

        if (isset($_SESSION[self::getKey('flash.' . $key)])) {
            $value = strval(self::get('flash.' . $key, $default));
            self::remove('flash.' . $key);
        }

        return $value;
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

                // Returns only our object; the ones starting with
                // our prefix
                $arr[$key] = $value;
            }
        }

        return $arr;
    }

    /**
     * Remove a key from the Session collection
     *
     * @param  string $key Key to remove
     * @return void
     */
    public function remove(string $key)
    {
        if (isset($_SESSION[self::getKey($key)])) {
            unset($_SESSION[self::getKey($key)]);
        }
    }

    /**
     * Gets the id for the current session.
     *
     * @return string - session id (not an integer since also contains letters)
     */
    public function getSessionId() : string
    {
        return strval(self::get('session_id'));
    }

    /**
     * Extends the current session
     */
    public function extends()
    {
        self::set('session_validUntil', self::validUntil());
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
     * Calculate the end validity time of the session
     * (i.e. probably now() + 15 minutes which is the
     * default duration for a session (see $this->duration))
     *
     * @return int timestamp
     */
    private function validUntil() : int
    {
        return time() + ($this->duration * 60);
    }
}
