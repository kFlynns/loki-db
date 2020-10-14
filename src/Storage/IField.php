<?php

namespace LokiDb\Storage;

/**
 * Interface IField
 * @package LokiDb\Storage
 */
interface IField
{

    /**
     * @param $value
     * @param bool $binary
     * @return mixed
     */
    public function write($value, $binary = false);

    /**
     * @param bool $binary
     * @return mixed
     */
    public function read($binary = false);

    /**
     *
     */
    public function setNull() : void;

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return int
     */
    public function getByteLength() : int;

}