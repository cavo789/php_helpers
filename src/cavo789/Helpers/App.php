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
 * Reusable in other projects
 */

declare(strict_types=1);

namespace cavo789\Helpers;

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

use cavo789\Helpers\Files as Files;

class App
{
	/**
	 * Depending on the $debug parameter, enable / disable the
	 * PHP error mode i.e. show extra infos (enable) or don't
	 * show at all (disable)
	 *
	 * Errors will be echoed in a logfile stored in the $folder
	 * folder, f.i.
	 * 		__DIR__.'error.log'
	 *
	 * @param  boolean $debug  False will hide errors in the browser
	 *                         True will activate a verbose mode
	 * @param  string  $folder Absolute folder name where to create
	 *                         the error.log file
	 * @return void
	 */
	public static function initDebugMode(bool $debug = false, string $folder = __DIR__)
	{
		// Check if the folder exists and if not, create it
		// Create the folder if needed

		$path = rtrim($folder, DS) . DS;

		if (!file_exists($path)) {
			Files::makeFolder($path, true);
		}

		ini_set('error_log', $path . 'error.log');

		if ($debug === true) {
			ini_set('display_errors', '1');
			ini_set('display_startup_errors', '1');
			ini_set('html_errors', '1');
			ini_set('docref_root', 'http://www.php.net/');

			ini_set('error_prepend_string', "<div style='color:red; font-family:verdana; border:1px solid red; padding:5px;'>");
			ini_set('error_append_string', '</div>');
			error_reporting(E_ALL);
		} else {
			error_reporting(E_ALL & ~E_NOTICE);
		}
	}
}
