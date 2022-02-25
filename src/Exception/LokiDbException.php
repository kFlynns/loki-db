<?php

namespace KFlynns\LokiDb\Exception;

use Exception;
use Throwable;

/**
 * Class LokiDbException
 * @package LokiDb\Exception
 */
class LokiDbException extends Exception implements ILokiDbException
{
    /**
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct(
            $message,
            crc32(\get_class($this)),
            $previous
        );
    }
}