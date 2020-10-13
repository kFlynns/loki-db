<?php

namespace LokiDb\Table;

use GuzzleHttp\Psr7\Stream;

/**
 * Class Table
 * @package LokiDb\Table
 */
class Table
{

    /** @var Stream */
    private $stream;

    /** @var array[Field]  */
    private $fields = [];

    /** @var string */
    private $name;

    /** @var int */
    private $rowLength = 0;

    /** @var string */
    private $hash;

    /** @var string */
    private $diskFilePath;


    /**
     * Table constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->hash = hash('md5', $name);
        $this->diskFilePath = implode(
                '/',
                str_split(
                    $this->hash,
                    2
                )
            ) . '/' . $this->hash . '.lki';
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
    public function setDataRow(array $data)
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


    public function flush()
    {;
        /** @var Field $field */
        foreach ($this->fields as $field)
        {
            $this->stream->write(
                $field->read(true)
            );
        }
    }


    /**
     * @return string
     */
    public function getHash() : string
    {
        return $this->hash;
    }

    /**
     * @param array[FieldDefinition] $fieldDefinitions
     */
    public function addDefinition(array $fieldDefinitions)
    {
        /** @var FieldDefinition $fieldDefinition */
        foreach ($fieldDefinitions as $fieldDefinition)
        {
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
        }

        if(!is_writable($path))
        {
            throw new \Exception('Could not write table to disk under: "' . $path . '".');
        }
        chmod($path, 0600);

        $this->stream = new Stream(
            fopen($path, 'w')
        );
        $this->stream->rewind();

    }

}