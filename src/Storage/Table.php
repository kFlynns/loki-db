<?php

namespace LokiDb\Storage;

use Generator;
use GuzzleHttp\Psr7\Stream;
use LokiDb\Query\Condition;
use LokiDb\TransactionManager;

/**
 * Class Table
 * @package LokiDb\Table
 */
class Table implements ITable
{

    /** @var Stream psr7 stream */
    private $stream;

    /** @var resource file resource of table */
    private $fileResource;

    /** @var array[Field]  */
    private $fields = [];

    /** @var string */
    private $name;

    /** @var int length of binary row */
    private $rowLength = 0;

    /** @var string first argument for pack() */
    private $packDescriptor;

    /** @var string  first argument for unpack() */
    private $unpackDescriptor;

    /** @var string actual binary data row */
    private $dataRow;

    /** @var string hash of name for handling tables */
    private $uId;

    /** @var string */
    private $diskFilePath;

    /** @var int actual offset in the table */
    private $datasetPointer;

    /** @var array */
    private $journal = [];

    /**
     * Table constructor.
     */
    private function __construct() {}


    /**
     *
     *
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
        $sortedRow = [];

        /** @var IField $field */
        foreach ($this->fields as $field)
        {
            if(isset($data[$field->getName()]))
            {
                $sortedRow[] = $data[$field->getName()];
                continue;
            }
            $sortedRow[] = null;
        }

        $this->dataRow = call_user_func_array (
            'pack',
            array_merge([
                    $this->packDescriptor
                ],
                $sortedRow
            )
        );

        $this->journal[$this->datasetPointer] = $this->dataRow;
        TransactionManager::getInstance()->autoCommit();

    }

    /**
     * @return array
     */
    public function getDataRow() : array
    {
        $this->dataRow = $this->stream->read($this->rowLength);
        return unpack(
            $this->unpackDescriptor,
            $this->dataRow
        );
    }


    /**
     * @param Condition|null $condition
     * @return Generator|void
     */
    public function fetch(Condition $condition = null) : Generator
    {

        $tableLength = $this->getTableLength();
        $this->stream->rewind();
        $this->datasetPointer = 0;

        do
        {

            if(isset($this->journal[$this->datasetPointer]))
            {
                $dataRow = unpack(
                    $this->unpackDescriptor,
                    $this->journal[$this->datasetPointer]
                );
            }
            else
            {
                $dataRow = $this->getDataRow();
            }

            $dataRow['trace'] = bin2hex(random_bytes(4));
            $matchCondition = true;

            if(null !== $condition)
            {
                $testCondition = clone $condition;
                $matchCondition = (bool)$testCondition->solve(function($fieldName) use ($dataRow) {
                    return $dataRow[$fieldName];
                });
            }

            if($matchCondition)
            {
                yield $dataRow;
            }

            $this->stream->seek($this->datasetPointer += $this->rowLength);

        } while ($tableLength > $this->datasetPointer);

    }

    /**
     * lock table and flush journal to disk
     */
    public function flush($intoVoid = false) : void
    {
        if(!$intoVoid)
        {
            foreach ($this->journal as $datasetPointer => $dataRow)
            {
                $this->stream->seek($datasetPointer);
                $this->stream->write($dataRow);
            }
        }
        $this->journal = [];
    }

    /**
     * @return int
     */
    private function getTableLength()
    {
        $length = $this->stream->getSize();
        foreach ($this->journal as $datasetPointer => $ignore)
        {
            $datasetPointer += $this->rowLength;
            $length = ($datasetPointer > $this->stream->getSize()) ? $datasetPointer : $length;
        }
        return (int)$length;
    }


    /**
     * set pointer to the end of the table
     */
    public function eof() : void
    {
        $this->datasetPointer = $this->getTableLength();
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

            $name = $fieldDefinition->getName();
            $dataType = $fieldDefinition->getDataType();
            $byteLength = $fieldDefinition->getByteLength();

            $this->fields[$name] = new Field(
                $name,
                $dataType,
                $byteLength
            );

            switch ($fieldDefinition->getDataType())
            {
                case FieldDefinition::DATA_TYPE_CHAR:
                case FieldDefinition::DATA_TYPE_BOOL:
                    $this->packDescriptor .= 'C1';
                    $this->unpackDescriptor .= 'C1' . $name . '/';
                    break;
                case FieldDefinition::DATA_TYPE_INT:
                    $this->packDescriptor .= 'i';
                    $this->unpackDescriptor .= 'i' . $name . '/';
                    break;
                case FieldDefinition::DATA_TYPE_STRING:
                    $this->packDescriptor .= 'Z' . $byteLength;
                    $this->unpackDescriptor .= 'Z' . $byteLength . $name . '/';
            }

            $this->rowLength += (int)$fieldDefinition->getByteLength();
        }

        $this->unpackDescriptor =  rtrim($this->unpackDescriptor, '/');

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
            chmod($path, 0600);
            chmod($path . '.idx', 0600);
        }

        if(!is_writable($path))
        {
            throw new \Exception('Could not write table to disk under: "' . $path . '".');
        }

        // try in case file is locked
        for($tries = 0; $tries < 1000000; $tries++)
        {
            $this->fileResource = fopen($path, 'r+');
            if(is_resource($this->fileResource))
            {
                break;
            }
        }

        $this->stream = new Stream($this->fileResource);
        $this->stream->rewind();
        $this->datasetPointer = 0;

    }


    public function lock() : void
    {
        flock($this->fileResource, LOCK_EX);
    }

    public function unlock() : void
    {
        flock($this->fileResource, LOCK_UN);
    }

}