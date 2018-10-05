<?php

declare(strict_types=1);

/**
 * Christophe Avonture
 * Written date : 2018-09-13
 *
 * Description
 * cavo789\Class\App aimed to provide features for
 * working with the application like enabling or not a debug mode
 *
 * This class implements the LoggerInterface and, thus, expose the methods
 * for writing an information into a log file (thanks to Monolog).
 *
 * Because this class can be instantiated in more than one script
 * of the same application, the class is a Singleton: only one instance
 * will be instantiated and loaded into memory.
 *
 * How to:
 * 		use \cavo789\Classes\App as App;
 * 		$app = App::getInstance(true, ['folder' => __DIR__.'/logs']);
 * 		$app->debug('This is a debug message');
 * 		$app->info('This is a information');
 *
 * Require monolog/monolog
 */

namespace cavo789\Classes;

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

use \cavo789\Helpers\Files as Files;
use \Psr\Log\LoggerInterface as LoggerInterface;
use \Monolog\Logger as Logger;
use \Monolog\Handler\StreamHandler as StreamHandler;
use \Monolog\Formatter\LineFormatter as LineFormatter;

// Each line outputted in the log file will respect this template
// @link https://github.com/Seldaek/monolog/blob/master/doc/message-structure.md
define('DEBUG_TEMPLATE', '[%datetime%] [%level_name%] %message% %context%');

// How to display dates in the log
define('DEBUG_DATE', 'Y-m-d H:i:s');

// In order to get the correct timezone
define('DEBUG_TIMEZONE', 'Europe/Brussels');

// When a line is written in the log, we can capture the trace
// i.e. all information's on which function was written in the log
// (class name, function name, line, arguments, ...) and we can do
// this on more than one "caller" (who has called the function that ...)
// Note: it's a zero based value !!!
// 0 ==> only the one caller will be outputted in the log (usefull less)
// 1 ==> the function who called the log function will also ... (two callers)
// 2 ==> three callers will be outputted and so on
// (a value like f.i. 99 means no max since you'll get up to 99 callers)
// -1 ==> No caller should be outputted
define('DEBUG_TRACE_DEEP', -1);

// Remove the previous file ? True means that the log file
// will be removed on each start of the calling php script
define('DEBUG_DELETE_PREVIOUS', false);

class App implements LoggerInterface
{
	// Log filename for application debug message
	private $app_log = 'application.log';

	// Log filename for errors occurs when the script was running
	private $error_log = 'error.log';

	// The log object
	private $log = null;

	// Folder when the application.log will be stored
	private $folder = '';

	// Root folder of the application
	private $root = '';

	// Debugging mode state (On / Off)
	private $debugMode = false;

	// Monolog handler
	private $log_handler = null;

	private $trace_deep = 0;

	/**
	 * @var App
	 * @access private
	 * @static
	 */
	private static $_instance = null;

	/**
	 * Private function: please use App::getInstance() instead
	 *
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
	 *                      'root'		= Root folder of the application (default: __DIR__)
	 *                      'folder'		= Absolute folder name where to create the logfile (default: __DIR__)
	 *                      'prefix'		= Prefix to use for entries in the logfile (default: 'APP')
	 *                      'timezone' 	= Timezone to get the correct date/time info (default: 'Europe/Brussels')
	 *                      'dateFormat' = Date format (default: DEBUG_DATE)
	 *                      'trace_deep' = How many callers should be taken in each log entry? (default: DEBUG_TRACE_DEEP)
	 * @return void
	 */
	private function __construct(
		bool $debugMode = false,
		array $extra = []
	) {
		$this->folder = rtrim(($extra['folder'] ?? __DIR__), DS) . DS;

		// Get the application root folder and be sure it's ending by
		// a /
		$this->root = $extra['root'] ?? __DIR__;
		$this->root = rtrim(str_replace('/', DS, $this->root), DS) . DS;

		$this->trace_deep = intval($extra['trace_deep'] ?? DEBUG_TRACE_DEEP);

		// Check if the folder exists and if not, create it
		// Create the folder if needed
		if (!file_exists($this->folder)) {
			Files::makeFolder($this->folder, true);
		}

		// Informs PHP where to store errors
		ini_set('error_log', $this->folder . $this->error_log);

		// create a log channel (by using monolog)
		$this->log = new Logger($extra['prefix'] ?? 'APP');

		// In debug mode send everything to the log i.e debug, info, notice, warning,
		// error, critical, alert and emergency.
		// In non debug mode, error and above (error, critical, alert and emergency)
		$level = ($debugMode ? Logger::DEBUG : Logger::ERROR);

		// Store information's into the /Logs/application.log file

		if (DEBUG_DELETE_PREVIOUS) {
			if (file_exists($log = $this->folder . $this->app_log)) {
				try {
					unlink($log);
				} catch (\Exception $e) {
					throw new \Exception(sprintf('The file %s can\'t be removed', $log), 0, $e);
				}
			}
		}
		$this->log_handler = new StreamHandler($this->folder . $this->app_log, $level);

		// How a line in the log should looks like
		$formatter = new LineFormatter(DEBUG_TEMPLATE . "\n", $extra['dateFormat'] ?? DEBUG_DATE);
		$this->log_handler->setFormatter($formatter);

		// Use the handler
		$this->log->pushHandler($this->log_handler);

		// Set timezone and date to match our configuration
		$this->log->setTimezone(new \DateTimeZone($extra['timezone'] ?? DEBUG_TIMEZONE));

		// Initialize the debug mode
		self::setDebugMode($debugMode);

		// Add context's information's in the log
		self::logContext();
	}

