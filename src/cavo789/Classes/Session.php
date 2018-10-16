<?php

declare(strict_types=1);

/**
 * Christophe Avonture
 * Written date: 2018-09-13.
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
use cavo789\Exception\SessionException as SessionException;

class Session
{
    /**
     * Prefix to add for each session's key.
     *
     * @var string
     */
    private $keyPrefix = '';

    /**
     * Default duration for a session (15 minutes).
     *
     * @var int
     */
    private $duration = 15;

    /**
     * @var Session
     * @access private
     * @static
     */
    private static $instance = null;

    /**
     * Constructor.
     *
     * @param string $keyPrefix Keys added by this class
     *                          can have a prefix to make sure that there will
     *                          be no conflict with other running script.
     *                          Specify f.i. "MySuperSoft_" to have all your keys
     *                          prefixed with that pattern.
     */
    private function __construct(string $keyPrefix = '')
    {
        $this->keyPrefix = trim($keyPrefix) ?? '';

        if (!@session_start()) {
            throw new SessionException('The session can\'t be started');
        } else {
            $_SESSION[$keyPrefix . 'session_id'] = session_id();
        }
    }

    /**
     * Load an instance of the class.
     *
     * @param string $keyPrefix Keys added by this class
     *                          can have a prefix to make sure that there will
     *                          be no conflict with other running script.
     *                          Specify f.i. "MySuperSoft_" to have all your keys
     *                          prefixed with that pattern.
     *
     * @return Session
     */
    public static function getInstance(string $keyPrefix = '') : Session
    {
        if (null == self::$instance) {
            self::$instance = new Session($keyPrefix);
        }

        return self::$instance;
    }

    /**
     * Destroys the session.
     *
     * @return void
     */
    public function destroy()
    {
        try {
            if (session_id()) {
                session_destroy();
            }
            $_SESSION = [];
        } catch (\Exception $e) {
            throw new SessionException('The session can\'t be destroyed', 0, $e);
        }
    }

    /**
     * Register the session.
     *
     * @param int $duration Expressed in minutes, the length of time
     *                      during which the session will be considered valid
     *
     * @return void
     */
    public function register(int $duration = 15)
    {
        $this->duration = $duration;
        self::setInt('session_validUntil', self::validUntil());
    }

    /**
     * Checks if the session has been registered. When the session
     * has expired, this function will return False too.
     *
     * @return bool True if it is, False if not.
     */
    public function isRegistered() : bool
    {
        if (self::isExpired()) {
            return false;
        } else {
            if (trim(strval(self::getString('session_id', ''))) !== '') {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Checks to see if the session is still valid (expired?).
     *
     * @return bool
     */
    public function isExpired()
    {
        if (self::getInt('session_validUntil') < time()) {
            // When the session is expired, destory the session
            // to remove any sensitive information's
            //self::destroy();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Set key/value in session when the value is an integer.
     *
     * @param string $key
     * @param int    $value
     */
    public function setInt(string $key, int $value)
    {
        $_SESSION[self::getKey($key)] = $value;
    }

    /**
     * Set key/value in session when the value is a string.
     *
     * @param string $key
     * @param string $value
     */
    public function setString(string $key, string $value)
    {
        $_SESSION[self::getKey($key)] = $value;
    }

    /**
     * Set key/value in session when value is an array.
     *
     * @param string $key
     * @param array  $value
     */
    public function setArray(string $key, array $value)
    {
        $_SESSION[self::getKey($key)] = $value;
    }

    /**
     * Retrieve an integer value stored in session by key.
     *
     * @param string $key     Entry to retrieve
     * @param int    $default Default value
     *
     * @return int
     */
    public function getInt(string $key, int $default = 0) : int
    {
        return $_SESSION[self::getKey($key)] ?? $default;
    }

    /**
     * Retrieve a string value stored in session by key.
     *
     * @param string $key     Entry to retrieve
     * @param string $default Default value
     *
     * @return string
     */
    public function getString(string $key, string $default = '') : string
    {
        return $_SESSION[self::getKey($key)] ?? $default;
    }

    /**
     * Retrieve an array value stored in session by key.
     *
     * @param string $key     Entry to retrieve
     * @param array  $default Default value
     *
     * @return array
     */
    public function getArray(string $key, array $default = []) : array
    {
        return $_SESSION[self::getKey($key)] ?? $default;
    }

    /**
     * Store a flash message into the Session. On the first call,
     * the message will be removed.
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public function flash(string $key, string $value)
    {
        self::setString('flash.' . $key, $value);
    }

    /**
     * Get a flash message. Once obtained, the message will be removed
     * from the Session variables.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function getFlash(string $key, string $default = '') : string
    {
        $value = $default;

        if (isset($_SESSION[self::getKey('flash.' . $key)])) {
            $value = strval(self::getString('flash.' . $key, $default));
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
            if (Strings::startsWith($key, $this->keyPrefix)) {
                $key = substr($key, strlen($this->keyPrefix));

                // Returns only our object; the ones starting with
                // our prefix
                $arr[$key] = $value;
            }
        }

        return $arr;
    }

    /**
     * Remove a key from the Session collection.
     *
     * @param string $key Key to remove
     *
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
        return strval(self::getString('session_id', ''));
    }

    /**
     * Extends the current session.
     */
    public function extends()
    {
        self::setInt('session_validUntil', self::validUntil());
    }

    /**
     * Simply add the prefix to the requested key.
     *
     * @param string $key
     *
     * @return string
     */
    private function getKey(string $key) : string
    {
        return $this->keyPrefix . $key;
    }

    /**
     * Calculate the end validity time of the session
     * (i.e. probably now() + 15 minutes which is the
     * default duration for a session (see $this->duration)).
     *
     * @return int timestamp
     */
    private function validUntil() : int
    {
        return time() + ($this->duration * 60);
    }
}
