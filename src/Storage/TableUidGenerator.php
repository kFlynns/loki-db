<?php
namespace KFlynns\LokiDb\Storage;

class TableUidGenerator
{

    const HASH_ALGO = 'md5';

    protected static ?TableUidGenerator $instance = null;

    private function __construct() {}

    /**
     * @param string $tableName
     * @return string
     */
    protected function get(string $tableName): string
    {
        return \hash(self::HASH_ALGO, $tableName);
    }

    /**
     * @param string $tableName
     * @return string
     */
    public static function generate(string $tableName): string
    {
        if (self::$instance === null)
        {
            self::$instance = new TableUidGenerator();
        }
        return self::$instance->get($tableName);
    }

}