	/**
	 * This class should be loaded only once so if called
	 * in several PHP scripts, we need to avoid to load the
	 * class again and again. For this, use getInstance() to
	 * load a Singleton and return the $_instance pointer as
	 * from the second class
	 *
	 * @param boolean $debugMode False will hide errors in the browser
	 *                           True will activate a verbose mode
	 *
	 * @param array $extra
	 *                     'root'		= Root folder of the application (default: __DIR__)
	 *                     'folder'		= Absolute folder name where to create the logfile (default: __DIR__)
	 *                     'prefix'		= Prefix to use for entries in the logfile (default: 'APP')
	 *                     'timezone' 	= Timezone to get the correct date/time info (default: 'Europe/Brussels')
	 *                     'dateFormat' = Date format (default: DEBUG_DATE)
	 *                     'trace_deep' = How many callers should be taken in each log entry? (default: DEBUG_TRACE_DEEP)
	 *
	 * @return App
	 */
	public static function getInstance(
		bool $debugMode = false,
		array $extra = []
	) : App {
		if (is_null(self::$_instance)) {
			self::$_instance = new App($debugMode, $extra);
		}

		return self::$_instance;
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
		self::info(
			'Debug mode is ' . ($this->debugMode
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
	 * Return true / false depending on the state of the debug mode flag
	 *
	 * @return boolean
	 */
	public function getDebugMode() : bool
	{
		return $this->debugMode;
	}

	/**
	 * Output in the application log information's about the context
	 * of the current script
	 *
	 * @return void
	 */
	private function logContext()
	{
		// Record the used path_info; used when calling an API like
		// "index.php/stats/surveys_count"
		if (isset($_SERVER['PATH_INFO'])) {
			if (trim($_SERVER['PATH_INFO']) !== '') {
				self::info('PATH_INFO: ' . $_SERVER['PATH_INFO']);
			}
		}

		// And get the query string if there is one
		if (isset($_SERVER['QUERY_STRING'])) {
			if (trim($_SERVER['QUERY_STRING']) !== '') {
				self::info('QUERY_STRING: ' . $_SERVER['QUERY_STRING']);
			}
		}
	}

	/**
	 * Adds a log record at an arbitrary level.
	 *
	 * This method allows for compatibility with common interfaces.
	 *
	 * @suppress PhanUnusedVariableCaughtException
	 *
	 * @param mixed  $level   The log level
	 * @param string $message The log message
	 * @param array  $context The log context
	 */
	public function log($level, $message, array $context = [])
	{
		if ($this->trace_deep > -1) {
			$trace = debug_backtrace();

			$j = (count($trace) > $this->trace_deep) ? $this->trace_deep : count($trace);

			try {
				$arrTrace = [];
				for ($i = 0; $i <= $j; $i++) {
					if (!isset($trace[$i])) {
						// No more items in the trace, exit the loop
						break;
					}

					$trace[$i]['file'] = str_ireplace($this->root, '', $trace[$i]['file']);

					// These entries aren't needed; try to minimize the
					// records otherwise the file will be too big to be
					// analyzed
					unset($trace[$i]['object']);
					unset($trace[$i]['type']);
					$arrTrace[$i] = $trace[$i];
				}
			} catch (\Exception $e) {
			}

			$context['trace'] = $arrTrace;
		}

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
		self::log(LOGGER::DEBUG, (string) $message, $context);
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
		self::log(LOGGER::INFO, (string) $message, $context);
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
		self::log(LOGGER::NOTICE, (string) $message, $context);
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
		self::log(LOGGER::WARNING, (string) $message, $context);
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
		self::log(LOGGER::ERROR, (string) $message, $context);
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
		self::log(LOGGER::CRITICAL, (string) $message, $context);
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
		self::log(LOGGER::ALERT, (string) $message, $context);
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
		self::log(LOGGER::EMERGENCY, (string) $message, $context);
	}
}
