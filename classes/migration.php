<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of migration
 *
 * @author jon
 */
class Migration
{

    const DEFAULT_CONNECTION = 'default';
    const MIGRATIONS_PATH = 'data/migrations';

    public function __construct($connection = NULL)
    {
        if($cinnection == NULL)
        {
            $connection = self::DEFAULT_CONNECTION;
        }
        $this->setConnection($connection);
    }

    public function migrate_to($version, $rebuild)
    {
        $updates = array();
        $file_extension = '.sql';
        $schema_ersion = $this->get_schema_version();

        if($version === NULL)
        {
            throw new Exception('Version not set');
        }

        if($schema_version === NULL)
        {
            throw new Exception('Schema version not set');
        }

        // if we don't need to update return false
        if(!$this->doesRequireUpdate())
        {
            return FALSE;
        }

        // scan the patches directory and add any patch files newer than the the current schema version to an array
        $patchesDirHandle = opendir($patchesDir);
        while (($filename = readdir($patchesDirHandle)) !== FALSE) {
            $fileNameParts = explode($fileExtension, $filename);
            $patchVersion = $fileNameParts[0];
            if($patchVersion > $schemaVersion && $patchVersion <= $appVersion) {
                $updates[] = $patchVersion;
            }
        }

        // run each update in the correct order
        asort($updates);
        foreach($updates as $update) {
            $patchFilePath = $patchesDir . DS . $update . $fileExtension;
            $sql = file_get_contents($patchFilePath);

            // split the sql into staements by using ';'
            $sqlStatements = explode(';', $sql);

            // remove the last element as it will always be empty
            array_pop($sqlStatements);

            // run each statement one by one
            foreach($sqlStatements as $sqlStatement) {
                $this->query(trim($sqlStatement));
            }
        }

        // updates executed successfully, now update the schema version
        $this->updateSchemaVersion($appVersion);

        return true;
     }
}

?>
