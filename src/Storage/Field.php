<?php

namespace LokiDb\Storage;

/**
 * Class Field
 * @package LokiDb\Table
 */
class Field implements IField
{

    /** @var mixed */
    private $value = null;

    /** @var int */
    private $dataType;

    /** @var int */
    private $byteLength;

    /** @var string */
    private $name;

    /**
     * Field constructor.
     * @param int $dataType
     * @param int $byteLength
     */
    public function __construct($name, $dataType, $byteLength)
    {
        $this->name = $name;
        $this->dataType = $dataType;
        $this->byteLength = $byteLength;
    }

    /**
     * todo: charsets...
     * @param null $value
     * @return mixed|null
     */
    public function write($value)
    {

        $inDataType = strtolower((gettype($value)));

        switch ($this->dataType)
        {
            case FieldDefinition::DATA_TYPE_CHAR:
                switch ($inDataType)
                {
                    case 'string':
                        $this->value = $value[0];
                        break;

                    case 'integer':
                        $this->value = chr($value);
                        break;

                    case 'boolean':
                        $this->value = $value ? chr(1) : chr (0);
                        break;
                }
                break;

            case FieldDefinition::DATA_TYPE_BOOL:
                $this->value = boolval($value);
                break;

            case FieldDefinition::DATA_TYPE_INT:
                $this->value = intval($value);
                break;

            case FieldDefinition::DATA_TYPE_STRING:
                $this->value = (string)$value;
                break;

        }

        return $this->value;

    }

    /**
     * @param bool $binary
     * @return string
     * @throws \Exception
     */
    public function read($binary = false) : string
    {
        if(!$binary)
        {
            return $this->value;
        }

        switch($this->dataType)
        {
            case FieldDefinition::DATA_TYPE_BOOL:
                return pack('C', $this->value ?  1 : 0);
            case FieldDefinition::DATA_TYPE_CHAR:
                return pack('C', ord($this->value));
            case FieldDefinition::DATA_TYPE_INT:
                return pack('q', $this->value);
            case FieldDefinition::DATA_TYPE_STRING:
                return pack('a' . (string)$this->byteLength, $this->value);
        }

        throw new \Exception('Can not pack field value.');

    }

    public function setNull() : void
    {
        $this->value = null;
    }

}