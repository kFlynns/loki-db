<?php

namespace KFlynns\LokiDb\Exception;

use Throwable;

/**
 * Class QueryMixedStatementException
 * @package LokiDb\Exception
 */
class QueryMixedStatementException extends LokiDbException
{
    /**
     * QueryMixedStatementException constructor.
     *
     * @param Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct(
            'Mixed INSERT, SELECT, UPDATE and/or DELETE statements in one query.',
            $previous
        );
    }
}