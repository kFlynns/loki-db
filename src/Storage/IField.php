<?php

namespace KFlynns\LokiDb\Storage;

/**
 * Interface IField
 * @package LokiDb\Storage
 */
interface IField
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return int
     */
    public function getByteLength() : int;

    /**
     * @return int
     */
    public function getDateType() : int;

}