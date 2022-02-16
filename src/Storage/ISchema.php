<?php

namespace KFlynns\LokiDb\Storage;

interface ISchema
{
    /**
     * @param string $databaseFolder
     */
    public function __construct(string $databaseFolder);

    /**
     * @return array
     */
    public function getTables(): array;

    /**
     * @return string
     */
    public function getDatabaseFolder(): string;

}