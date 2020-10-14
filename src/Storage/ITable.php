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
    public function getUId() : string;

    /**
     * @param array $data
     */
    public function setDataRow(array $data) : void;

    /**
     * @return array
     */
    public function getDataRow() : array;


    /**
     * @param callable $callback
     * @param array|null $filter
     */
    public function fetch(callable $callback, array $filter = null) : void;

    /**
     *
     */
    public function eof() : void;

    /**
     * @return array
     */
    public function getFields() : array;

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