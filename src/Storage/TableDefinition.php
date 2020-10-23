<?php

namespace LokiDb\Storage;

use LokiDb\Exception\RunTimeException;

/**
 * Class TableDefinition
 * @package LokiDb\Storage
 */
class TableDefinition
{

    /** @var string */
    private $tableName;

    /** @var array */
    private $indices = [];

    /** @var array  */
    private $fieldDefinitions = [];

    /**
     * TableDefinition constructor.
     * @param string $tableName
     * @param array $indeces
     */
    public function __construct($tableName, array $indices)
    {
        $this->tableName = $tableName;
        $this->indices = $indices;
        $foundPrimary = false;

        foreach ($this->indices as $index)
        {
            if(isset($index['primary']) && $index['primary'])
            {
                $foundPrimary = true;
                break;
            }
        }
        if(!$foundPrimary)
        {
            throw new RunTimeException('The table "'  .$tableName . '" need to define at least one primary key.');
        }

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

    /**
     * @return array
     */
    public function getIndices()
    {
        return $this->indices;
    }

}