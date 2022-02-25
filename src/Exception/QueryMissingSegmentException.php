<?php

namespace KFlynns\LokiDb\Exception;

use Throwable;

/**
 * Class QueryMissingSegmentException
 * @package LokiDb\Exception
 */
class QueryMissingSegmentException extends LokiDbException
{
    /**
     * QueryMissingSegmentException constructor.
     *
     * @param Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('Missing segment in query.', $previous);
    }
}