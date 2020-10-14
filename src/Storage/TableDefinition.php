<?php

namespace LokiDb\Storage;

/**
 * Class TableDefinition
 * @package LokiDb\Storage
 */
class TableDefinition
{

    /** @var string */
    private $tableName;

    /** @var array */
    private $primaryKeys = [];

    /**
     * @var array
     */
    private $fieldDefinitions = [];

    /**
     * TableDefinition constructor.
     * @param string $tableName
     * @param array $primaryKeys
     */
    public function __construct($tableName, array $primaryKeys)
    {
        $this->tableName = $tableName;
        $this->primaryKeys = $primaryKeys;
    }

    /**
     * @param string $name
     * @param int $dateType
     * @param int $byteLength
     * @return $this
     * @throws \Exception
     */
    public function addField($name, $dateType, $byteLength = 1) : TableDefinition
    {
        $this->fieldDefinitions[] = new FieldDefinition(
            $name,
            $dateType,
            $byteLength
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->tableName;
    }

    /**
     * @return array
     */
    public function getFieldDefinitions() : array
    {
        return $this->fieldDefinitions;
    }


}