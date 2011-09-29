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

	private $connection = NULL;

	public function __construct($connection = NULL)
	{
		if ($connection == NULL)
		{
			$connection = self::DEFAULT_CONNECTION;
		}
		$this->set_connection($connection);
	}

	public function migrate_to($version, $rebuild)
	{
		$migrations = array();
		$direction = NULL;
		$schema_version = $this->get_schema_version();

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
		$migrations_path_handle = opendir(self::MIGRATIONS_PATH);
		while (($filename = readdir($migrations_path_handle)) !== FALSE)
		{
			$regex_pattern = '/([0-9\.]+)\-' . $direction . '\.sql/';
			$matches = array();
			if (preg_match($regex_pattern, $filename, $matches))
			{
				$migration_version = $matches[1];
			}

			if ($direction == self::DIRECTION_UP)
			{
				if ($migration_version > $schema_version && $migration_version <= $app_version)
				{
					$migrations[] = $migration_version;
				}
			}
			else
			{
				if ($migration_version < $schema_version && $migration_version >= $app_version)
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
			$patch_file_path = $patches_dir . DS . $migration . $file_extension;
			$sql = file_get_contents($patch_file_path);

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
					return FALSE;
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

}