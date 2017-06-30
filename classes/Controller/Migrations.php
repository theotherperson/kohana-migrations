<?php

/**
 * Controller_Wbmigrations -
 *
 * @author Jon Cotton <jon@rpacode.co.uk>
 * @package water-babies
 * @since v1.0
 * @copyright (c) 2011 RPA Code
 */
class Controller_Migrations extends Controller_Migrations_Core
{
	/**
	 * @return string Schema version number
	 */
	protected function get_schema_version()
	{
		$schema_version = NULL;

		try
		{
			$migration_log = ORM::factory('migration_log');
			$schema_version = $migration_log->get_current_schema_version();
		}
		catch(Exception $e)
		{
			// table doesn't exist, schema version is 0
			$schema_version = '0';
		}

		/**
		 * if we haven't got a version number it's most likely becuase the
		 * migration_log table is empty, schema is at version 0
		 */
		if($schema_version === NULL)
		{
			$schema_version = '0';
		}

		return $schema_version;
	}

	/**
	 * @return string App version number
	 */
	protected function get_app_version()
	{
		return Kohana::$config->load('application.version');
	}

	/**
	 * implementation of after_migrate method that updates the migration log
	 *
	 * @param Model_Migration $migration the migration object that represents the data
	 * migration task that has just run
	 */
	protected function after_migrate(Model_Migration $migration)
	{
		$migration_log = ORM::factory('migration_log');
		$migration_log->add_entry(
			$migration->get_schema_version(),
			$migration->get_to_version(),
			$migration->get_status()
		);
	}
}