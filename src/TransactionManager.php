<?php

namespace LokiDb;

use LokiDb\Storage\ITable;


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
            throw new \RuntimeException('There is no active transaction.');
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
            throw new \RuntimeException('A transaction was already started.');
        }
        $this->tables = [];
        $this->transactionStarted = true;
    }

    /**
     * if no transaction is active but tables was changed
     */
    public function autoCommit()
    {
        if(!(!$this->transactionStarted && count($this->tables) > 0))
        {
            return;
        }
        /** @var ITable $table */
        foreach ($this->tables as $table)
        {
            $table->lock();
            $table->flush();
            $table->unlock();
        }
    }

}