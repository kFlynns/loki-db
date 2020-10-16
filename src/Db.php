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

    /** @var TransactionManager  */
    private $transactionManager;

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
            $this->transactionManager = new TransactionManager();
            return;
        }
        throw new \Exception('Folder "' . $databaseFolder . '" is invalid.');
    }

    /**
     * flush tables beside transactions
     */
    public function __destruct()
    {
        $this->transactionManager->autoCommit();
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
        $this->transactionManager->start();
    }

    /**
     * flush all table journals to disk
     */
    public function commit()
    {
        $this->transactionManager->commit();
    }

    /**
     * flush all table journals to void
     */
    public function rollBack()
    {
        $this->transactionManager->commit(true);
    }

    /**
     * @param Query $query
     * @throws QueryMissingSegmentException
     */
    protected function runSelect(Query $query)
    {
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

        return $result;

    }

    /**
     * @param Query $query
     * @throws QueryMissingSegmentException
     */
    protected function runInsert(Query $query)
    {
        $insert = $query->getSegment(Query::SEGMENT_INSERT);
        $into = $query->getSegment(Query::SEGMENT_INTO);

        if(null === $into)
        {
            throw new QueryMissingSegmentException();
        }

        /** @var ITable $table */
        $table = $this->tables[$into];
        $this->transactionManager->addTable($table);
        $table->eof();
        $table->setDataRow($insert);
    }

    /**
     * @param Query $query
     * @return array
     *
     */
    public function runQuery(Query $query) : array
    {
        return $this->{'run' . ucfirst($query->getMode())}($query) ?? [];
    }


}