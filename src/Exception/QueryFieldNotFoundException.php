<?php

namespace KFlynns\LokiDb\Exception;
use Throwable;

class QueryFieldNotFoundException extends LokiDbException
{
    /**
     * QueryTableNotFoundException constructor.
     *
     * @param $field
     * @param Throwable|null $previous
     */
    public function __construct(string $field, Throwable $previous = null)
    {
        parent::__construct(
            'The field "' . $field . '" is unknown.',
            $previous
        );
    }
}