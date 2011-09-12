<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author jon
 */
interface SchemaVersionProvider
{
    /**
     * @return float
     */
    public function get_schema_version();
}
