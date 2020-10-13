<?php

namespace LokiDb;

use LokiDb\Table\Table;

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
     * @param Table $table
     * @throws \Exception
     */
    public function registerTable(Table $table)
    {
        $this->tables[$table->getHash()] = $table;
        $table->connectToDisk($this->databaseFolder);
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
     * @param string $tableName
     * @param array $row
     */
    public function insert($tableName, array $row)
    {

        $hash = hash('md5', $tableName);

        /** @var Table $table */
        $table = $this->tables[$hash];
        $table->setDataRow($row);

        if(null === $this->transactions)
        {
            $table->flush();
            return;
        }
        $this->transactions[] = $hash;

    }


}