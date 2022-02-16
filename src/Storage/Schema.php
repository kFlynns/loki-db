<?php

namespace KFlynns\LokiDb\Storage;

use KFlynns\LokiDb\Exception\RunTimeException;

class Schema implements ISchema
{

    /** @var array */
    protected $schemaDescription;

    /** @var array */
    protected $tables;

    /** @var string */
    protected $databaseFolder;

    /**
     * @param string $databaseFolder
     * @throws RunTimeException
     */
    public function __construct(string $databaseFolder)
    {
        $databaseFolder = \rtrim($databaseFolder, '\/\\');
        $schemaDescriptionPath = $databaseFolder . '/loki.json';
        if (
            !\file_exists($schemaDescriptionPath) ||
            !\is_readable($schemaDescriptionPath)
        ) {
            $this->throwException('There must be a readable schema file under: "' . $schemaDescriptionPath . '".');
        }
        $this->databaseFolder = $databaseFolder;
        $json = \file_get_contents($schemaDescriptionPath);
        $this->schemaDescription = \json_decode($json, true);
        if (!$this->schemaDescription)
        {
            $this->throwException(
                'The schema file "' . $schemaDescriptionPath . '" must contain valid json, error: ' . \json_last_error_msg()
            );
        }
        $this->build();
    }


    /**
     * Throw exception.
     *
     * @param string $message
     * @return void
     * @throws RunTimeException
     */
    protected function throwException(string $message): void
    {
        throw new RunTimeException($message);
    }


    /**
     * Build schema from json data.
     *
     * @return void
     * @throws RunTimeException
     */
    protected function build(): void
    {

        $this->tables = [];
        /**
         * @var string $tableName
         * @var array $tableDescription
         */
        foreach ($this->schemaDescription as $tableName => $tableDescription)
        {
            $table = new TableDefinition($tableName, []);
            foreach ($tableDescription as $fieldName => $fieldDescription)
            {
                $length = (int)($fieldDescription['length'] ?? 0);
                $table->addField(
                    (string)$fieldName,
                    $fieldDescription['type'] ?? 'missing',
                    $length
                );
            }
            $this->tables[$tableName] = $table;
        }

    }

    /**
     * Getter for tables
     * .
     * @return array
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * Getter for database folder path.
     *
     * @return string
     */
    public function getDatabaseFolder(): string
    {
        return $this->databaseFolder;
    }



}