<?php

namespace LokiDb;

use LokiDb\Exception\QueryMissingSegmentException;
use LokiDb\Query\Query;
use LokiDb\Storage\ITable;
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
        $table = Storage\Table::create($tableDefinition->getName());
        $table->addDefinition($tableDefinition->getFieldDefinitions());
        $this->tables[$table->getUId()] = $table;
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

        $result = [];

        switch($query->getMode())
        {
            case Query::MODE_INSERT:

                $insert = $query->getSegment(Query::SEGMENT_INSERT);
                $into = $query->getSegment(Query::SEGMENT_INTO);

                if(null === $into)
                {
                    throw new QueryMissingSegmentException();
                }

                /** @var ITable $table */
                $table = $this->tables[$into];
                $table->eof();
                $table->setDataRow($insert);
                $table->flush();

                break;

            case Query::MODE_SELECT:

                $select = $query->getSegment(Query::SEGMENT_SELECT);
                $from = $query->getSegment(Query::SEGMENT_FROM);

                if(null === $from)
                {
                    throw new QueryMissingSegmentException();
                }

                /** @var ITable $table */
                $table = $this->tables[$from];
                $table->fetch(function(array $row) use (&$result) {
                    $result[] = $row;
                }, $select);
                break;
        }

        return $result;
    }


}