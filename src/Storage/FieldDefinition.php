<?php

namespace LokiDb\Storage;

use LokiDb\Exception\RunTimeException;

/**
 * Class FieldDefinition
 * @package LokiDb\Table
 */
class FieldDefinition
{

    const DATA_TYPE_CHAR = 0x0;
    const DATA_TYPE_BOOL = 0x1;
    const DATA_TYPE_INT = 0x2;
    const DATA_TYPE_STRING = 0x3;
    const DATA_TYPE_FLOAT = 0x4;

    const DATA_TYPE_DATE = 0x10;
    const DATA_TYPE_DATETIME = 0x11;

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
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getDataType() : int
    {
        return $this->dataType;
    }

    /**
     * @return int
     */
    public function getByteLength() : int
    {
        return $this->byteLength;
    }

}