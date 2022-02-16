<?php

namespace KFlynns\LokiDb;

use KFlynns\LokiDb\Exception\RunTimeException;
use KFlynns\LokiDb\Storage\ITable;


/**
 * Class TransactionManager
 * @package LokiDb
 */
class TransactionManager
{

    /** @var bool */
    private $transactionStarted = false;

    /** @var array[ITable] */
    private $tables = [];

    /** @var null|TransactionManager */
    private static $instance = null;

    /**
     * private due singleton
     * TransactionManager constructor.
     */
    private function __construct() {}

    /**
     * @return TransactionManager|null
     */
    public static function getInstance()
    {
        if(null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param ITable $table
     */
    public function addTable(ITable $table)
    {
        if(in_array($table->getUId(), $this->tables))
        {
            return;
        }
        $this->tables[$table->getUId()] = $table;
        $table->lock();
    }

    /**
     * flush all tables and reset
     * @param bool $intoVoid
     */
    public function commit($intoVoid = false)
    {
        if(!$this->transactionStarted)
        {
            throw new RunTimeException('There is no active transaction.');
        }
        /** @var ITable $table */
        foreach ($this->tables as $table)
        {
            $table->flush($intoVoid);
        }
        /** @var ITable $table */
        foreach ($this->tables as $table)
        {
            $table->unlock();
        }
        $this->tables = [];
        $this->transactionStarted = false;
    }

    /**
     * clean start
     */
    public function start()
    {
        if($this->transactionStarted)
        {
            throw new RuntimeException('A transaction was already started.');
        }
        $this->tables = [];
        $this->transactionStarted = true;
    }

    /**
     * if no transaction is active but tables was changed
     * @return bool
     */
    public function autoCommit() : bool
    {
        if(!(!$this->transactionStarted && count($this->tables) > 0))
        {
            return false;
        }
        /** @var ITable $table */
        foreach ($this->tables as $table)
        {
            $table->lock();
            $table->flush();
            $table->unlock();
        }
        return true;
    }

}