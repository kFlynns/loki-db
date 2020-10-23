<?php

namespace LokiDb;

use LokiDb\Exception\QueryMissingSegmentException;
use LokiDb\Exception\RunTimeException;
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
            $this->transactionManager = TransactionManager::getInstance();
            return;
        }
        throw new RunTimeException('Folder "' . $databaseFolder . '" is invalid.');
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
        if(isset($this->tables[$table->getUId()]))
        {
            throw new RuntimeException('The table "' . $table->getUId() . '" does already exist.');
        }

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
        $where = $query->getSegment(Query::SEGMENT_WHERE);

        if(null === $from)
        {
            throw new QueryMissingSegmentException();
        }

        /** @var ITable $table */
        $table = $this->tables[$from];
        return $table->fetch($where);

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
     */
    public function runUpdate(Query $query)
    {

        $update = $query->getSegment(Query::SEGMENT_UPDATE);
        $set = $query->getSegment(Query::SEGMENT_SET);
        $where = $query->getSegment(Query::SEGMENT_WHERE);

        if(null === $update || null === $set)
        {
            throw new QueryMissingSegmentException();
        }

        /** @var ITable $table */
        $table = $this->tables[$update];
        $this->transactionManager->addTable($table);
        foreach ($table->fetch($where) as $row)
        {
            foreach ($set as $key => $value)
            {
                if(!isset($row[$key]))
                {
                    throw new RuntimeException('Field "' . $key . '" in update query is unknown.');
                }
                $row[$key] = $value;
            }
            $table->setDataRow($row);
        }

    }


    public function runDelete(Query $query)
    {
        $from = $query->getSegment(Query::SEGMENT_FROM);
        $where = $query->getSegment(Query::SEGMENT_WHERE);

        if(null === $from)
        {
            throw new QueryMissingSegmentException();
        }

        /** @var ITable $table */
        $table = $this->tables[$from];
        $this->transactionManager->addTable($table);
        foreach ($table->fetch($where) as $row)
        {
            $table->setEmptyDataRow();
        }
    }


    /**
     * @param Query $query
     */
    public function runQuery(Query $query)
    {
        return $this->{'run' . ucfirst($query->getMode())}($query) ?? [];
    }


}