<?php

namespace KFlynns\LokiDb\Exception;

use Throwable;

/**
 * Class QueryMixedStatementException
 * @package LokiDb\Exception
 */
class QueryNotCompleteException extends LokiDbException
{
    /**
     * QueryMixedStatementException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Query is not completely filled for executing against the database.', '001', $previous);
    }
}