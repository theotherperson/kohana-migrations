<?php defined('SYSPATH') or die('No direct script access.');


class Model_Migration_Log_Core extends ORM
{

	protected $_table_name = 'migration_log';

    public function get_current_schema_version()
	{
		$schema_version = '0';

		$sql = "SELECT to_version
				FROM ".$this->_table_name."
				WHERE status = :status_success
				ORDER BY time DESC
				LIMIT 1";

		$results = DB::query(Database::SELECT, $sql)
			->bind(':table', $this->_table_name)
			->param(':status_success', Model_Migration::STATUS_SUCCESS)
			->execute();

		foreach($results as $row)
		{
			$schema_version = $row['to_version'];
		}

		return $schema_version;
	}

	public function add_entry($from_version, $to_version, $status)
	{
		$this->from_version = $from_version;
		$this->to_version = $to_version;
		$this->status = $status;
		$this->time = date('Y-m-d h:i:s');

		$this->save();
	}

}