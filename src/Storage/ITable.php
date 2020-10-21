<?php

namespace LokiDb\Storage;

use Generator;
use LokiDb\Query\Condition;

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
     * @param bool $intoVoid
     */
    public function flush($intoVoid = false) : void;

    /**
     * @param Condition|null $filter
     */
    public function fetch(Condition $filter = null) : Generator;

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

    /**
     *
     */
    public function lock() : void;

    /**
     *
     */
    public function unlock() : void;

}