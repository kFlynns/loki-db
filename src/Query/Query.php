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
     * @param array[string] $fields
     * @return $this
     */
    public function select(array $fields) : Query
    {
        $this->segments[self::SEGMENT_SELECT] = $fields;
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
     * @return array
     * @throws \Exception
     */
    public function execute() : array
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


}