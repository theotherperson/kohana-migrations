<?php

/**
 * Description of migration
 *
 * @author jon
 */
class Model_Migration_Core
{
	const DEFAULT_CONNECTION = 'default';
	const MIGRATIONS_PATH = 'data/migrations';
	const DIRECTION_UP = 'up';
	const DIRECTION_DOWN = 'down';
	const MODULE_NAME = 'migrations';

	const STATUS_SUCCESS = 'success';
	const STATUS_FAILED = 'failed';

	private $connection = NULL;
	private $schema_version = NULL;
	private $to_version = NULL;
	private $status = NULL;

	public function __construct($connection = NULL)
	{
		if ($connection == NULL)
		{
			$connection = self::DEFAULT_CONNECTION;
		}
		$this->set_connection($connection);
	}

	/**
	 *
	 * @param type $version
	 * @param type $rebuild
	 * @return	bool	true if a migration task was attempted regardless of outcome, otherwise false
	 */
	public function migrate_to($version, $schema_version = NULL)
	{
		$migrations = array();
		$direction = NULL;
		$file_extension = '.sql';

		if($schema_version === NULL)
		{
			$schema_version = $this->get_schema_version();
		}

		if ($version === NULL)
		{
			throw new Exception('Version not set');
		}
		$this->set_to_version($version);

		if ($schema_version === NULL)
		{
			throw new Exception('Schema version not set');
		}

		/**
		 * Work out the direction in which we are migrating
		 * UP = applying schema updates
		 * DOWN = rolling back schema changes
		 */
		if ($version > $schema_version)
		{
			$direction = self::DIRECTION_UP;
		}
		elseif ($version < $schema_version)
		{
			$direction = self::DIRECTION_DOWN;
		}
		else
		{
			// versions must be equal, no migrations required
			return FALSE;
		}

		/**
		 * first, scan the module's migrations directory and then the app's migrations directory and
		 * add any migration files newer/older than the the current schema version to an array
		 */
		$module_migrations_path = MODPATH.DIRECTORY_SEPARATOR.self::MODULE_NAME.DIRECTORY_SEPARATOR.self::MIGRATIONS_PATH;
		$app_migrations_path = APPPATH.'..'.DIRECTORY_SEPARATOR.self::MIGRATIONS_PATH;
		$migrations_paths = array($module_migrations_path, $app_migrations_path);

		foreach($migrations_paths as $migrations_path)
		{
			$migrations_path_handle = opendir($migrations_path);
			while (($filename = readdir($migrations_path_handle)) !== FALSE)
			{
				$regex_pattern = '/([0-9\.]+)\-'.$direction.'\\'.$file_extension.'/';
				$matches = array();
				$migration_version = NULL;
				if (preg_match($regex_pattern, $filename, $matches))
				{
					$migration_version = $matches[1];
				}

				if($migration_version === NULL)
				{
					// the filename does not match the expected pattern so move on to the next file
					continue;
				}

				if ($direction == self::DIRECTION_UP)
				{
					if ($migration_version > $schema_version && $migration_version <= $version)
					{
						$migrations[$migration_version] = $migrations_path.DIRECTORY_SEPARATOR.$filename;
					}
				}
				else
				{
					if ($migration_version <= $schema_version && $migration_version > $version)
					{
						$migrations[$migration_version] = $migrations_path.DIRECTORY_SEPARATOR.$filename;
					}
				}
			}
		}

		if (count($migrations) < 1)
		{
			// no migrations to apply
			return FALSE;
		}

		// run each migration in the correct order
		if ($direction == self::DIRECTION_UP)
		{
			ksort($migrations);
		}
		else
		{
			krsort($migrations);
		}

		foreach ($migrations as $version => $migration)
		{
			$sql = file_get_contents($migration);

			// split the sql into statements by using ';'
			$sql_statements = explode(';', $sql);

			// remove the last element as it will always be empty
			array_pop($sql_statements);

			/**
			 * mark as a successfull migrtion here, it will be marked as failed
			 * later if something bad happens
			 */
			$this->set_status(self::STATUS_SUCCESS);

			// run each statement one by one
			foreach ($sql_statements as $sql_statement)
			{
				try
				{
					DB::query(NULL, $sql_statement)->execute($this->get_connection());
				}
				catch (Exception $e)
				{
					/**
					 * if we're currently executing an 'UP' migration we need to
					 * stop and trigger the equivelent down migration to undo
					 * what has happened so far, if this is a 'DOWN' migration
					 * we should just ignore the error and carry on as no damage
					 * can be done when removing/changing things that don't yet
					 * exist or by attempting to add things that already do
					 * (that's the theory anyway!)
					 */
					if ($direction == self::DIRECTION_UP)
					{
						echo "Migration Failed, attempting to undo migration.\n";
						echo $e->getMessage()."\n";

						// go back down to the version we just came from
						$this->migrate_to($schema_version, $version);
						$this->set_to_version($version);
						$this->set_schema_version($schema_version);
						$this->set_status(self::STATUS_FAILED);
						return TRUE;
					}
					else
					{
						// going down, ignore errors and move on to the next statement
						echo $e->getMessage()."\n";
						$this->set_status(self::STATUS_FAILED);
					}
				}
			}
		}

		return TRUE;
	}

	/**
	 * Getter for connection property
	 *
	 * @return	mixed	Database instance or name of instance
	 */
	public function get_connection()
	{
		return $this->connection;
	}

	/**
	 * Setter for connection property
	 *
	 * @param	mixed	$connection		Database instance or name of instance
	 */
	public function set_connection($connection)
	{
		$this->connection = $connection;
	}

	/**
	 *
	 * @return string
	 */
	public function get_schema_version() {
		return $this->schema_version;
	}

	/**
	 *
	 * @param string $schema_version
	 */
	public function set_schema_version($schema_version) {
		$this->schema_version = $schema_version;
	}

	/**
	 *
	 * @return string
	 */
	public function get_to_version() {
		return $this->to_version;
	}

	/**
	 *
	 * @param string $to_version
	 */
	public function set_to_version($to_version) {
		$this->to_version = $to_version;
	}

	/**
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 *
	 * @param string $status
	 */
	public function set_status($status) {
		$this->status = $status;
	}

}