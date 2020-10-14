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
     * @param $value
     * @param bool $binary
     */
    public function write($value, $binary = false)
    {

        if($binary)
        {
            switch($this->dataType)
            {
                case FieldDefinition::DATA_TYPE_CHAR:
                    $this->value = unpack('Cc', $value)['c'];
                    break;
                case FieldDefinition::DATA_TYPE_BOOL:
                    $this->value = !!unpack('Cc', $value)['c'];
                    break;
                case FieldDefinition::DATA_TYPE_INT:
                    $this->value = unpack('Ji', $value)['i'];
                    break;
                case FieldDefinition::DATA_TYPE_STRING:
                    $this->value = trim($value, chr(0));
                    break;
            }
            return;
        }

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
                $this->value = intval(trim($value));
                break;

            case FieldDefinition::DATA_TYPE_STRING:
                $this->value = (string)trim($value);
                break;

        }

    }

    /**
     * @param bool $binary
     * @return mixed
     * @throws \Exception
     */
    public function read($binary = false)
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
                return pack('J', $this->value);
            case FieldDefinition::DATA_TYPE_STRING:
                return pack('a' . (string)$this->byteLength, $this->value);
        }
        throw new \Exception('Can not pack field value.');

    }

    public function setNull() : void
    {
        $this->value = null;
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
    public function getByteLength(): int
    {
        return $this->byteLength;
    }


}