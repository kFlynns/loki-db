<?php

namespace KFlynns\LokiDb\Exception;

use Throwable;

/**
 * Class RunTimeException
 * @package LokiDb\Exception
 */
class RunTimeException extends LokiDbException
{
    /**
     * RunTimeException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message === 'There was an error due running LokiDb.' ? '' : $message, '100', $previous);
    }

}