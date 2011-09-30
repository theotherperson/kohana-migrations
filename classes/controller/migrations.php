<?php defined('SYSPATH') OR die('No Direct Script Access');

/**
 *
 *
 *
 */
abstract class Controller_Migrations extends Controller
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

		// get the parameters
		$to_version = $this->request->param('to_version', NULL);

		$connection = $this->request->param('connection', NULL);

		$rebuild = FALSE;
		if ($this->request->param('rebuild') == 'rebuild')
		{
			$rebuild = TRUE;
		}

		$migration = new Migration($connection);
		$migration->set_schema_version($this->get_schema_version());
		if ($to_version === NULL)
		{
			$to_version = $this->get_app_version();
		}

		if($migration->migrate_to($to_version, $rebuild))
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
	 * @param	Migration	The migration that has just run
	 * @return	string		App version number
	 */
	protected function after_migrate(Migration $migration)
	{

	}

}