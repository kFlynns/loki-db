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

    /**
     * @return int
     */
    public function getDateType(): int
    {
        return $this->dataType;
    }

}