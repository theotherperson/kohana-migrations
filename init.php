<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Migrations init.php
 * Initialises the Migrations module by loading in any config settings and
 * checking that all dependencies have been loaded.
 *
 * @author		Jon Cotton <jon@rpacode.co.uk>
 * @copyright	(c) 2011 RPA Code
 * @version		1.0
 */

// migrations route
Route::set('migrations', 'migrate(/<connection>(/<to_version>(/<from_version>)))',
	array(
		'to_version' => '[0-9\.]+',
		'from_version' => '[0-9\.]+'
	)
)->defaults(
	array(
		'controller' => 'migrations',
		'action' => 'migrate',
		'connection' => NULL,
		'to_version' => NULL,
		'from_version' => NULL
	)
);