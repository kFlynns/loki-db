<?php

namespace KFlynns\LokiDb\Storage;

use KFlynns\LokiDb\Exception\RunTimeException;

/**
 * Class FieldDefinition
 * @package LokiDb\Table
 */
class FieldDefinition
{

    const DATA_TYPE_CHAR = 'char';
    const DATA_TYPE_BOOL = 'boolean';
    const DATA_TYPE_INT = 'integer';
    const DATA_TYPE_STRING = 'string';
    const DATA_TYPE_FLOAT = 'float';

    const DATA_TYPE_DATE = 'date';
    const DATA_TYPE_DATETIME = 'datetime';

    const INTEGER_SIZE = 4;

    /** @var string  */
    private $name;

    /** @var int */
    private $byteLength;

    /** @var int */
    private $dataType;

    /**
     * FieldDefinition constructor.
     * @param int $dataType
     * @param int $byteLength
     * @param string $name
     * @throws \Exception
     */
    public function __construct($name, $dataType, $byteLength = 1)
    {

        $dataType = \strtolower($dataType);
        switch ($dataType)
        {
            case self::DATA_TYPE_CHAR:
            case self::DATA_TYPE_BOOL:
                $byteLength = 1;
                break;

            case self::DATA_TYPE_INT:
                $byteLength = self::INTEGER_SIZE;
                break;

            case self::DATA_TYPE_STRING:
                break;

            case self::DATA_TYPE_FLOAT:
                break;

            case self::DATA_TYPE_DATE:
                $byteLength = 10;
                break;

            case self::DATA_TYPE_DATETIME:
                $byteLength = 19;
                break;

            default:
                throw new RunTimeException('Tha datatype "' . $dataType . '" is unknown.');

        }

        $this->name = $name;
        $this->dataType = $dataType;
        $this->byteLength = $byteLength;

    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * @return int
     */
    public function getByteLength(): int
    {
        return $this->byteLength;
    }

}