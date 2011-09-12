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
    public function action_migrate()
    {
        // only accessible via the command line
        if(!Kohana::$is_cli)
        {
           throw new HTTP_Exception_403('Access via CLI only');
        }

        // get the parameters
        $to_version = $this->request->param('to_version', 'auto');

        $connection = $this->request->param('connection', null);

        $rebuild = FALSE;
        if($this->request->param('rebuild') == 'rebuild')
        {
            $rebuild = TRUE;
        }

        $migration = new Migration($connection);
        $migration->set_observer($this->get_migrationObserver());
        $migration->set_schemaVersionProvider($this->get_schemaVersionProvider());
        if($to_version == 'auto')
        {
            $migration->set_appVersionProvider($this->get_appVersionProvider());
            $migration->auto_migrate($rebuild);
        }
        else
        {
            $migration->migrate_to($to_version, $rebuild);
        }
    }

    /**
     * @abstract
     * @return SchemaVersionProvider
     */
    abstract protected function get_schemaVersionProvider();

    /**
     * @abstract
     * @return AppVersionProvider
     */
    abstract protected function get_appVersionProvider();

    /**
     * @abstract
     * @return MigrationObserver OR null
     */
    abstract protected function get_migrationObserver();

}