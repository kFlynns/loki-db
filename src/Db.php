<?php

namespace KFlynns\LokiDb;

use KFlynns\LokiDb\Exception\QueryMissingSegmentException;
use KFlynns\LokiDb\Exception\RunTimeException;
use KFlynns\LokiDb\Query\Query;
use KFlynns\LokiDb\Storage\ISchema;
use KFlynns\LokiDb\Storage\ITable;
use KFlynns\LokiDb\Storage\Schema;
use KFlynns\LokiDb\Storage\TableDefinition;

/**
 * Class Db
 * @package LokiDb
 */
class Db
{

    /** @var array[Table] */
    private $tables = [];

    /** @var TransactionManager  */
    private $transactionManager;

    /** @var ISchema  */
    protected $schema;

    /**
     * Db constructor.
     * @param string $databaseFolder
     * @throws \Exception
     */
    public function __construct(ISchema $schema)
    {
        $this->transactionManager = TransactionManager::getInstance();
        $this->schema = $schema;
        /** @var TableDefinition $table */
        foreach ($schema->getTables() as $table)
        {
            $this->createTable($table);
        }
    }

    /**
     * @param TableDefinition $tableDefinition
     * @return ITable
     */
    protected function createTable(TableDefinition $tableDefinition): ITable
    {
        $table = Storage\Table::create($tableDefinition->getName());
        if(isset($this->tables[$table->getUId()]))
        {
            throw new RuntimeException('The table "' . $table->getUId() . '" does already exist.');
        }
        $table->addFieldDefinitions($tableDefinition->getFieldDefinitions());
        $table->addIndices($tableDefinition->getIndices());
        $table->connectToDisk($this->schema->getDatabaseFolder());
        $this->tables[$table->getUId()] = $table;
        return $table;
    }

    /**
     * @return Query
     */
    public function createQuery() : Query
    {
        return Query::create($this);
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


    protected function getValidatedQuerySegment(Query $query, string $segmentName)
    {
        $segment = $query->getSegment($segmentName);
        if (!$segment)
        {
            return $segment;
        }
        switch (\strtolower($segmentName))
        {
            case Query::SEGMENT_INTO:
                if(!($this->tables[$segment] ?? false))
                {
                    // todo..
                    throw new QueryMissingSegmentException('table bla unknown');
                }
                return $segment;
                break;
        }
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
        $into = $this->getValidatedQuerySegment($query,Query::SEGMENT_INTO);
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
                    throw new RunTimeException('Field "' . $key . '" in update query is unknown.');
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

    /**
     * Getter for $schema.
     *
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

}