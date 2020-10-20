<?php

namespace LokiDb\Query;

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


    private $left;

    private $right;

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
     * @return bool
     * @throws \Exception
     */
    public function solve()
    {


        foreach (['left', 'right'] as $side)
        {
            if(is_a($this->{$side}, Condition::class))
            {
                $this->{$side} = $this->{$side}->solve();
            }
            elseif(is_string($this->{$side}))
            {
                $this->{$side} = self::$symbols[$this->{$side}] ?? null;
                if(!$this->{$side})
                {
                    throw new \Exception('asdasd');
                }
            }

        }


        switch($this->operator)
        {
            case self::EQUAL:
                return $this->left === $this->right;

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
                throw new \Exception('asd');

        }



    }



}