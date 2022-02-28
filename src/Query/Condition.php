<?php

namespace KFlynns\LokiDb\Query;

use KFlynns\LokiDb\Exception\RunTimeException;

/**
 * Class Condition
 * @package LokiDb\Query
 */
class Condition
{


    const AND = '&&';
    const OR = '||';

    const EQUAL = '=';
    const NOT_EQUAL = '!=';
    const GREATER_THEN = '>';
    const LESS_THEN = '<';


    const ADDITION = '+';
    const SUBTRACTION = '-';
    const DIVISION = '/';
    const MULTIPLICATION = '*';

    /** @var mixed */
    private $left;

    /** @var mixed */
    private $right;

    /** @var mixed */
    private $operator;

    /** @var array  */
    private static $symbols = [];


    /**
     * Condition constructor.
     * @param $left
     * @param string $operator
     * @param $right
     */
    public function __construct($left, $operator, $right)
    {
        $this->left = $left;
        $this->right = $right;
        $this->operator = trim($operator);
    }

    /**
     * @param array $symbols
     */
    public static function setSymbols(array $symbols)
    {
        self::$symbols = $symbols;
    }


    /**
     * @param callable|null $callback
     * @throws \Exception
     */
    protected function resolveSymbols(callable $callback = null)
    {
        foreach (['right', 'left'] as $side)
        {
            if($this->{$side} instanceof Condition)
            {
                $this->{$side} = $this->{$side}->solve($callback);
                continue;
            }
            elseif(is_string($this->{$side}))
            {
                if(is_callable($callback))
                {
                    $this->{$side} = $callback($this->{$side});
                    continue;
                }
                $this->{$side} = self::$symbols[$this->{$side}] ?? null;
                if(!$this->{$side})
                {
                    throw new RuntimeException('Could not resolve symbol.');
                }
            }

        }
    }


    /**
     * @param callable|null $callback
     * @return bool|float|int
     * @throws \Exception
     */
    public function solve(callable $callback = null)
    {
        $this->resolveSymbols($callback);
        switch($this->operator)
        {
            case self::EQUAL:
                return $this->left == $this->right;

            case self::NOT_EQUAL:
                return $this->left !== $this->right;

            case self::GREATER_THEN:
                return $this->left > $this->right;

            case self::LESS_THEN:
                return $this->left < $this->right;

            case self::ADDITION:
                return $this->left + $this->right;

            case self::SUBTRACTION:
                return $this->left - $this->right;

            case self::DIVISION:
                return $this->left / $this->right;

            case self::MULTIPLICATION:
                return $this->left * $this->right;

            default:
                throw new RunTimeException('Unknown operator "' . $this->operator . '".');

        }

    }


}