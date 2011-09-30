<?php defined('SYSPATH') OR die('No Direct Script Access');

/**
 * Description of migration
 *
 * @author jon
 */
class Migration
{
	const DEFAULT_CONNECTION = 'default';
	const MIGRATIONS_PATH = 'data/migrations';
	const DIRECTION_UP = 'up';
	const DIRECTION_DOWN = 'down';

	const STATUS_SUCCESS = 'success';
	const STSTUS_FAILED = 'failed';

	private $connection = NULL;
	private $schema_version = NULL;
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
	public function migrate_to($version, $rebuild)
	{
		$migrations = array();
		$direction = NULL;
		$schema_version = $this->get_schema_version();
		$file_extension = '.sql';

		if ($version === NULL)
		{
			throw new Exception('Version not set');
		}

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
		 * scan the migrations directory and add any migration files newer
		 * than the the current schema version to an array
		 */
		$migrations_path = APPPATH.'..'.DIRECTORY_SEPARATOR.self::MIGRATIONS_PATH;
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
					$migrations[] = $migration_version;
				}
			}
			else
			{
				if ($migration_version <= $schema_version && $migration_version > $version)
				{
					$migrations[] = $migration_version;
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
			asort($migrations);
		}
		else
		{
			arsort($migrations);
		}

		foreach ($migrations as $migration)
		{
			$migration_file_path = $migrations_path.DIRECTORY_SEPARATOR.$migration.'-'.$direction.$file_extension;
			$sql = file_get_contents($migration_file_path);

			// split the sql into statements by using ';'
			$sql_statements = explode(';', $sql);

			// remove the last element as it will always be empty
			array_pop($sql_statements);

			// run each statement one by one
			foreach ($sql_statements as $sql_statement)
			{
				try
				{
					DB::query(NULL, $sql_statement)->execute($this->get_connection());
				}
				catch (Exception $e)
				{
					$this->set_status(self::STATUS_FAILED);
					return TRUE;
				}
			}
		}

		$this->set_status(self::STATUS_SUCCESS);
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