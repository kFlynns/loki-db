<?php

namespace LokiDb\Query;

use LokiDb\Db;
use LokiDb\Exception\QueryMissingSegmentException;
use LokiDb\Exception\QueryNotCompleteException;


/**
 * Class Query
 * @package LokiDb\Query
 */
class Query
{

    const SEGMENT_SELECT = 0x0;
    const SEGMENT_FROM = 0x1;
    const SEGMENT_INSERT = 0x2;
    const SEGMENT_INTO = 0x3;
    const SEGMENT_UPDATE = 0x4;
    const SEGMENT_SET = 0x5;
    const SEGMENT_DELETE = 0x6;
    const SEGMENT_WHERE = 0xF0;

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
        $this->segments[self::SEGMENT_FROM] = hash('md5', $tableName);
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
        $this->segments[self::SEGMENT_INTO] = hash('md5', $tableName);
        return $this;
    }

    /**
     * @param $tableName
     * @return $this
     */
    public function update($tableName) : Query
    {
        $this->segments[self::SEGMENT_UPDATE] = hash('md5', $tableName);
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
     * @return array
     * @throws \Exception
     */
    public function execute()
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
            throw new QueryMissingSegmentException();
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