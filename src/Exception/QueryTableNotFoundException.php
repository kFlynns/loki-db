<?php

namespace KFlynns\LokiDb\Exception;
use Throwable;

class QueryTableNotFoundException extends LokiDbException
{
    /**
     * QueryTableNotFoundException constructor.
     *
     * @param $table
     * @param Throwable|null $previous
     */
    public function __construct($table, Throwable $previous = null)
    {
        parent::__construct(
            'The table "' . $table . '" is unknown.',
            $previous
        );
    }
}