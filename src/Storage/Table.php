<?php

namespace LokiDb\Storage;

use GuzzleHttp\Psr7\Stream;

/**
 * Class Table
 * @package LokiDb\Table
 */
class Table implements ITable
{

    /** @var Stream */
    private $stream;

    /** @var resource */
    private $fileResource;

    /** @var array[Field]  */
    private $fields = [];

    /** @var string */
    private $name;

    /** @var int */
    private $rowLength = 0;

    /** @var string */
    private $uId;

    /** @var string */
    private $diskFilePath;

    /**
     * Table constructor.
     */
    private function __construct() {}


    /**
     * @param string $tableName
     * @return ITable
     */
    static public function create($tableName) : ITable
    {
        $table = new self();
        $table->name = $tableName;
        $table->uId = hash('md5', $table->name);
        $table->diskFilePath = implode(
                '/',
                str_split(
                    $table->uId,
                    2
                )
            ) . '/lki';
        return $table;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->stream->close();
    }

    /**
     * @param array[array] $data
     */
    public function setDataRow(array $data) : void
    {
        foreach ($data as $key => $value)
        {
            /** @var Field $field */
            $field = $this->fields[$key] ?? null;
            if(null === $field)
            {
                throw new \Exception('Table "' . $this->name . '" has no field "' . $key . '".');
            }
            $field->write($value);
        }
    }

    /**
     *
     */
    protected function getDataRow()
    {
        /** @var IField $field */
        foreach ($this->fields as &$field)
        {
            $field->write(
                $this->stream->read($field->getByteLength()),
                true
            );
        }
    }


    /**
     * @param callable $callback
     * @param array $fields
     * @param array|null $filter
     */
    public function fetch(callable $callback, array $fields, array $filter = null) : void
    {

        $this->stream->rewind();
        $offset = 0;

        do
        {

            $this->getDataRow();
            $row = [];

            /** @var IField $field */
            foreach ($this->fields as &$field)
            {
                $fieldName = $field->getName();
                if(count($fields) === 0)
                {
                    $row[$fieldName] = $field->read();
                    continue;
                }
                if(in_array($fieldName, $fields))
                {
                    $row[$fieldName] = $field->read();
                    continue;
                }
            }

            $callback($row);
            $this->stream->seek($offset += $this->rowLength);

        } while($this->stream->getSize() > $offset);
    }


    public function flush()
    {
        /** @var Field $field */
        foreach ($this->fields as $field)
        {
            $this->stream->write(
                $field->read(true)
            );
        }
    }

    /**
     *
     */
    public function eof() : void
    {
        $this->stream->eof();
    }

    /**
     * @return string
     */
    public function getUId() : string
    {
        return $this->uId;
    }

    /**
     * @return array[Fields]
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * @param array[FieldDefinition] $fieldDefinitions
     */
    public function addDefinition(array $fieldDefinitions)
    {
        /** @var FieldDefinition $fieldDefinition */
        foreach ($fieldDefinitions as $fieldDefinition)
        {

            if(!is_a($fieldDefinition, FieldDefinition::class))
            {
                throw new \Exception('Error while setting field definitions for table "' . $this->name . '", given object was not a FieldDefinition.');
            }

            $this->fields[$fieldDefinition->getName()] = new Field(
                $fieldDefinition->getName(),
                $fieldDefinition->getDataType(),
                $fieldDefinition->getByteLength()
            );

            $this->rowLength += (int)$fieldDefinition->getByteLength();
        }
    }

    /**
     * @param string $databaseFolder
     * @throws \Exception
     */
    public function connectToDisk($databaseFolder)
    {

        $path = rtrim($databaseFolder, '/\\') . '/' . $this->diskFilePath;

        if(!is_writable($path))
        {
            mkdir(
                pathinfo($path)['dirname'],
                0777,
                true
            );
            touch($path);
            touch($path . '.idx');
            touch($path . '.jnl');
            chmod($path, 0600);
            chmod($path . '.idx', 0600);
            chmod($path . '.jnl', 0600);
        }

        if(!is_writable($path))
        {
            throw new \Exception('Could not write table to disk under: "' . $path . '".');
        }

        $this->fileResource = fopen($path, 'a+');
        $this->stream = new Stream(
            $this->fileResource
        );
        $this->stream->eof();
    }


    public function lock()
    {
        flock($this->fileResource, LOCK_EX);
    }

    public function unlock()
    {
        flock($this->fileResource, LOCK_UN);
    }

}