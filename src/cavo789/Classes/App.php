<?php

/**
 * Christophe Avonture
 * Written date : 2018-09-13
 * Last modified:
 *
 * Generic helper functions.
 *
 * cavo789\Helpers\App aimed to provide features for
 * working with the application like enabling or not a debug mode
 *
 * This class implements the LoggerInterface and, thus, expose the methods
 * for writing an information into a log file (thanks to Monolog).
 *
 * How to:
 * 		use \cavo789\Classes\App as App;
 * 		$app = new App(true, ['folder' => __DIR__.'/logs']);
 * 		$app->setDebugMode(true);
 * 		$app->debug('This is a debug message');
 * 		$app->info('This is a information');
 *
 * Require monolog/monolog
 *
 * Reusable in other projects
 */

declare(strict_types=1);

namespace cavo789\Classes;

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

use cavo789\Helpers\Files as Files;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class App implements LoggerInterface
{
	private $app_log = 'application.log';	// Logfile name for application debug message
	private $error_log = 'error.log';		// Logfile name for errors occured when the script was running
	private $log = null;							// The log object
	private $folder = '';						// Folder when the application.log will be stored
	private $debugMode = false;				// Debugging mode state (On / Off)

	private $log_handler = null;

	/**
	 * Depending on the $debug parameter, enable / disable the
	 * PHP error mode i.e. show extra infos (enable) or don't
	 * show at all (disable)
	 *
	 * Errors will be echoed in a logfile stored in the $folder
	 * folder, f.i.
	 * 		__DIR__.'error.log'
	 *
	 * @param boolean $debugMode False will hide errors in the browser
	 *                           True will activate a verbose mode
	 *
	 * @param  array $extra
	 *                      'folder'		= Absolute folder name where to create the logfile (default: __DIR__)
	 *                      'prefix'		= Prefix to use for entries in the logfile (default: 'APP')
	 *                      'timezone' 	= Timezone to get the correct date/time info (default: 'Europe/Brussels')
	 *                      'dateFormat' = Date format (default: 'd M Y H:i:s')
	 * @return void
	 */
	public function __construct(
		bool $debugMode = false,
		array $extra = []
	) {
		$this->folder = rtrim(($extra['folder'] ?? __DIR__), DS) . DS;

		// Check if the folder exists and if not, create it
		// Create the folder if needed
		if (!file_exists($this->folder)) {
			Files::makeFolder($this->folder, true);
		}

		// Set timezone and date to match our configuration
		date_default_timezone_set($extra['timezone'] ?? 'Europe/Brussels');
		$Date = date($extra['dateFormat'] ?? 'd M Y H:i:s');

		ini_set('error_log', $this->folder . $this->error_log);

		// create a log channel (by using monolog)
		$this->log = new Logger($extra['prefix'] ?? 'APP');

		// In debug mode send everything to the log i.e debug, info, notice, warning,
		// error, critical, alert and emergency.
		// In non debug mode, error and above (error, critical, alert and emergency)
		$level = ($debugMode ? Logger::DEBUG : Logger::ERROR);

		// Store information's into the /Logs/application.log file
		$this->log_handler = new StreamHandler($this->folder . $this->app_log, $level);
		$this->log->pushHandler($this->log_handler);

		self::setDebugMode($debugMode);

		// Add context's information's in the log
		self::logContext();
	}

	/**
	 * Set the debugging mode
	 *
	 * @param  boolean $onOff
	 * @return void
	 */
	public function setDebugMode(bool $onOff = false)
	{
		$this->debugMode = $onOff;

		// add records to the log
		$this->log->info(
			'Debug mode is ' .
			($this->debugMode
				? 'ON, output all levels'
				: 'OFF, output only Error, Critical, Alert and Emergency')
		);

		$this->log_handler->setLevel($this->debugMode ? Logger::DEBUG : Logger::ERROR);

		// When debug mode is on, we want to see every messages; even notice.
		if ($this->debugMode === true) {
			ini_set('display_errors', '1');
			ini_set('display_startup_errors', '1');
			ini_set('html_errors', '1');
			ini_set('docref_root', 'http://www.php.net/');

			ini_set('error_prepend_string', "<div style='color:red; font-family:verdana;" .
				"border:1px solid red; padding:5px;'>");
			ini_set('error_append_string', '</div>');
			error_reporting(E_ALL);
		} else {
			error_reporting(E_ALL & ~E_NOTICE);
		}
	}

	/**
	 * Output in the application log information's about the context
	 * of the current script
	 *
	 * @return void
	 */
	private function logContext()
	{
		self::info($_SERVER['QUERY_STRING'] ?? 'Query string empty');
	}

	/**
	 * Adds a log record at an arbitrary level.
	 *
	 * This method allows for compatibility with common interfaces.
	 *
	 * @param mixed  $level   The log level
	 * @param string $message The log message
	 * @param array  $context The log context
	 */
	public function log($level, $message, array $context = [])
	{
		$this->log->log($level, $message, $context);
	}

	/**
	 * Adds a log record at the DEBUG level.
	 *
	 * This method allows for compatibility with common interfaces.
	 *
	 * @param string $message The log message
	 * @param array  $context The log context
	 */
	public function debug($message, array $context = [])
	{
		$this->log->debug((string) $message, $context);
	}

	/**
	 * Adds a log record at the INFO level.
	 *
	 * This method allows for compatibility with common interfaces.
	 *
	 * @param string $message The log message
	 * @param array  $context The log context
	 */
	public function info($message, array $context = [])
	{
		$this->log->info((string) $message, $context);
	}

	/**
	 * Adds a log record at the NOTICE level.
	 *
	 * This method allows for compatibility with common interfaces.
	 *
	 * @param string $message The log message
	 * @param array  $context The log context
	 */
	public function notice($message, array $context = [])
	{
		$this->log->notice((string) $message, $context);
	}

	/**
	 * Adds a log record at the WARNING level.
	 *
	 * This method allows for compatibility with common interfaces.
	 *
	 * @param string $message The log message
	 * @param array  $context The log context
	 */
	public function warning($message, array $context = [])
	{
		$this->log->warning((string) $message, $context);
	}

	/**
	 * Adds a log record at the ERROR level.
	 *
	 * This method allows for compatibility with common interfaces.
	 *
	 * @param string $message The log message
	 * @param array  $context The log context
	 */
	public function error($message, array $context = [])
	{
		$this->log->error((string) $message, $context);
	}

	/**
	 * Adds a log record at the CRITICAL level.
	 *
	 * This method allows for compatibility with common interfaces.
	 *
	 * @param string $message The log message
	 * @param array  $context The log context
	 */
	public function critical($message, array $context = [])
	{
		$this->log->critical((string) $message, $context);
	}

	/**
	 * Adds a log record at the ALERT level.
	 *
	 * This method allows for compatibility with common interfaces.
	 *
	 * @param string $message The log message
	 * @param array  $context The log context
	 */
	public function alert($message, array $context = [])
	{
		$this->log->alert((string) $message, $context);
	}

	/**
	 * Adds a log record at the EMERGENCY level.
	 *
	 * This method allows for compatibility with common interfaces.
	 *
	 * @param string $message The log message
	 * @param array  $context The log context
	 */
	public function emergency($message, array $context = [])
	{
		$this->log->emergency((string) $message, $context);
	}
}
