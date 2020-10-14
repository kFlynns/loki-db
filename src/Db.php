<?php

namespace LokiDb;

use LokiDb\Query\Query;
use LokiDb\Storage\ITable;
use LokiDb\Storage\Table;
use LokiDb\Storage\TableDefinition;

/**
 * Class Db
 * @package LokiDb
 */
class Db
{

    /** @var string */
    private $databaseFolder;

    /** @var array[Table] */
    private $tables = [];

    /** @var null|array  */
    private $transactions = null;

    /**
     * Db constructor.
     * @param string $databaseFolder
     * @throws \Exception
     */
    public function __construct($databaseFolder)
    {
        if(is_dir($databaseFolder))
        {
            $this->databaseFolder = $databaseFolder;
            return;
        }
        throw new \Exception('Folder "' . $databaseFolder . '" is invalid.');
    }

    /**
     * @return Query
     */
    public function createQuery() : Query
    {
        return Query::create($this);
    }


    /**
     * @param TableDefinition $tableDefinition
     * @return ITable
     */
    public function createTable(TableDefinition $tableDefinition) : ITable
    {
        $table = Table::create($tableDefinition->getName());
        $table->addDefinition($tableDefinition->getFieldDefinitions());
        $this->tables[$table->getHash()] = $table;
        $table->connectToDisk(
            $this->databaseFolder
        );
        return $table;
    }


    /**
     * @throws \Exception
     */
    public function beginTransaction()
    {
        if(null !== $this->transactions)
        {
            throw new \Exception('Transaction can\'t be started because an existing one.');
        }
        $this->transactions = [];
    }

    /**
     *
     */
    public function commit()
    {
        if(null !== $this->transactions)
        {
            foreach ($this->transactions as $transaction)
            {
                $this->tables[$transaction]->flush();
            }
        }
        $this->transactions = null;
    }


    public function rollBack()
    {
        $this->transactions = null;
    }


    /**
     * @param Query $query
     * @return array
     *
     */
    public function runQuery(Query $query) : array
    {
        switch($query->getMode())
        {
            case Query::MODE_INSERT:
                break;
        }


        return [];
    }





    /**
     * @param string $tableName
     * @param array $row

    public function insert($tableName, array $row)
    {

        $hash = hash('md5', $tableName);

        /** @var Table $table
        $table = $this->tables[$hash];
        $table->setDataRow($row);

        if(null === $this->transactions)
        {
            $table->flush();
            return;
        }
        $this->transactions[] = $hash;

    }
        */

}