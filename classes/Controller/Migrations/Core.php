<?php

/**
 *
 *
 *
 */
abstract class Controller_Migrations_Core extends Controller
{

	/**
	 *
	 *
	 *
	 */
	public function action_migrate() {
		// only accessible via the command line
		if (!Kohana::$is_cli)
		{
			throw new HTTP_Exception_403('Access via CLI only');
		}

        // try and ensure the cache and log dirs are writeable
        @chmod(APPPATH.'/cache', 0777);
        @chmod(APPPATH.'/logs', 0777);

		// get the parameters
		$connection = $this->request->param('connection', NULL);
		$to_version = $this->request->param('to_version', NULL);
		$from_version = $this->request->param('from_version', NULL);

		$migration = new Model_Migration($connection);

		if($from_version === NULL)
		{
			$from_version = $this->get_schema_version();
		}
		$migration->set_schema_version($from_version);

		if ($to_version === NULL)
		{
			$to_version = $this->get_app_version();
		}

		if($migration->migrate_to($to_version))
		{
			$this->after_migrate($migration);
		}
	}

	/**
	 * @abstract
	 * @return string Schema version number
	 */
	abstract protected function get_schema_version();

	/**
	 * @abstract
	 * @return string App version number
	 */
	abstract protected function get_app_version();

	/**
	 * after migrate is called after each migration, this method definition
	 * contains no logic and should be overriden in each
	 * application's implementation of migrations.
	 *
	 * @param	Model_Migration	The migration that has just run
	 * @return	string						App version number
	 */
	protected function after_migrate(Model_Migration $migration)
	{

	}

}