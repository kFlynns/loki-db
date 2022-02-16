<?php

namespace KFlynns\LokiDb\Storage;

use GuzzleHttp\Psr7\Stream;
use KFlynns\LokiDb\Exception\RunTimeException;

/**
 * Class Index
 * @package LokiDb\Storage
 */
class Index
{

    const SORT_ASC = 0x0;
    const SORT_DESC = 0x1;

    /** @var IField */
    private $field;

    /** @var bool */
    private $unique;

    /** @var int */
    private $sort;

    /** @var Stream */
    private static $stream;

    /**
     * Index constructor.
     * @param FieldDefinition $fieldDefinition
     * @param bool $unique
     * @param int $sort
     */
    public function __construct(
        IField $field,
        $unique = false,
        $sort = self::SORT_ASC
    ) {
        if($field->getByteLength() > 64)
        {
            throw new RunTimeException('An field that should be indexed can\'t be longer than 64 bytes.');
        }
        $this->field = $field;
        $this->unique = $unique;
        $this->sort = $sort;
    }


    private function calculateSector()
    {

    }


    /**
     * @param mixed $value
     * @param int $address
     */
    public function write($value, $address)
    {
        $path = str_split(hash('sha512', $value), 4);
        print_r($path );
        die();
    }



}