<?php

namespace LokiDb\Storage;

use GuzzleHttp\Psr7\Stream;

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

    /** @var int */
    private $lastIndex;

    /** @var int */
    private $sectorSize;

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
        $this->field = $field;
        $this->unique = $unique;
        $this->sort = $sort;
        $this->lastIndex = pow(2, 4 * 8);

        /** @var 1M per file sectorSize */
        $this->sectorSize = 1 * 1024 * 1024;


        //print_r($this->lastIndex);die();
    }


    private function calculateSector()
    {

    }


    /**
     * @param IField $forField
     * @param int $address
     */
    public function write(IField $forField, $address)
    {

    }



}