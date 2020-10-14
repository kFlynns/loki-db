<?php

namespace LokiDb\Exception;

use Throwable;

/**
 * Class QueryMissingSegmentException
 * @package LokiDb\Exception
 */
class QueryMissingSegmentException extends LokiDbException
{
    /**
     * QueryMissingSegmentException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Missing segment in query.', '002', $previous);
    }
}