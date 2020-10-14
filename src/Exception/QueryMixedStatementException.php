<?php

namespace LokiDb\Exception;

use Throwable;

/**
 * Class QueryMixedStatementException
 * @package LokiDb\Exception
 */
class QueryMixedStatementException extends LokiDbException
{
    /**
     * QueryMixedStatementException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Mixed INSERT, SELECT, UPDATE and/or DELETE statements in one query.', '001', $previous);
    }
}