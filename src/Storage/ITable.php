<?php

namespace LokiDb\Storage;

/**
 * Interface ITable
 * @package LokiDb\Table
 */
interface ITable
{

    /**
     * @param $tableName
     * @return ITable
     */
    static public function create($tableName) : ITable;

    /**
     * @return string
     */
    public function getHash() : string;

    /**
     * @param array[FieldDefinition] $fieldDefinitions
     * @return mixed
     */
    public function addDefinition(array $fieldDefinitions);

    /**
     * @param string $databaseFolder
     * @return mixed
     */
    public function connectToDisk($databaseFolder);

}