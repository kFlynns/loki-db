<?php

namespace KFlynns\LokiDb\Query;

use KFlynns\LokiDb\Db;
use KFlynns\LokiDb\Exception\QueryMissingSegmentException;
use KFlynns\LokiDb\Exception\QueryNotCompleteException;


/**
 * Class Query
 * @package LokiDb\Query
 */
class Query
{

    const SEGMENT_SELECT = 'select';
    const SEGMENT_FROM = 'from';
    const SEGMENT_INSERT = 'insert';
    const SEGMENT_INTO = 'into';
    const SEGMENT_UPDATE = 'update';
    const SEGMENT_SET = 'set';
    const SEGMENT_DELETE = 'delete';
    const SEGMENT_WHERE = 'where';

    const MODE_SELECT = 'select';
    const MODE_INSERT = 'insert';
    const MODE_UPDATE = 'update';
    const MODE_DELETE = 'delete';

    /** @var Db */
    private $db;

    /** @var array  */
    private $segments = [];

    /** @var string */
    private $mode = null;

    /**
     * Query constructor.
     */
    private function __construct() {}


    /**
     * @param Db $db
     * @return Query
     */
    public static function create(Db $db) : Query
    {
        $query = new self();
        $query->setDb($db);
        return $query;
    }

    /**
     * @param Db $db
     */
    protected function setDb(Db $db)
    {
        $this->db = $db;
    }

    /**
     * @param array|null $fields
     * @return $this
     */
    public function select(array $fields = null) : Query
    {
        $this->segments[self::SEGMENT_SELECT] = $fields ? $fields : [];
        return $this;
    }

    /**
     * @param string $tableName
     * @return $this
     */
    public function from($tableName) : Query
    {
        $this->segments[self::SEGMENT_FROM] = $tableName;
        return $this;
    }

    /**
     * @param array[array] $fields
     * @return $this
     */
    public function insert(array $fields) : Query
    {
        $this->segments[self::SEGMENT_INSERT] = $fields;
        return $this;
    }

    /**
     * @param $tableName
     * @return $this
     */
    public function into($tableName) : Query
    {
        $this->segments[self::SEGMENT_INTO] = $tableName;
        return $this;
    }

    /**
     * @param $tableName
     * @return $this
     */
    public function update($tableName) : Query
    {
        $this->segments[self::SEGMENT_UPDATE] = $tableName;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function set(array $fields) : Query
    {
        $this->segments[self::SEGMENT_SET] = $fields;
        return $this;
    }

    /**
     * @return $this
     */
    public function delete() : Query
    {
        $this->segments[self::SEGMENT_DELETE] = true;
        return $this;
    }


    /**
     * @param Condition $condition
     * @return $this
     */
    public function where(Condition $condition) : Query
    {
        $this->segments[self::SEGMENT_WHERE] = $condition;
        return $this;
    }


    /**
     * @return \Iterator|null
     * @throws QueryMissingSegmentException
     * @throws QueryNotCompleteException
     */
    public function execute(): ?\Iterator
    {
        $setMode = function() {
            $exception = new QueryNotCompleteException();
            if(isset($this->segments[self::SEGMENT_SELECT]))
            {
                $this->mode = self::MODE_SELECT;
            }

            if(isset($this->segments[self::SEGMENT_INSERT]))
            {
                if(null !== $this->mode)
                {
                    throw $exception;
                }
                $this->mode = self::MODE_INSERT;
                return;
            }

            if(isset($this->segments[self::SEGMENT_UPDATE]))
            {
                if(null !== $this->mode)
                {
                    throw $exception;
                }
                $this->mode = self::MODE_UPDATE;
                return;
            }

            if(isset($this->segments[self::SEGMENT_DELETE]))
            {
                if(null !== $this->mode)
                {
                    throw $exception;
                }
                $this->mode = self::MODE_DELETE;
                return;
            }

        };
        $setMode();
        if(!$this->mode)
        {
            throw new QueryMissingSegmentException('The given query is incomplete.');
        }
        return $this->db->runQuery($this);

    }

    /**
     * @return string
     * @throws QueryNotCompleteException
     */
    public function getMode()
    {
        if(!$this->mode)
        {
            throw new QueryNotCompleteException();
        }
        return $this->mode;
    }

    /**
     * @param int $segment
     * @return mixed|null
     */
    public function getSegment($segment)
    {
        return $this->segments[$segment] ?? null;
    }

}