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
     *
     * @param Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct(
            'Query is not completely filled for executing against the database.',
            $previous
        );
    }
}