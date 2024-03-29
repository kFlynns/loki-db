<?php

namespace KFlynns\LokiDb\Storage;

use Generator;
use KFlynns\LokiDb\Query\Condition;

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
     * @return array|null
     */
    public function getDataRow(): ?array;

    /**
     * @param bool $intoVoid
     */
    public function flush($intoVoid = false): void;

    /**
     * @param Condition|null $filter
     */
    public function fetch(Condition $filter = null): Generator;

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
    public function addFieldDefinitions(array $fieldDefinitions);

    /**
     * todo: interfacing Index
     * @param array $indices
     * @return mixed
     */
    public function addIndices(array $indices);

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