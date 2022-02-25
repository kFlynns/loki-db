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
     * @param string $segmentName
     * @param Throwable|null $previous
     */
    public function __construct(string $segmentName, Throwable $previous = null)
    {
        parent::__construct('Missing segment "' . $segmentName . '" in query.', $previous);
    }
}