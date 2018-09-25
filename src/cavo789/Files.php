<?php

/**
 * Christophe Avonture
 * Written date : 2018-09-13
 * Last modified:
 *
 * Files and folders generic helper
 * Reusable in other projects
 */

declare(strict_types=1);

namespace cavo789;

class Files
{
	/**
	 * Create a folder and, if requested, add a deny from all
	 * configuration file
	 *
	 * @param  string  $path Full path of the folder to create
	 * @param  boolean $deny Add a .htaccess file with a DENY FROM ALL
	 * @return void
	 */
	public static function makeFolder(string $path, bool $deny = true)
	{
		$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		
		// If not yet present, create the folder
		if (!file_exists($path)) {
			mkdir($path, 0755, true);
		}

		// Then create a .htaccess file to deny direct access to the
		// folder.
		if ($deny) {
			if (file_exists($path)) {
				file_put_contents($path . '.htaccess', 'deny from all');
			}
		}
	}

	/**
	 * Check if a file exists
	 *
	 * @param  string  $name
	 * @return boolean
	 */
	public static function exists(string $name) : bool
	{
		return file_exists($name) ? true : false;
	}

	/**
	 * Sanitize a file/folder name; remove dangerous characters
	 * Allow / and \ to allow to identify a subfolder
	 *
	 * @param  string $name
	 * @return string
	 */
	public static function sanitize(string $name) : string
	{
		return preg_replace('/[^a-zA-Z0-9\-\.\/\\_]/', '', $name);
	}
}
