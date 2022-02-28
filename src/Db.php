<?php

namespace KFlynns\LokiDb;

use KFlynns\LokiDb\Exception\QueryFieldNotFoundException;
use KFlynns\LokiDb\Exception\QueryMissingSegmentException;
use KFlynns\LokiDb\Exception\QueryTableNotFoundException;
use KFlynns\LokiDb\Exception\RunTimeException;
use KFlynns\LokiDb\Query\Query;
use KFlynns\LokiDb\Storage\ISchema;
use KFlynns\LokiDb\Storage\ITable;
use KFlynns\LokiDb\Storage\Schema;
use KFlynns\LokiDb\Storage\TableDefinition;
use KFlynns\LokiDb\Storage\TableUidGenerator;

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
        if(null === $segment)
        {
            throw new QueryMissingSegmentException($segmentName);
        }

        switch (\strtolower($segmentName))
        {
            case Query::SEGMENT_FROM:
            case Query::SEGMENT_INTO:
            case Query::MODE_UPDATE:
                $tableUId = TableUidGenerator::generate($segment);
                if(!($this->tables[$tableUId] ?? false))
                {
                    throw new QueryTableNotFoundException($segment);
                }
                return $tableUId;
            case Query::SEGMENT_INSERT:
            case Query::SEGMENT_SET:
                if (\count($segment) === 0)
                {
                    throw new RunTimeException('For inserting or updating a table, there must be specified fields.');
                }
                return $segment;

        }
        throw new \RuntimeException('The segment identifier "' . $segmentName .'" is unknown to die RDBMS.');
    }

    /**
     * @param Query $query
     * @return \Generator
     * @throws QueryMissingSegmentException
     * @throws QueryTableNotFoundException
     * @throws RunTimeException
     */
    protected function runSelect(Query $query): \Generator
    {
        $select = $query->getSegment(Query::SEGMENT_SELECT);
        $from = $this->getValidatedQuerySegment($query,Query::SEGMENT_FROM);
        $where = $query->getSegment(Query::SEGMENT_WHERE);
        /** @var ITable $table */
        $table = $this->tables[$from];
        return $table->fetch();
    }

    /**
     * @param Query $query
     * @return void
     * @throws QueryMissingSegmentException
     * @throws QueryTableNotFoundException
     * @throws RunTimeException
     */
    protected function runInsert(Query $query)
    {
        $insert = $this->getValidatedQuerySegment($query,Query::SEGMENT_INSERT);
        $into = $this->getValidatedQuerySegment($query,Query::SEGMENT_INTO);
        /** @var ITable $table */
        $table = $this->tables[$into];
        $fields = $table->getFields();
        /**
         * @var string $field
         * @var  mixed $value
         */
        foreach ($insert as $field => $value)
        {
            if (!\key_exists($field, $fields))
            {
                throw new QueryFieldNotFoundException($field);
            }
        }
        $this->transactionManager->addTable($table);
        $table->eof();
        $table->setDataRow($insert);
    }

    /**
     * @param Query $query
     */
    public function runUpdate(Query $query)
    {

        $update = $this->getValidatedQuerySegment($query, Query::SEGMENT_UPDATE);
        $set = $this->getValidatedQuerySegment($query, Query::SEGMENT_SET);
        $where = $query->getSegment(Query::SEGMENT_WHERE);

        /** @var array $tableFields */
        $tableFields = \array_keys($this->tables[$update]->getFields());
        foreach (\array_keys($set) as $fieldName)
        {
            if (!\in_array($fieldName, $tableFields))
            {
                throw new QueryFieldNotFoundException($fieldName);
            }
        }
        /** @var ITable $table */
        $table = $this->tables[$update];
        $this->transactionManager->addTable($table);
        foreach ($table->fetch($where) as $row)
        {
            foreach ($set as $key => $value)
            {
                $row[$key] = $value;
            }
            $table->setDataRow($row);
        }

    }


    public function runDelete(Query $query)
    {
        $from = $this->getValidatedQuerySegment($query, Query::SEGMENT_FROM);
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
    public function runQuery(Query $query): ?\Iterator
    {
        return $this->{'run' . ucfirst($query->getMode())}($query) ?? null;
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