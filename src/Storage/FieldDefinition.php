<?php

namespace LokiDb\Storage;

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

    const INTEGER_SIZE = 8;

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
        if($dataType < self::DATA_TYPE_CHAR || $dataType > self::DATA_TYPE_STRING)
        {
            throw new \Exception('The datatype "' . $dataType . '" is unknown.');
        }

        $this->name = $name;
        $this->dataType = $dataType;
        $byteLength = ($dataType === self::DATA_TYPE_INT) ? 8 : $byteLength;
        $byteLength = ($dataType === self::DATA_TYPE_BOOL) ? 1 : $byteLength;
        $byteLength = ($dataType === self::DATA_TYPE_CHAR) ? 1 : $byteLength;
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