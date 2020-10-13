<?php

namespace LokiDb\Query;


use LokiDb\Db;


/**
 * Class Query
 * @package LokiDb\Query
 */
class Query
{

    const SEGMENT_SELECT = 0x0;
    const SEGMENT_FROM = 0x1;


    /** @var Db */
    private $db;

    private $segments = [];


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
    public function select(array $fields)
    {
        $segments[self::SEGMENT_SELECT] = $fields;
        return $this;
    }

    /**
     * @param string $tableName
     * @return $this
     */
    public function from($tableName)
    {
        $segments[self::SEGMENT_FROM] = hash('md5', $tableName);
        return $this;
    }



    public function insert()
    {
        return $this;
    }


    public function execute()
    {

    }


}