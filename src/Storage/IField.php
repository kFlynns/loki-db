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
     * @return mixed
     */
    public function write($value);

    /**
     * @param bool $binary
     * @return mixed
     */
    public function read($binary = false);

    /**
     *
     */
    public function setNull() : void;

}